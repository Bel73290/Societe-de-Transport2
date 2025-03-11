<?php
require 'db_connect.php';
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

        $stmt = $conn->prepare("INSERT INTO Client (nom, prenom, adresse, mail, numero_tel, numero_colis, id_tranche) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($rows as $row) {
            $stmt->execute([$row[0], $row[1], $row[2], $row[3], $row[4], $row[5], !empty($row[6]) ? $row[6] : NULL]);
        }

        header("Location: index.php?status=success");
    } catch (Exception $e) {
        header("Location: index.php?status=error");
    }
}
?>


