<?php 
/*****  REDISTRIBUTION SIMPLE DES LIVRAISONS ENTRE EMPLOYÉS *****/
require_once __DIR__.'/../db/db_connect.php';

/* 1) Récupérer tous les livreurs SAUF l'employé 3 */
$livreurs = [];
$res = mysqli_query($conn, "SELECT id FROM Utilisateur WHERE types = 'employe' AND id != 3");
while ($row = mysqli_fetch_assoc($res)) $livreurs[] = $row['id'];
$nbLivreurs = count($livreurs);

/* 2) Récupérer les livraisons à réassigner */
$sql = "
SELECT id
FROM Livraison
WHERE statut = 'en attente'
  AND id_employe = 3
  AND DATE(date_livraison) = CURDATE()
ORDER BY id
";
$resLivraisons = mysqli_query($conn, $sql);

/* 3) Répartition round-robin */
$indexLivreur = 0;
while ($livraison = mysqli_fetch_assoc($resLivraisons)) {
    $nouvelEmploye = $livreurs[$indexLivreur];

    $stmt = mysqli_prepare($conn, "
        UPDATE Livraison
        SET id_employe = ?
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmt, "ii", $nouvelEmploye, $livraison['id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $indexLivreur = ($indexLivreur + 1) % $nbLivreurs;
}

mysqli_close($conn);

