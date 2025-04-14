<?php
session_start();
include_once '../db/db_connect.php';

if (!isset($_SESSION['employee_name'])) {
    header("Location: index.php");
    exit();
}

$employee_name = $_SESSION['employee_name'];

// Récupérer l'ID de l'employé connecté
$sql_id = "SELECT id FROM Utilisateur WHERE nom = ?";
$stmt = mysqli_prepare($conn, $sql_id);
mysqli_stmt_bind_param($stmt, "s", $employee_name);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $employee_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Récupérer les livraisons du jour pour cet employé
$sql = "
SELECT Livraison.id AS id_livraison, Colis.code_colis, Colis.id_client, Colis.statut AS statut_colis,
       TrancheHoraire.heure_debut, TrancheHoraire.heure_fin,
       Utilisateur.nom AS nom_client, Utilisateur.adresse
FROM Livraison
JOIN Colis ON Livraison.id_colis = Colis.id
JOIN TrancheHoraire ON Livraison.id_tranche_horaire = TrancheHoraire.id
JOIN Utilisateur ON Colis.id_client = Utilisateur.id
WHERE Livraison.id_employe = $employee_id
AND DATE(Livraison.date_livraison) = CURDATE()
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ma tournée - TransPlac</title>
    <link rel="stylesheet" href="css/livreur_style.css">
</head>
<body>
    <h2 style="text-align:right; padding: 10px;">Bonjour, <?= htmlspecialchars($employee_name) ?></h2>
    <h1 style="text-align:center;">Votre tournée du jour</h1>

    <div class="tournée-container">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="livraison-card">
                <p><strong>Client :</strong> <?= htmlspecialchars($row['nom_client']) ?></p>
                <p><strong>Adresse :</strong> <?= htmlspecialchars($row['adresse']) ?></p>
                <p><strong>Code Colis :</strong> <?= htmlspecialchars($row['code_colis']) ?></p>
                <p><strong>Tranche horaire :</strong> <?= substr($row['heure_debut'], 0, 5) ?> - <?= substr($row['heure_fin'], 0, 5) ?></p>
                <form action="update_statut.php" method="POST">
                    <input type="hidden" name="id_livraison" value="<?= $row['id_livraison'] ?>">
                    <button type="submit" name="statut" value="livrée">✅ Livré</button>
                    <button type="submit" name="statut" value="annulée">❌ Colis non livré</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>
