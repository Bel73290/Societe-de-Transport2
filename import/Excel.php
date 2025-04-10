<?php
require '../db/db_connect.php';
require 'vendor/autoload.php'; // Chargement de PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excelFile'])) {
    $file = $_FILES['excelFile']['tmp_name'];

    if (!$file) {
        header("Location: index.php?status=error");
        exit();
    }

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        array_shift($rows); // Supprime la ligne d'en-tête

        $stmt = mysqli_prepare($conn, "INSERT INTO Utilisateur (nom, email, mot_de_passe, telephone, adresse, type) VALUES (?, ?, ?, ?, ?, 'client')");

        foreach ($rows as $row) {
            $nom = $row[0];
            $email = $row[1];
            $mot_de_passe = password_hash($row[2], PASSWORD_DEFAULT); 
            $telephone = $row[3];
            $adresse = $row[4];

            mysqli_stmt_bind_param($stmt, "sssss", $nom, $email, $mot_de_passe, $telephone, $adresse);
            mysqli_stmt_execute($stmt);
        }

        header("Location: index.php?status=success");
    } catch (Exception $e) {
        header("Location: index.php?status=error");
    }
}
?>




