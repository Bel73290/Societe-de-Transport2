<?php
include_once '../db/db_connect.php'; // Connexion à la BDD

// Récupérer toutes les livraisons sans employé assigné
$sql_livraisons = "SELECT * FROM Livraison WHERE id_employe IS NULL";
$result_livraisons = mysqli_query($conn, $sql_livraisons);

if (!$result_livraisons) {
    die("Erreur SQL (récupération des livraisons) : " . mysqli_error($conn));
}

while ($livraison = mysqli_fetch_assoc($result_livraisons)) {
    $id_livraison = $livraison['id'];
    $id_colis = $livraison['id_colis'];
    $id_tranche = $livraison['id_tranche_horaire'];

    // Rechercher tous les employés disponibles dans cette tranche horaire (pas déjà une livraison dans cette tranche)
    $sql_employes = "
        SELECT u.id, COUNT(l2.id) AS nb_livraisons
        FROM Utilisateur u
        LEFT JOIN Livraison l2 ON u.id = l2.id_employe 
            AND DATE(l2.date_livraison) = CURDATE()
        WHERE u.type = 'employe'
        AND u.id NOT IN (
            SELECT id_employe
            FROM Livraison
            WHERE id_tranche_horaire = $id_tranche
            AND DATE(date_livraison) = CURDATE()
        )
        GROUP BY u.id
        ORDER BY nb_livraisons ASC
        LIMIT 1
    ";

    $result_employe = mysqli_query($conn, $sql_employes);

    if ($result_employe && mysqli_num_rows($result_employe) > 0) {
        $employe = mysqli_fetch_assoc($result_employe);
        $id_employe = $employe['id'];

        // Mettre à jour la livraison avec l'employé trouvé
        $sql_update = "
            UPDATE Livraison
            SET id_employe = $id_employe
            WHERE id = $id_livraison
        ";
        mysqli_query($conn, $sql_update);

        // Mettre à jour le statut du colis
        $sql_update_colis = "
            UPDATE Colis
            SET statut = 'en livraison'
            WHERE id = $id_colis
        ";
        mysqli_query($conn, $sql_update_colis);
    } else {
        // Aucun employé dispo pour cette tranche
        echo "Aucun employé disponible pour la tranche horaire ID $id_tranche.<br>";
    }
}

mysqli_close($conn);
echo "Assignation terminée.";
?>
