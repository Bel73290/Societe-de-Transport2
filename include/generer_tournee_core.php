<?php 
/*****  REDISTRIBUTION DES LIVRAISONS EXISTANTES ENTRE EMPLOYÉS DISPONIBLES *****/
require_once __DIR__.'/../db/db_connect.php';

/* 1) livreurs disponibles */
$livreurs = [];
$res = mysqli_query($conn, "SELECT id FROM Utilisateur WHERE types = 'employe'");
while ($row = mysqli_fetch_assoc($res)) $livreurs[] = $row['id'];
$nbLivreurs = count($livreurs);

/* 2) récupérer les livraisons à réassigner (employé = 3, en attente aujourd’hui) */
$sql = "
SELECT id, id_tranche_horaire
FROM Livraison
WHERE statut = 'en attente'
  AND id_employe = 3
  AND DATE(date_livraison) = CURDATE()
ORDER BY id
";
$resLivraisons = mysqli_query($conn, $sql);

$indexLivreur = 0;
while ($livraison = mysqli_fetch_assoc($resLivraisons)) {
    $slotId = $livraison['id_tranche_horaire'];
    $assigned = false;

    for ($i = 0; $i < $nbLivreurs; $i++) {
        $livreurId = $livreurs[$indexLivreur];

        /* Vérifie que le livreur est dispo pour ce créneau */
        $dispo = mysqli_query($conn, "
            SELECT 1 FROM Horaires_employe
            WHERE id_employe = $livreurId AND id_tranche_horaire = $slotId
        ");
        if (mysqli_num_rows($dispo) == 0) {
            $indexLivreur = ($indexLivreur + 1) % $nbLivreurs;
            continue;
        }

        /* Vérifie qu’il n’a pas déjà une livraison à ce créneau aujourd’hui */
        $occupé = mysqli_query($conn, "
            SELECT 1 FROM Livraison
            WHERE id_employe = $livreurId
              AND id_tranche_horaire = $slotId
              AND DATE(date_livraison) = CURDATE()
        ");
        if (mysqli_num_rows($occupé) > 0) {
            $indexLivreur = ($indexLivreur + 1) % $nbLivreurs;
            continue;
        }

        /* Mise à jour de l’employé sur cette livraison */
        mysqli_query($conn, "
            UPDATE Livraison
            SET id_employe = $livreurId
            WHERE id = {$livraison['id']}
        ");

        $assigned = true;
        $indexLivreur = ($indexLivreur + 1) % $nbLivreurs;
        break;
    }

    if (!$assigned) {
        // Aucun livreur dispo pour ce créneau, on ignore cette livraison
        continue;
    }
}

mysqli_close($conn);
