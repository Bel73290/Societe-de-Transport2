<!doctype html>
<?php
session_start(); // Démarrer la session
?>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>TranspLac</title>
  <link rel="stylesheet" href="../css/Menu_Client.css">

</head>
<body>
    <div>
        <img src="../img/Logo.png"/>
        <h1>Choisissez votre horaire de livraison</h1>
    </div>
    <div class="client">
        <?php
        if (isset($_SESSION['name']) && isset($_SESSION['colis'])) {
            // Si les données sont présentes, on les affiche
            echo "<p>Nom : " . htmlspecialchars($_SESSION['name']) . "</p>";
            echo "<p>Numéro de colis : " . htmlspecialchars($_SESSION['colis']) . "</p>";
        } else {
            // Sinon, on affiche un message d'avertissement
            echo "<p style='color:red;'>Données manquantes ou session expirée.</p>";
        }
        ?>
    </div>
    <div class="Planning">
            <div class="grille">
                <div class="day">
                    <a></a>
                </div>
                <div class="Horraire">
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_8_10')">Matin: Entre 8h et 10h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_10_12')">Matin: Entre 10h et 12h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_13_16')">Après-midi: Entre 13h et 16h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_16_19')">Après-midi: Entre 16h et 19h</a>
                </div>
            </div>
        <div class="grille">
            <div class="day">
                <a></a>
            </div>
            <div class="Horraire">
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_8_10')">Matin: Entre 8h et 10h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_10_12')">Matin: Entre 10h et 12h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_13_16')">Après-midi: Entre 13h et 16h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_16_19')">Après-midi: Entre 16h et 19h</a>
            </div>
        </div>
        <div class="grille">
            <div class="day">
                <a></a>
            </div>
            <div class="Horraire">
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_8_10')">Matin: Entre 8h et 10h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_10_12')">Matin: Entre 10h et 12h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_13_16')">Après-midi: Entre 13h et 16h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_16_19')">Après-midi: Entre 16h et 19h</a>
            </div>
        </div>
        <div class="grille">
            <div class="day">
                <a></a>
            </div>
            <div class="Horraire">
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_8_10')">Matin: Entre 8h et 10h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_10_12')">Matin: Entre 10h et 12h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_13_16')">Après-midi: Entre 13h et 16h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_16_19')">Après-midi: Entre 16h et 19h</a>
            </div>
        </div>
        <div class="grille">
            <div class="day">
                <a></a>
            </div>
            <div class="Horraire">
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_8_10')">Matin: Entre 8h et 10h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_10_12')">Matin: Entre 10h et 12h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_13_16')">Après-midi: Entre 13h et 16h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_16_19')">Après-midi: Entre 16h et 19h</a>
            </div>
        </div>
        <div class="grille">
            <div class="day">
                <a></a>
            </div>
            <div class="Horraire">
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_8_10')">Matin: Entre 8h et 10h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_10_12')">Matin: Entre 10h et 12h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_13_16')">Après-midi: Entre 13h et 16h</a>
                <a href="confirmation.php" class="horaire" onclick="selectHoraire('day1_16_19')">Après-midi: Entre 16h et 19h</a>
            </div>
			
        </div>
    </div>

</body>
</html>

<script src="script_menu.js"></script>