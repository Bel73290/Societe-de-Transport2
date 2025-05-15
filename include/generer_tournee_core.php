<?php 
/*****  ALGORITHME ROUND-ROBIN MODIFIÉ AVEC DISPONIBILITÉ PAR TRANCHE HORAIRE *****/
require_once __DIR__.'/../db/db_connect.php';

/* 1) livreurs */
$livreurs = [];
$res = mysqli_query($conn, "SELECT id FROM Utilisateur WHERE types = 'employe'");
while ($row = mysqli_fetch_assoc($res)) $livreurs[] = $row['id'];
$nbLivreurs = count($livreurs);

/* 2) colis à traiter : statut 'en attente', id_employe = 3, date_livraison = aujourd'hui */
$sql = "
SELECT id
FROM   Colis
WHERE  statut = 'en attente'
  AND  id_employe = 3
  AND  DATE(date_livraison) = CURDATE()
ORDER BY id
";
$colis = mysqli_query($conn, $sql);

$indexLivreur = 0;
while ($col = mysqli_fetch_assoc($colis)) {

    $slot = null;
    $resSlots = mysqli_query($conn, "SELECT id FROM TrancheHoraire ORDER BY id");

    while ($rowSlot = mysqli_fetch_assoc($resSlots)) {
        $slotId = $rowSlot['id'];
        $livreurId = $livreurs[$indexLivreur];

        /* Vérifie si le livreur est dispo à ce créneau */
        $resDispo = mysqli_query($conn, "
            SELECT 1 FROM Horaires_employe
            WHERE id_employe = $livreurId AND id_tranche_horaire = $slotId
        ");
        if (mysqli_num_rows($resDispo) == 0) continue; // Pas dispo, on passe au suivant

        /* Vérifie qu’il n’a pas déjà une livraison à ce créneau aujourd’hui */
        $slotLibre = mysqli_num_rows(mysqli_query($conn, "
            SELECT 1 FROM Livraison
            WHERE id_employe = $livreurId
              AND id_tranche_horaire = $slotId
              AND DATE(date_livraison) = CURDATE()
        ")) == 0;

        if ($slotLibre) {
            $slot = $slotId;
            break;
        }
    }

    if ($slot === null) {
        $indexLivreur = ($indexLivreur + 1) % $nbLivreurs;
        continue;
    }

    /* insertion Livraison */
    $stmt = mysqli_prepare($conn, "
        INSERT INTO Livraison
        (id_colis, id_employe, id_tranche_horaire, statut, date_livraison, id_depot)
        VALUES (?, ?, ?, 'en attente', CURDATE(), 1)
    ");
    mysqli_stmt_bind_param($stmt, "iii",
        $col['id'], $livreurs[$indexLivreur], $slot);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    /* maj statut colis */
    mysqli_query($conn,
        "UPDATE Colis SET statut = 'en livraison' WHERE id = {$col['id']}");

    /* livreur suivant */
    $indexLivreur = ($indexLivreur + 1) % $nbLivreurs;
}

mysqli_close($conn);
