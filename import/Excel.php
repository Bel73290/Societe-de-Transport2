<?php
require '../db/db_connect.php';
require 'vendor/autoload.php'; // Chargement de PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/* db_connect.php - Connexion à la base de données avec MySQLi */
<?php
// Affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Connexion à la base de données
$conn = mysqli_connect("localhost", "info1", "1G1", "livraison_db");
mysqli_set_charset($conn, "utf8");

// Vérification de la connexion
if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}
?>


/* index.php - Interface d'upload du fichier Excel */
<?php
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    echo "<p style='color: green;'>Importation réussie !</p>";
} elseif (isset($_GET['status']) && $_GET['status'] == 'error') {
    echo "<p style='color: red;'>Erreur lors de l'importation !</p>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importer un fichier Excel</title>
</head>
<body>
    <h2>Importation de Clients dans les tables Utilisateur et Colis</h2>
    <form action="Excel.php" method="post" enctype="multipart/form-data">
        <input type="file" name="excelFile" accept=".xls,.xlsx" required>
        <button type="submit">Importer</button>
    </form>
</body>
</html>


/* Excel.php - Traitement du fichier et insertion en base de données */
<?php
require 'db_connect.php';
require 'vendor/autoload.php'; // Chargement de PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (!$conn) {
    die("Connexion à la base de données échouée : " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excelFile'])) {
    $file = $_FILES['excelFile']['tmp_name'];

    if (!$file) {
        die("Fichier introuvable ou invalide.");
    }

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        echo "<pre>";
        print_r($rows);
        echo "</pre>";

        array_shift($rows); // Supprime la ligne d'en-tête

        foreach ($rows as $row) {
            $nom = $row[0];
            $email = $row[1];
            $code_colis = $row[2];
            $telephone = $row[3];
            $adresse = $row[4];

            // 1. Insertion dans Utilisateur (sans mot de passe)
            $queryUser = "INSERT INTO Utilisateur (nom, email, telephone, adresse, types) VALUES (?, ?, ?, ?, 'client')";
            $stmtUser = mysqli_prepare($conn, $queryUser);
            if (!$stmtUser) {
                die("Erreur préparation Utilisateur : " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmtUser, "ssss", $nom, $email, $telephone, $adresse);
            mysqli_stmt_execute($stmtUser);

            // Récupération de l'id du client
            $id_client = mysqli_insert_id($conn);

            // 2. Insertion dans Colis
            $queryColis = "INSERT INTO Colis (code_colis, id_client) VALUES (?, ?)";
            $stmtColis = mysqli_prepare($conn, $queryColis);
            if (!$stmtColis) {
                die("Erreur préparation Colis : " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmtColis, "si", $code_colis, $id_client);
            mysqli_stmt_execute($stmtColis);
        }

        echo "Importation terminée.";
        exit();
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
        exit();
    }
}
?>





