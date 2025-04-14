<?php
session_start();
include_once '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_livraison'], $_POST['statut'])) {
    $id_livraison = intval($_POST['id_livraison']);
    $nouveau_statut = $_POST['statut'];

    // Sécurité : limiter aux statuts autorisés
    $statuts_autorises = ['livrée', 'annulée'];
    if (!in_array($nouveau_statut, $statuts_autorises)) {
        die("Statut invalide.");
    }

    // Récupérer l'id du colis associé à cette livraison
    $sql_colis = "SELECT id_colis FROM Livraison WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql_colis);
    mysqli_stmt_bind_param($stmt, "i", $id_livraison);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id_colis);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$id_colis) {
        die("Livraison non trouvée.");
    }

    // Mettre à jour la table Livraison
    $sql_update = "UPDATE Livraison SET statut = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt, "si", $nouveau_statut, $id_livraison);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Mettre à jour la table Colis
    if ($nouveau_statut === 'livrée') {
        $sql_colis_update = "UPDATE Colis SET statut = 'livré' WHERE id = $id_colis";
    } else {
        $sql_colis_update = "UPDATE Colis SET statut = 'en stock' WHERE id = $id_colis";
    }

    mysqli_query($conn, $sql_colis_update);

    mysqli_close($conn);

    // Redirection vers la tournée
    header("Location: livreur_main.php");
    exit();
} else {
    echo "Requête invalide.";
}
