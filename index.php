<?php
session_start(); // Démarrer la session
include_once 'db/db_connect.php';  // Inclure la connexion à la base de données

// Vérifier si la connexion est établie
if (!isset($conn) || $conn === false) {
    die("Erreur de connexion à la base de données.");
}

// Vérifie si le formulaire client est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_client'])) {
    // Nettoyer et échapper les entrées pour le client
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $colis = mysqli_real_escape_string($conn, trim($_POST['colis']));

    // Requête SQL pour récupérer l'ID du client
    $sql_id_client = "SELECT id FROM `Utilisateur` WHERE `nom`='$name'";
    $result_id = mysqli_query($conn, $sql_id_client);

    if (!$result_id) {
        die("Erreur SQL (Utilisateur) : " . mysqli_error($conn));
    }

    // Vérifier si un ID de client a été trouvé
    if (mysqli_num_rows($result_id) > 0) {
        $row = mysqli_fetch_assoc($result_id);
        $id_client = $row['id'];

        // Requête SQL pour vérifier le colis associé au client
        $sql = "SELECT * FROM `Colis` WHERE `code_colis`='$colis' AND `id_client`='$id_client'";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Erreur SQL (Colis) : " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['name'] = $name;
            $_SESSION['colis'] = $colis;
            header("Location: Client/Menu_Client.php");
            exit();
        } else {
            echo "Numéro de colis incorrect.";
        }
    } else {
        echo "Nom incorrect.";
    }

    mysqli_close($conn);
}
?>


<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>TranspLac</title>
  <link rel="stylesheet" href="css/Client.css">
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
      <h1>Horaire de livraison</h1>
      <form method="POST" action="">
        <label for="name">Nom :</label>
        <input type="text" id="name" name="name" required minlength="4" maxlength="8"  />
        <label for="colis">Numéro du colis :</label>
        <input type="text" id="colis" name="colis" required minlength="4" maxlength="50"  />
        <button class="button" type="submit" name="submit_client">Connexion</button>
      </form>
    </div>
</body>
</html>
