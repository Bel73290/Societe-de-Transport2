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





