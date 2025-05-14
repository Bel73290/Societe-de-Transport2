<?php
session_start();

/* ---------- 0. Contrôle d’accès ---------- */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employe') {
    header('Location: index.php');
    exit();
}

/* ---------- 1. Identité du livreur ---------- */
$employee_name = $_SESSION['employee_name'] ?? 'Livreur';
$employee_id   = $_SESSION['id']           ?? 0;      // mis en session au login

if ($employee_id === 0) {
    echo 'Impossible de déterminer votre identifiant employé.';
    exit();
}

require_once __DIR__ . '/db/db_connect.php';

/* ---------- 2. Récupération de la tournée du jour et filtrage par TrancheHoraire + Distance en Km ---------- */
$sql = "
SELECT
    Livraison.id              AS id_livraison,      
    Colis.id                  AS id_colis,           
    Colis.code_colis,                               
    Utilisateur.nom           AS nom_client,        
    Utilisateur.adresse,                         
    TrancheHoraire.heure_debut,                      
    TrancheHoraire.heure_fin,                     

    /* 
     * Utilisation de la formule de la loi des cosinus sphérique :
     * formule : d = R * acos(cos(lat1) * cos(lat2) * cos(lon2 - lon1) + sin(lat1) * sin(lat2))
     * avec R = 6371 km, et lat/lon en radians
     */


    (
        6371 * ACOS(
            COS(RADIANS(SUBSTRING_INDEX(Depot.coordonneeGps, ',', 1))) *
            COS(RADIANS(SUBSTRING_INDEX(Utilisateur.coordonneeGps, ',', 1))) *
            COS(
                RADIANS(SUBSTRING_INDEX(Utilisateur.coordonneeGps, ',', -1)) -
                RADIANS(SUBSTRING_INDEX(Depot.coordonneeGps, ',', -1))
            ) +
            SIN(RADIANS(SUBSTRING_INDEX(Depot.coordonneeGps, ',', 1))) *
            SIN(RADIANS(SUBSTRING_INDEX(Utilisateur.coordonneeGps, ',', 1)))
        )
    ) AS distance_km

FROM Livraison
JOIN Colis          ON Colis.id = Livraison.id_colis                      /* Associe livraison au colis */
JOIN TrancheHoraire ON TrancheHoraire.id = Livraison.id_tranche_horaire  /* Associe livraison à son créneau horaire */
JOIN Utilisateur    ON Utilisateur.id = Colis.id_client                   /* Associe colis à son client */
JOIN Depot          ON Livraison.id_depot = Depot.id                      /* Associe livraison à son dépôt d'origine */

WHERE Livraison.id_employe = ?                                            /* Limite aux livraisons du livreur connecté */
  AND DATE(Livraison.date_livraison) = CURDATE()                         /* Ne garde que les livraisons du jour */
  AND Utilisateur.coordonneeGps IS NOT NULL                              /* Nécessite des coordonnées côté client */
  AND Depot.coordonneeGps IS NOT NULL                                    /* Nécessite des coordonnées côté dépôt */

ORDER BY TrancheHoraire.heure_debut ASC, distance_km ASC                 /* Trie : d'abord par heure, puis par distance */
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ma tournée - TransPlac</title>
    <link rel="stylesheet" href="css/livreur_style.css">
</head>
<body>

<!-- ---------- En-tête ---------- -->
<header class="entete-livreur">
    <span class="bonjour">Bonjour, <?= htmlspecialchars($employee_name) ?></span>
    <h1 class="titre-tournee">Votre tournée du <?= date('d/m/Y') ?></h1>
</header>

<?php if (isset($_GET['ok'])): ?>
    <p class="flash success">Statut enregistré ✔️</p>
<?php elseif (isset($_GET['err'])): ?>
    <p class="flash error">Une erreur est survenue.</p>
<?php endif; ?>

<!-- ---------- Conteneur des cartes ---------- -->
<div class="tournee-container">
    <?php if (mysqli_num_rows($result) === 0): ?>
        <p style="text-align:center;margin-top:2rem">
            Vous n’avez pas de livraison prévue aujourd’hui 🙂
        </p>
    <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="livraison-card">

                <p class="client">
                    <strong>Client :</strong> <?= htmlspecialchars($row['nom_client']) ?>
                </p>

                <p class="adresse">
                    <strong>Adresse :</strong> <?= htmlspecialchars($row['adresse']) ?>
                </p>

                <p class="code">
                    <strong>Code colis :</strong> <?= htmlspecialchars($row['code_colis']) ?>
                </p>

                <p class="creneau">
                    <strong>Créneau :</strong>
                    <?= substr($row['heure_debut'], 0, 5) ?> – <?= substr($row['heure_fin'], 0, 5) ?>
                </p>

                <!-- Bouton LIVRÉ (en bas à gauche) -->
                <form action="include/update_status.php" method="POST"
                      class="inline btn-left">
                    <input type="hidden" name="id_colis" value="<?= $row['id_colis'] ?>">
                    <input type="hidden" name="action"   value="livre">
                    <button type="submit" class="btn success">✅ Livré</button>
                </form>

                <!-- Bouton NON LIVRÉ (en bas à droite) -->
                <form action="include/update_status.php" method="POST"
                      class="inline btn-right">
                    <input type="hidden" name="id_colis" value="<?= $row['id_colis'] ?>">
                    <input type="hidden" name="action"   value="non_livre">
                    <button type="submit" class="btn danger">❌ Colis non livré</button>
                </form>

            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

</body>
</html>
<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
