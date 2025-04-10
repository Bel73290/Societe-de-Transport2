<?php
require '../db/db_connect.php';
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

        $query = "INSERT INTO Utilisateur (nom, email, mdp, telephone, adresse, types) VALUES (?, ?, ?, ?, ?, 'client')";
        $stmt = mysqli_prepare($conn, $query);

        if (!$stmt) {
            die("Erreur dans la requête SQL : " . mysqli_error($conn));
        }

        foreach ($rows as $row) {
            $nom = $row[0];
            $email = $row[1];
            $mdp = $row[2]; 
            $telephone = $row[3];
            $adresse = $row[4];

            mysqli_stmt_bind_param($stmt, "sssss", $nom, $email, $mdp, $telephone, $adresse);
            mysqli_stmt_execute($stmt);
        }

        echo "Importation terminée.";
        exit();
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
        exit();
    }
}
?>




