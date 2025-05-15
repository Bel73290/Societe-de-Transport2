<?php
// Affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 1- Connexion à la base de données
$conn = mysqli_connect("localhost", "info1", "1G1", "info1");
//$conn = mysqli_connect("localhost", "root", "", "livraison_db");
mysqli_set_charset($conn, "utf8");

// Vérification de la connexion
if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}
?>