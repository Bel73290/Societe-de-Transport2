<!doctype html>
<?php
session_start(); // Démarrer la session
include_once '../db/db_connect.php';

// Vérifie si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nettoyer les entrées
    $name = trim($_POST['name']);
    $colis = trim($_POST['colis']);
    
    // Échapper les valeurs pour la base de données
    $name = mysqli_real_escape_string($conn, $name);
    $colis = mysqli_real_escape_string($conn, $colis);

    // Requête SQL
    $sql = "SELECT * FROM `Client` WHERE `nom`='$name' AND `numero_colis`='$colis'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Erreur SQL : " . mysqli_error($conn)); 
    }

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['name'] = $name;
		$_SESSION['colis'] = $colis;
        header("Location: Menu_Client.php");
        exit();  
    } else {
        echo "Nom ou numéro du colis incorrect.";
    }

    mysqli_close($conn);
}
?>

<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>TranspLac</title>
  <link rel="stylesheet" href="Client.css">
  <script src="script.js"></script>
</head>
<body>
  <img src="img/Logo.png" />
  <div class="connexion">
    <a>Connexion</a>
  </div>
  <div class="box">
  

    
  <div class="T_colis">
    
    <h1>Horraire de livraison</h1>
	<form method="POST" action="">
    <label for="name">Nom :</label>
    <input type="text" id="name" name="name" required minlength="4" maxlength="8"  />
    <label for="name">Numéro du colis :</label>
    <input type="text" id="colis" name="colis" required minlength="4" maxlength="50"  />
    <button class="button" type="submit"> Connexion</button>
	</form>
  </div>
</div>
  
</body>
</html>