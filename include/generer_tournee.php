<?php
session_start();

/* ---------- 0. Sécurité : rôle + méthode ---------- */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Accès refusé');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);         
    exit('Méthode interdite');
}

/* ---------- 1. Connexion BD ---------- */
require_once __DIR__.'/../db/db_connect.php';

/* ---------- 2. On supprime la tournée existante (date du jour) ---------- */
mysqli_query(
    $conn,
    "DELETE FROM Livraison WHERE DATE(date_livraison) = CURDATE()"
);

/* ---------- 3. Appel de l’algorithme round-robin ---------- */
/*   (le cœur se trouve dans include/generer_tournee_core.php) */
require_once __DIR__.'/generer_tournee_core.php';

/* ---------- 4. Fin, message flash + redirection ---------- */
$_SESSION['flash'] = 'Nouvelle tournée générée (écrasée si elle existait) ✔️';
header('Location: ../admin.php');
exit;
