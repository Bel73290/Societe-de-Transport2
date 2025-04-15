<?php
session_start();
include_once 'db/db_connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Vérifie si les données de livraison sont passées
// Support à la fois POST et GET
$selectedDate = $_POST['selected_date'] ?? $_GET['selected_date'] ?? null;
$selectedHoraireId = $_POST['selected_horaire'] ?? $_GET['selected_horaire'] ?? null;

if (!$selectedDate || !$selectedHoraireId) {
    header("Location: index.php");
    exit();
}


$selectedDate = $_POST['selected_date'];
$selectedHoraireId = $_POST['selected_horaire'];

// Récupère les infos de la tranche horaire
$query = "SELECT heure_debut, heure_fin FROM TrancheHoraire WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $selectedHoraireId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $heureDebut = substr($row['heure_debut'], 0, 5);
    $heureFin = substr($row['heure_fin'], 0, 5);
} else {
    $heureDebut = "??:??";
    $heureFin = "??:??";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation</title>
    <meta http-equiv="refresh" content="5;url=index.php">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .confirmation-box {
            background-color: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .confirmation-box h1 {
            color: #1373c2;
            margin-bottom: 20px;
        }

        .confirmation-box p {
            font-size: 1.2em;
            margin: 10px 0;
        }

        .confirmation-box .timer {
            margin-top: 20px;
            font-style: italic;
            color: gray;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <h1>Livraison confirmée !</h1>
        <p>Votre colis sera livré le <strong><?= htmlspecialchars($selectedDate) ?></strong></p>
        <p>entre <strong><?= $heureDebut ?></strong> et <strong><?= $heureFin ?></strong>.</p>
        <p class="timer">Redirection vers l'accueil dans 5 secondes...</p>
    </div>
</body>
</html>
