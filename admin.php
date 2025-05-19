<?php
session_start();
include 'db/db_connect.php';

$date = new DateTime();

if (isset($_GET['annee']) && isset($_GET['semaine'])) {
    $annee = intval($_GET['annee']);
    $semaine = intval($_GET['semaine']);
} else {
    $annee = intval($date->format("o"));     // Année ISO
    $semaine = intval($date->format("W"));    // Semaine ISO
    // Redirection pour afficher l’URL proprement avec les bons paramètres
    header("Location: ?annee=$annee&semaine=$semaine");
    exit;
}

$jours_semaine = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
$horaires_grille = [];

for ($heure = 9; $heure <= 18; $heure++) {
    foreach ($jours_semaine as $jour) {
        $horaires_grille[$heure][$jour] = [];
    }
}

if ($annee !== '' && $semaine !== '') {
    $query = "
        SELECT u.nom, t.jour, t.heure_debut, t.heure_fin
        FROM Horaire_employe t
        JOIN Utilisateur u ON u.id = t.utilisateur_id
        WHERE t.annee = " . intval($annee) . "
        AND t.semaine = " . intval($semaine) . "
    ";

    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Erreur dans la requête : " . mysqli_error($conn));
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $nom = $row['nom'];
        $jour = strtolower($row['jour']);
        $heure_debut = intval(substr($row['heure_debut'], 0, 2));
        $heure_fin = intval(substr($row['heure_fin'], 0, 2));

        for ($h = $heure_debut; $h < $heure_fin; $h++) {
            if (isset($horaires_grille[$h][$jour])) {
                $horaires_grille[$h][$jour][] = $nom;
            }
        }
    }
}

if (!empty($_SESSION['flash'])) {
    echo '<div class="alert alert-info">'.htmlspecialchars($_SESSION['flash']).'</div>';
    unset($_SESSION['flash']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Transplac - Calendrier Administrateur</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>


<div>
    <header>
        <div class="header-left">
        <h1><a href="index.php" class="home-link">TransPlac</a></h1>

        </div>
        <div class="header-right">
            <?php
                $employee_name = $_SESSION['employee_name'] ?? ($_SESSION['user_name'] ?? 'Utilisateur');
            ?>
            <p><?= htmlspecialchars($employee_name) ?></p>
        </div>
    </header>

    <div class="main-content">
        <aside>
            <h2>Menu administrateur</h2>

            <!-- Liste des employés -->
            <div class="aside-section">
                <h3>Liste des employés</h3>
                <ul class="employee-list">
                    <?php
                    $empQuery = mysqli_query($conn, "SELECT id, nom FROM Utilisateur WHERE types = 'employe'");
                    while ($emp = mysqli_fetch_assoc($empQuery)) {
                        echo '<li>' . htmlspecialchars($emp['nom']) . '</li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- Génération de la tournée -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="aside-section">
                <h3>Génération de tournée</h3>
                <form action="include/generer_tournee.php" method="POST" onsubmit="return confirm('Générer la tournée pour aujourd’hui ?');">
                    <button type="submit" class="btn btn-primary">🗺 Générer la tournée du jour</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Importation de fichiers -->
            <div class="aside-section">
                <h3>Importer des fichiers</h3>
                <form action="import/Excel.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="excelFile" accept=".xls,.xlsx" required>
                    <button type="submit" class="btn btn-primary">📂 Importer</button>
                </form>
            </div>
        </aside>


        <main>
            <form method="GET" class="filters">
                <label>Année :
                    <input type="number" name="annee" value="<?= htmlspecialchars($annee) ?>" required>
                </label>
                <label>Semaine :
                    <input type="number" name="semaine" value="<?= htmlspecialchars($semaine) ?>" required>
                </label>
                <button type="submit">Afficher</button>
                <button data-nav="prev">← Semaine précédente</button>
                <button data-nav="next">Semaine suivante →</button>
            </form>


            <?php if ($annee && $semaine): ?>
                <table class="calendar">
                    <thead>
                        <tr>
                            <th>Heure</th>
                            <?php foreach ($jours_semaine as $jour): ?>
                                <th><?= ucfirst($jour) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($h = 9; $h <= 18; $h++): ?>
                            <tr>
                                <th><?= $h ?>h</th>
                                <?php foreach ($jours_semaine as $jour): ?>
                                    <td>
                                        <?php foreach ($horaires_grille[$h][$jour] as $nom): ?>
                                            <span class="nom"><?= htmlspecialchars($nom) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            <?php elseif ($_GET): ?>
                <p>Aucun horaire trouvé.</p>
            <?php endif; ?>
        </main>
    </div>

    <footer>
        <p>© 2025 Entreprise TransPlac - Tous droits réservés</p>
    </footer>
</div>

<script src="js/admin_main.js"></script>
</body>
</html>
