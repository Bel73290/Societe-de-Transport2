<?php
require '../db/db_connect.php';
require 'vendor/autoload.php'; // Chargement de PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

function geocodeAdresse($adresse) {
    $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($adresse);
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: mon-script-php"
        ]
    ];
    $context = stream_context_create($opts);
    $json = file_get_contents($url, false, $context);
    $data = json_decode($json, true);

    if (!empty($data)) {
        return [
            'lat' => $data[0]['lat'],
            'lon' => $data[0]['lon']
        ];
    } else {
        return null;
    }
}

// ID du dépôt par défaut (Le Bourget-du-Lac)
$defaultDepotId = 1; // À ajuster selon ton id exact dans la table Depot

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

            // Géocodage de l'adresse client
            $coords = geocodeAdresse($adresse);
            $coordonneeGps = null;
            if ($coords) {
                $lat = $coords['lat'];
                $lon = $coords['lon'];
                $coordonneeGps = "$lat,$lon";
                sleep(1); // Pour respecter l'API OSM
            }

            // Insertion dans Utilisateur
            $queryUser = "INSERT INTO Utilisateur (nom, email, mdp, telephone, adresse, types, coordonneeGps) VALUES (?, ?, ?, ?, ?, 'client', ?)";
            $stmtUser = mysqli_prepare($conn, $queryUser);
            mysqli_stmt_bind_param($stmtUser, "ssssss", $nom, $email, $code_colis, $telephone, $adresse, $coordonneeGps);
            mysqli_stmt_execute($stmtUser);

            $id_client = mysqli_insert_id($conn);

            // Insertion dans Colis (structure originale conservée)
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







