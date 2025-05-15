<?php
// Script exécutable automatiquement par CRON

require_once __DIR__ . '/../db/db_connect.php';

$idDepotRelais = 2; // ID du point relais dans la table Depot
$idTrancheHoraire = 1; // Tranche horaire par défaut
$idEmploye = 1; // Employé affecté aux livraisons automatiques

// Récupérer l'adresse du point relais depuis la table Depot
$adresseRelais = '';
$resDepot = mysqli_query($conn, "SELECT adresse FROM Depot WHERE id = $idDepotRelais LIMIT 1");
if ($rowDepot = mysqli_fetch_assoc($resDepot)) {
    $adresseRelais = $rowDepot['adresse'];
} else {
    echo "Erreur : le dépôt point relais avec l'ID $idDepotRelais est introuvable.\n";
    exit();
}

// Récupérer les colis en stock depuis au moins 14 jours
$query = "
    SELECT id, id_client
    FROM Colis
    WHERE statut = 'en stock'
      AND date_reception <= NOW() - INTERVAL 14 DAY
";

$result = mysqli_query($conn, $query);
$count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $id_colis = $row['id'];

    // Créer une livraison vers le point relais
    $queryLivraison = "
        INSERT INTO Livraison (
            id_colis, id_employe, id_tranche_horaire,
            adresse_livraison, statut, date_livraison, id_depot
        )
        VALUES (?, ?, ?, ?, 'en attente', NOW(), ?)
    ";
    $stmt = mysqli_prepare($conn, $queryLivraison);

    if (!$stmt) {
        echo "Erreur de préparation : " . mysqli_error($conn) . "\n";
        continue;
    }

    mysqli_stmt_bind_param($stmt, "iiisi", $id_colis, $idEmploye, $idTrancheHoraire, $adresseRelais, $idDepotRelais);
    mysqli_stmt_execute($stmt);

    // Mettre à jour le statut du colis en "en cours"
    $updateStatut = "UPDATE Colis SET statut = 'en cours' WHERE id = ?";
    $stmtUpdate = mysqli_prepare($conn, $updateStatut);
    if ($stmtUpdate) {
        mysqli_stmt_bind_param($stmtUpdate, "i", $id_colis);
        mysqli_stmt_execute($stmtUpdate);
    } else {
        echo "Erreur mise à jour statut colis #$id_colis : " . mysqli_error($conn) . "\n";
    }

    $count++;
}

// Affiche nombre de livraison relais
echo "[$count] livraisons vers le point relais créées.\n";

mysqli_close($conn);
?>

