<?php
// OSM = L'api OpenStreetMap
// Connexion à la base de données et chargement de PhpSpreadsheet
require '../db/db_connect.php';
require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Fonction pour géocoder une adresse en obtenant latitude et longitude via l'API OSM
function geocodeAdresse($adresse) {
    // Construction de l'URL OSM en format JSON
    $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($adresse);

    // Config HTTP + User-Agent obligatoire OSM
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: mon-script-php"
        ]
    ];
    $context = stream_context_create($opts);
    // Envoi de la requête + récupération de la réponse JSON
    $json = file_get_contents($url, false, $context);
    $data = json_decode($json, true);

    // Si des données sont retournées, on extrait la latitude et la longitude sinon null

    if (!empty($data)) {
        return [
            'lat' => $data[0]['lat'],
            'lon' => $data[0]['lon']
        ];
    } else {
        return null;
    }
}

// ID du dépôt par défaut qui est 1
$defaultDepotId = 1;

if (!$conn) {
    die("Connexion à la base de données échouée : " . mysqli_connect_error());
}

// Si un fichier a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excelFile'])) {
    // Récupère le chemin temp du fichier
    $file = $_FILES['excelFile']['tmp_name'];

    if (!$file) {
        die("Fichier introuvable ou invalide.");
    }

    try {
        // Chargement de notre fichier Excel
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();

        // Convertion de notre feuille en tableau PHP
        $rows = $sheet->toArray();

        array_shift($rows); // Supprime la ligne d'en-tête

        // Parcours de chaque ligne du fichier Excel
        foreach ($rows as $row) {
            $nom = $row[0];
            $email = $row[1];
            $code_colis = $row[2];
            $telephone = $row[3];
            $adresse = $row[4];

            // Géocodage de l'adresse de chacuns de nos clients
            $coords = geocodeAdresse($adresse);
            $coordonneeGps = null;
            if ($coords) {
                $lat = $coords['lat'];
                $lon = $coords['lon'];
                $coordonneeGps = "$lat,$lon";
                sleep(1); // Faire dormir une seconde a cause des limites d'utilisation de OSM
            }

            // Insertion dans Utilisateur
            $queryUser = "INSERT INTO Utilisateur (nom, email, mdp, telephone, adresse, types, coordonneeGps) VALUES (?, ?, ?, ?, ?, 'client', ?)";
            $stmtUser = mysqli_prepare($conn, $queryUser);
            mysqli_stmt_bind_param($stmtUser, "ssssss", $nom, $email, $code_colis, $telephone, $adresse, $coordonneeGps);
            mysqli_stmt_execute($stmtUser);

            // Récupère l'ID du nouvel utilisateur inséré
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







