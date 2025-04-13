<?php
session_start(); // Démarrer la session
include_once 'db/db_connect.php';  // Inclure la connexion à la base de données

// Vérifier si la connexion à la base est établie
if (!isset($conn) || $conn === false) {
die("Erreur de connexion à la base de données.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_search'])) {
$colis = mysqli_real_escape_string($conn, trim($_POST['colis']));

if (empty($colis)) {
    $error_message_search = "Veuillez entrer un numéro de colis.";
} else {
    $sql = "SELECT * FROM `Colis` WHERE `code_colis`='$colis'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("Erreur SQL (Colis) : " . mysqli_error($conn));
    }
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $search_result = "Colis trouvé ! Statut : " . htmlspecialchars($row['statut']) . 
                            ", Date de réception prévue : " . htmlspecialchars($row['date_reception']);
    } else {
        $error_message_search = "Le colis n'existe pas dans la base de données.";
    }
}
}

// Vérifie si le formulaire client est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_client'])) {
// Nettoyer et échapper les entrées utilisateur pour éviter les injections SQL
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
        // Si le colis est trouvé, stocker les données dans la session et rediriger
        $_SESSION['name'] = $name;
        $_SESSION['colis'] = $colis;
        header("Location: Menu_Client.php");
        exit();
    } else {
        $error_message = "Numéro de colis incorrect.";
    }
} else {
    $error_message = "Nom incorrect.";
}
}

// Vérifie si le formulaire employé est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_employee'])) {
// Nettoyer et échapper les entrées utilisateur
$employee_name = mysqli_real_escape_string($conn, trim($_POST['employee_name']));
$employee_password = mysqli_real_escape_string($conn, trim($_POST['employee_password']));

// Requête SQL pour récupérer les informations de l'employé
$sql_employee = "SELECT * FROM `Utilisateur` WHERE `nom`='$employee_name' AND `mdp`='$employee_password'";
$result_employee = mysqli_query($conn, $sql_employee);

if (!$result_employee) {
    die("Erreur SQL (Employés) : " . mysqli_error($conn));
}

if (mysqli_num_rows($result_employee) > 0) {
    $row = mysqli_fetch_assoc($result_employee);
    $user_type = $row['types']; // "admin" ou "employé"

    // Stocker les informations dans la session
    $_SESSION['employee_name'] = $employee_name;

    // Rediriger en fonction du type d'utilisateur
    if ($user_type === "admin") {
        header("Location: admin.php");
    } else {
        header("Location: livreur_main.php");
    }
    exit();
} else {
    $error_message_employee = "Nom ou mot de passe incorrect.";
}
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Page d'Accueil</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="background-image">
    <div class="top-left">
        <a href="index.html" class="icon-home">
            <img src="img/accueil.png" alt="Accueil">
        </a>
    </div>
    <div class="top-center">
        <div class="vertical-bar">
            <span class="menu-item" data-action="search">Recherche Colis</span>
            <span class="menu-item" data-action="employees">Espace Employés</span>
            <span class="menu-item" data-action="clients">Modifier Livraison</span>
        </div>
        <div id="search-box" class="hidden-content">
            <!-- Messages d'erreur ou de succès -->
            <?php if (isset($error_message_search)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message_search) ?></div>
            <?php endif; ?>

            <?php if (isset($search_result)): ?>
                <div class="search-result"><?= htmlspecialchars($search_result) ?></div>
            <?php endif; ?>

            <!-- Formulaire de recherche -->
            <form method="POST" action="">
                <input type="text" id="colis" name="colis" placeholder="Entrez un numéro de colis..." required>
                <button type="submit" name="submit_search">Rechercher</button>
            </form>
        </div>
        <div id="employee-login" class="hidden-content">
            <h3>Espace Employés</h3>
            <?php if (isset($error_message_employee)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message_employee) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="text" id="employee_name" name="employee_name" placeholder="Nom" required>
                <input type="password" id="employee_password" name="employee_password" placeholder="Mot de passe" required>
                <button type="submit" name="submit_employee">Connexion</button>
            </form>
        </div>
        <div id="client-login" class="hidden-content">
            <h3>Modifier Livraison</h3>
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="text" id="name" name="name" placeholder="Nom du client" required>
                <input type="text" id="colis" name="colis" placeholder="Numéro de colis" required>
                <button type="submit" name="submit_client">Valider</button>
            </form>
        </div>
    </div>
    <div class="bottom-left">
        <h1>TransPlac</h1>
        <p><strong>Chèr(e) client(e),</strong> bienvenue sur <em>TransPlac.com</em>.</p>
        <p><strong>Votre destination pour une livraison rapide, simple, et sécurisée.</strong></p>
        <p>TransPlac vous accompagne pour accéder à vos colis, où que vous soyez, avec une fiabilité à toute épreuve.</p>
    </div>
    <div class="bottom-right">
        <div class="big-block"></div>
        <div class="small-blocks">
            <div class="small-block1"></div>
            <div class="small-block2"></div>
        </div>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>
