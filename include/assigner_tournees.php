<?php
include "../db/db_connect.php";

// Fonction pour récupérer les livraisons non assignées
function get_livraisons_en_attente($conn) {
    $sql = "SELECT * FROM Livraisons WHERE id_employe IS NULL ORDER BY id_tranche, date_livraison";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fonction pour récupérer les livreurs disponibles
function get_livreurs_disponibles($conn) {
    $sql = "SELECT id_employe FROM Employe WHERE poste = 'livreur'";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fonction pour assigner les livraisons aux livreurs
function assigner_tournees($conn) {
    $livraisons = get_livraisons_en_attente($conn);
    $livreurs = get_livreurs_disponibles($conn);

    if (empty($livraisons) || empty($livreurs)) {
        return "Aucune livraison à assigner ou aucun livreur disponible.";
    }

    $tournees = [];
    foreach ($livreurs as $livreur) {
        $tournees[$livreur['id_employe']] = [];
    }

    foreach ($livraisons as $livraison) {
        $tranche = $livraison['id_tranche'];

        // Trouver un livreur sans ce créneau
        foreach ($tournees as $id_livreur => $tournee) {
            $creneaux_deja_assignes = array_column($tournee, 'id_tranche');
            if (!in_array($tranche, $creneaux_deja_assignes)) {
                $tournees[$id_livreur][] = $livraison;
                break;
            }
        }
    }

    // Mise à jour de la base de données
    foreach ($tournees as $id_livreur => $livraisons_livreur) {
        foreach ($livraisons_livreur as $livraison) {
            $sql = "UPDATE Livraisons SET id_employe = $id_livreur WHERE id_livraison = {$livraison['id_livraison']}";
            mysqli_query($conn, $sql);
        }
    }

    return "Tournées assignées avec succès.";
}

// Exécution 
echo assigner_tournees($conn);

// Fermeture de la connexion
include "../db/db_disconnect.php";
?>
