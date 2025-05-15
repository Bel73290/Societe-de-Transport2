<?php
session_start();
include_once 'db/db_connect.php';
include 'include/Crud_colis.php'; // Pour utiliser insert_Livraison

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$idUtilisateur = $_SESSION['id'];

// Vérifier la table Colis pour récupérer l'ID du colis associé à l'utilisateur
$queryColis = "SELECT id FROM Colis WHERE id_client = '$idUtilisateur' LIMIT 1";
$resultColis = mysqli_query($conn, $queryColis);

if ($resultColis && mysqli_num_rows($resultColis) > 0) {
    $rowColis = mysqli_fetch_assoc($resultColis);
    $idColis = $rowColis['id'];
} else {
    die("Erreur : Aucun colis associé trouvé pour cet utilisateur.");
}

$selectedDate = $_POST['selected_date'] ?? $_GET['selected_date'] ?? null;
$selectedHoraireId = $_POST['selected_horaire'] ?? $_GET['selected_horaire'] ?? null;

if (!$selectedDate || !$selectedHoraireId) {
    header("Location: index.php"); 
    exit();
}

// Insérer la livraison
$idEmploye = "3"; // Non assigné
$statut = 'En attente';
$commentaire = '';
$depot = "1";

$res = insert_Livraison($conn, $idColis, $idEmploye, $selectedHoraireId, $statut, $selectedDate, $depot);
if (!$res) {
    die("Erreur SQL : " . mysqli_error($conn));
}

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
        <link rel="stylesheet" href="css/confirmation.css">
    </head>
    <body>
        <div class="confirmation-box">
        <h1>Livraison confirmée !</h1>
        <?php
            echo "<p>Votre colis sera livré le " . htmlspecialchars($selectedDate) . ".</p>";
            echo "<p>entre " . $heureDebut . " et " . $heureFin . ".</p>";
        ?>
        <p class="timer">Redirection vers l'accueil dans 5 secondes...</p>
        </div>

    </body>
</html>