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

        array_shift($rows); // Supprime la ligne d'en-tête

        foreach ($rows as $row) {
            $nom = $row[0];
            $email = $row[1];
            $code_colis = $row[2];
            $telephone = $row[3];
            $adresse = $row[4];

            //Insertion dans Utilisateur (avec code_colis comme mot de passe)
            $queryUser = "INSERT INTO Utilisateur (nom, email, mdp, telephone, adresse, types) VALUES (?, ?, ?, ?, ?, 'client')";
            $stmtUser = mysqli_prepare($conn, $queryUser);
            mysqli_stmt_bind_param($stmtUser, "sssss", $nom, $email, $code_colis, $telephone, $adresse);
            mysqli_stmt_execute($stmtUser);


            // Recupération de l'id de L'utilisateur ajouté
            $id_client = mysqli_insert_id($conn);

            //Insertion dans Colis
            $queryColis = "INSERT INTO Colis (code_colis, id_client) VALUES (?, ?)";
            $stmtColis = mysqli_prepare($conn, $queryColis);
            mysqli_stmt_bind_param($stmtColis, "si", $code_colis, $id_client);
            mysqli_stmt_execute($stmtColis);
        }

        echo "<script>alert('Importation réussie'); window.location.href = '../admin.php';</script>";
        exit();
    } catch (Exception $e) {
        echo "<script>alert('Erreur : " . addslashes($e->getMessage()) . "'); window.location.href = '../admin.php';</script>";
        exit();
    }
}
?>






