<?php
/*****  ALGORITHME ROUND-ROBIN  *****/
require_once __DIR__.'/../db/db_connect.php';

/* 1) livreurs */
$livreurs = [];
$res = mysqli_query($conn, "SELECT id FROM Utilisateur WHERE types = 'employe'");
while ($row = mysqli_fetch_assoc($res)) $livreurs[] = $row['id'];
$nbLivreurs = count($livreurs);

/* 2) colis disponibles (en stock, pas déjà planifiés aujourd’hui) */
$sql = "
SELECT id
FROM   Colis AS c
WHERE  c.statut = 'en stock'
  AND  NOT EXISTS (
        SELECT 1 FROM Livraison AS l
        WHERE l.id_colis = c.id
          AND DATE(l.date_livraison) = CURDATE()
  )
ORDER BY c.id
";
$colis = mysqli_query($conn, $sql);

$indexLivreur = 0;
while ($col = mysqli_fetch_assoc($colis)) {

    /* chercher un créneau libre pour ce livreur */
    $slot = null;
    $resSlots = mysqli_query($conn, "SELECT id FROM TrancheHoraire ORDER BY id");
    while ($rowSlot = mysqli_fetch_assoc($resSlots)) {
        $slotOK = mysqli_num_rows(mysqli_query($conn, "
            SELECT 1 FROM Livraison
            WHERE id_employe = {$livreurs[$indexLivreur]}
              AND id_tranche_horaire = {$rowSlot['id']}
              AND DATE(date_livraison) = CURDATE()
        ")) == 0;
        if ($slotOK) { $slot = $rowSlot['id']; break; }
    }
    if ($slot === null) {                 /* livreur saturé sur tous les créneaux */
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
