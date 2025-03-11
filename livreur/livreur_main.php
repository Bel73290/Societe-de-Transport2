<?php
session_start();
include "../connexion/db_connect.php";

// Vérifier si l'employé est connecté
if (!isset($_SESSION['id_employe'])) {
    header("Location: index.php"); // Redirige vers la page de connexion si non connecté
    exit();
}

$id_employe = $_SESSION['id_employe'];

// Récupérer les infos du livreur
$sql_livreur = "SELECT nom, prenom FROM Employe WHERE id_employe = $id_employe";
$result_livreur = mysqli_query($conn, $sql_livreur);
$livreur = mysqli_fetch_assoc($result_livreur);

// Récupérer les livraisons assignées au livreur
$sql_tournee = "SELECT L.id_livraison, C.nom, C.prenom, C.adresse, L.status, T.heure_debut, T.heure_fin
                FROM Livraisons L
                INNER JOIN Client C ON L.id_client = C.id_client
                INNER JOIN TrancheHoraire T ON L.id_tranche = T.id_tranche
                WHERE L.id_employe = $id_employe
                ORDER BY L.id_tranche, L.date_livraison";

$result_tournee = mysqli_query($conn, $sql_tournee);
$tournee = mysqli_fetch_all($result_tournee, MYSQLI_ASSOC);

// Fermeture de la connexion
include "../connexion/db_disconnect.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de tournée</title>
    <link rel="stylesheet" href="livreur_style.css">
</head>
<body>
    <header>
        <h2>Fiche de tournée</h2>
        <span class="livreur-nom"><?= htmlspecialchars($livreur['prenom'] . " " . $livreur['nom']) ?></span>
    </header>

    <main>
        <?php 
        $tranches_utilisees = [];

        foreach ($tournee as $livraison): 
            // Exclure les doublons de tranche horaire
            if (in_array($livraison['heure_debut'], $tranches_utilisees)) {
                continue;
            }
            $tranches_utilisees[] = $livraison['heure_debut'];
        ?>
            <div class="livraison">
                <p><strong><?= htmlspecialchars($livraison['nom'] . " " . $livraison['prenom']) ?></strong></p>
                <p><?= htmlspecialchars($livraison['adresse']) ?></p>
                
                <label>
                    <input type="checkbox" class="status-checkbox" data-livraison="<?= $livraison['id_livraison'] ?>" value="livré">
                    Livré
                </label>

                <label>
                    <input type="checkbox" class="status-checkbox" data-livraison="<?= $livraison['id_livraison'] ?>" value="non_livré">
                    Colis non livré
                </label>
            </div>
        <?php endforeach; ?>
    </main>

    <footer>
        <label>
            <input type="checkbox" id="fin-tournee">
            Fin de tournée
        </label>
    </footer>

    <script>
        // Gestion des cases à cocher
        document.querySelectorAll(".status-checkbox").forEach(checkbox => {
            checkbox.addEventListener("change", function() {
                let livraisonId = this.getAttribute("data-livraison");
                let status = this.value;

                // Décocher l'autre case si une est cochée
                document.querySelectorAll(`[data-livraison='${livraisonId}']`).forEach(cb => {
                    if (cb !== this) cb.checked = false;
                });

                // Envoyer la mise à jour en AJAX
                fetch("update_status.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `id_livraison=${livraisonId}&status=${status}`
                });
            });
        });

        // Gestion de la fin de tournée
        document.getElementById("fin-tournee").addEventListener("change", function() {
            if (this.checked) {
                alert("Tournée terminée !");
            }
        });
    </script>
</body>
</html>
