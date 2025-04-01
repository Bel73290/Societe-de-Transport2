<?php
session_start(); // Démarrer la session
include_once 'db/db_connect.php';  // Inclure la connexion à la base de données
include_once 'db/db_disconnect.php';

// Vérifie si le formulaire client est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_client'])) {
    // Nettoyer les entrées pour le client
    $name = trim($_POST['name']);
    $colis = trim($_POST['colis']);
    
    // Échapper les valeurs pour la base de données
    $name = mysqli_real_escape_string($conn, $name);
    $colis = mysqli_real_escape_string($conn, $colis);

    // Requête SQL pour vérifier le client
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

// Vérifie si le formulaire employé est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_employe'])) {
    // Nettoyer les entrées pour l'employé
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Échapper les valeurs pour la base de données
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // Requête SQL pour vérifier l'existence de l'employé
    $sql = "SELECT * FROM `Employe` WHERE `nom`='$username' AND `mdp`='$password'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Erreur SQL : " . mysqli_error($conn)); 
    }

    // Si l'employé existe, on crée une session
    if (mysqli_num_rows($result) > 0) {
        // Récupérer les informations de l'employé
        $employe = mysqli_fetch_assoc($result);
        $_SESSION['id_employe'] = $employe['id_employe'];
        $_SESSION['nom'] = $employe['nom'];
        $_SESSION['prenom'] = $employe['prenom'];
        $_SESSION['poste'] = $employe['poste'];
        
        // Redirection vers la page de tournée après la connexion
        header("Location: livreur_main.php");
        exit();  
    } else {
        echo "Identifiants incorrects.";
    }

    mysqli_close($conn);
}
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>TranspLac ayoub</title>
  <link rel="stylesheet" href="Societe-de-Transport2/Client/Connect_style.css">
  <script src="script.js"></script>
</head>
<body>
  <img src="img/Logo.png" />
  <div class="connexion">
    <a>Connexion</a>
  </div>
  <div class="box">
    <!-- Formulaire Client -->
    <div class="T_colis">
      <h1>Horraire de livraison (Client)</h1>
      <form method="POST" action="">
        <label for="name">Nom :</label>
        <input type="text" id="name" name="name" required minlength="4" maxlength="8"  />
        <label for="colis">Numéro du colis :</label>
        <input type="text" id="colis" name="colis" required minlength="4" maxlength="50"  />
        <button class="button" type="submit" name="submit_client">Connexion (Client)</button>
      </form>
    </div>

    <!-- Formulaire Employé -->
    <div class="T_colis">
      <h1>Connexion Employé</h1>
      <form method="POST" action="">
        <label for="username">Nom de l'employé :</label>
        <input type="text" id="username" name="username" required minlength="4" maxlength="50" />
        
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required minlength="4" maxlength="50" />
        
        <button class="button" type="submit" name="submit_employe">Se connecter</button>
      </form>
    </div>
  </div>
</body>
</html>
