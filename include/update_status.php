<?php
session_start();

/* 1) sécurité : uniquement les livreurs connectés */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employe') {
    http_response_code(403);
    exit('Accès refusé');
}

/* 2) vérifier la requête */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode interdite');
}

require_once __DIR__.'/../db/db_connect.php';

/* 3) récupérer et assainir les données */
$id_colis = isset($_POST['id_colis']) ? (int)$_POST['id_colis'] : 0;
$action   = $_POST['action'] ?? '';

if ($id_colis <= 0 || !in_array($action, ['livre', 'non_livre'], true)) {
    mysqli_close($conn);
    header('Location: ../livreur_main.php?err=badparam');
    exit;
}

/* 4) exécuter l’action */
if ($action === 'livre') {
    /* a) supprimer la tournée */
    mysqli_query($conn, "DELETE FROM Livraison WHERE id_colis = $id_colis");
    /* b) supprimer définitivement le colis */
    mysqli_query($conn, "DELETE FROM Colis WHERE id = $id_colis");
} else { // non_livre
    /* a) remettre le colis en stock */
    mysqli_query($conn, "
        UPDATE Colis
        SET statut = 'en stock'
        WHERE id = $id_colis
    ");
    /* b) libérer le créneau (plus de livreur assigné) */
    mysqli_query($conn, "DELETE FROM Livraison WHERE id_colis = $id_colis");
}

mysqli_close($conn);
header('Location: ../livreur_main.php?ok');
exit;
