<?php
session_start();
include_once 'db/db_connect.php'; // Connexion à la base de données

// Vérification de connexion utilisateur
if (!isset($_SESSION['id'])) {
    header("Location: index.php"); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    exit();
}

$idUtilisateur = $_SESSION['id']; // ID utilisateur connecté

// Vérifier la connexion à la base de données
if (!$conn || $conn === false) {
    die("Erreur : Connexion à la base de données impossible.");
}

// Vérifier la table `Colis` pour récupérer l'ID du colis associé à l'utilisateur
$queryColis = "SELECT id FROM Colis WHERE id_client = '$idUtilisateur' LIMIT 1";
$resultColis = mysqli_query($conn, $queryColis);

if ($resultColis && mysqli_num_rows($resultColis) > 0) {
    $rowColis = mysqli_fetch_assoc($resultColis);
    $idColis = $rowColis['id']; // ID du colis récupéré
} else {
    die("Erreur : Aucun colis associé trouvé pour cet utilisateur.");
}

// Gestion des requêtes POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['selected_horaire']) && isset($_POST['selected_date'])) {
        // Récupérer les données POST
        $selectedHoraire = mysqli_real_escape_string($conn, $_POST['selected_horaire']);
        $selectedDate = mysqli_real_escape_string($conn, $_POST['selected_date']);

        // Valeurs pour insertion dans livraison
        $idEmploye = "3"; // Pas d'employé assigné
        $statut = 'En attente'; // Statut initial
        $commentaire = ''; // Commentaire vide par défaut
        $depot = "1";

        // Insérer dans la table livraison
        $queryLivraison = "
            INSERT INTO Livraison (id_colis, id_employe, id_tranche_horaire, statut, date_livraison, id_depot)
            VALUES ('$idColis', $idEmploye, '$selectedHoraire', '$statut', '$selectedDate', '$depot')
        ";

        // Exécuter la requête d'insertion
        $resultLivraison = mysqli_query($conn, $queryLivraison);

         // Si l'insertion réussit, rediriger vers la page confirmation
         if ($resultLivraison) {
            header("Location: confirmation.php?selected_date=$selectedDate&selected_horaire=$selectedHoraire");
            exit(); // Important pour éviter toute exécution supplémentaire
        } else {
            echo "Erreur lors de l'insertion dans la base de données.";
        }
    } elseif (isset($_POST['selected_date'])) {
        $selectedDate = mysqli_real_escape_string($conn, $_POST['selected_date']);

        // Récupérer les tranches horaires disponibles pour la date sélectionnée
        $queryHoraire = "SELECT * FROM TrancheHoraire";
        $resultHoraire = mysqli_query($conn, $queryHoraire);

        if (!$resultHoraire) {
            die("Erreur SQL : " . mysqli_error($conn));
        }

        if (mysqli_num_rows($resultHoraire) > 0) {
            echo "<h2>Horaires disponibles pour le $selectedDate</h2>";
            echo "<form id='horaire-form' method='POST' action='confirmation.php'>";
            while ($row = mysqli_fetch_assoc($resultHoraire)) {
                $horaireId = $row['id'];
                $heureDebut = substr($row['heure_debut'], 0, 5); // Ex : 08:00
                $heureFin = substr($row['heure_fin'], 0, 5);     // Ex : 10:00

                echo "<div class='horaire-item'>";
                echo "<input type='radio' name='selected_horaire' value='$horaireId' required>";
                echo "<label>$heureDebut - $heureFin</label>";
                echo "</div>";
            }
            echo "<input type='hidden' name='selected_date' value='$selectedDate'>";
            echo "<button type='submit'>Valider</button>";
            echo "</form>";
        } else {
            echo "<p>Aucune tranche horaire disponible pour cette date.</p>";
        }
        exit();
    }
}

// Gérer la navigation du calendrier
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fonction pour générer un calendrier
function generateCalendar($month, $year) {
    $daysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    $firstDayOfMonth = strtotime("$year-$month-01");
    $numberOfDays = date('t', $firstDayOfMonth);
    $startingDay = (date('w', $firstDayOfMonth) + 6) % 7;

    $calendar = "<table><tr>";

    foreach ($daysOfWeek as $day) {
        $calendar .= "<th>$day</th>";
    }
    $calendar .= "</tr><tr>";

    $currentDay = 1;
    for ($i = 0; $i < $startingDay; $i++) {
        $calendar .= "<td class='empty-cell'>...</td>";
    }

    while ($currentDay <= $numberOfDays) {
        if (($startingDay + $currentDay - 1) % 7 == 0 && $currentDay != 1) {
            $calendar .= "</tr><tr>";
        }
        $isoDate = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-" . str_pad($currentDay, 2, "0", STR_PAD_LEFT);
        $calendar .= "<td><button class='date-btn' data-date='$isoDate'>$currentDay</button></td>";
        $currentDay++;
    }

    while (($startingDay + $currentDay - 1) % 7 != 0) {
        $calendar .= "<td class='empty-cell'>...</td>";
        $currentDay++;
    }

    $calendar .= "</tr></table>";
    return $calendar;
}

$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;


// Si le mois est invalide (en dehors de la plage de 1 à 12), utilisez le mois actuel
if (!is_int($month)) {
    $month = 4;
}

$moisFrancais = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai',
    6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre',
    11 => 'Novembre', 12 => 'Décembre'
];

$monthYear = $moisFrancais[$month] . " " . $year;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier interactif</title>
    <link rel="stylesheet" href="css/client2.css">
</head>
<body>
    <div id="calendar-container">
        <div class="calendar-header">
            <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" id="prev-month">Mois précédent</a>
            <h1 id="month-year-display"><?php echo $monthYear; ?></h1>
            <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" id="next-month">Mois suivant</a>
        </div>
        <div id="calendar">
            <?php echo generateCalendar($month, $year); ?>
        </div>
    </div>

    <div id="horaire-container" style="display: none;">
        <h2>Tranches horaires disponibles</h2>
        <p>Sélectionnez une date pour afficher les horaires.</p>
    </div>
    <button id="back-button" style="display: none;">Retour</button>
    <script src="js/client.js" defer></script>
</body>
</html>
