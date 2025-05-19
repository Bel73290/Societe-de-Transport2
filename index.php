<?php
session_start();                         // Démarrer la session
include_once 'db/db_connect.php';        // Connexion MySQL

/* ——— Sécurité : s’assurer que la connexion MySQL existe ——— */
if (!isset($conn) || $conn === false) {
    die('Erreur de connexion à la base de données.');
}

/* ——— 1) Formulaire « Recherche colis » ——— */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_search'])) {
    $colis = mysqli_real_escape_string($conn, trim($_POST['colis']));

    if (empty($colis)) {
        $error_message_search = "Veuillez entrer un numéro de colis.";
    } else {
        $sql = "SELECT Colis.*, Utilisateur.nom, Utilisateur.adresse
                FROM Colis
                JOIN Utilisateur ON Colis.id_client = Utilisateur.id
                WHERE Colis.code_colis = '$colis'";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Erreur SQL (Colis) : " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
            $colis_data = mysqli_fetch_assoc($result);
        } else {
            $error_message_search = "Le colis n'existe pas dans la base de données.";
        }
    }
}

/* ——— 2) Formulaire « Modifier livraison » (clients) ——— */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_client'])) {
    $name  = mysqli_real_escape_string($conn, trim($_POST['name']));
    $colis = mysqli_real_escape_string($conn, trim($_POST['colis']));

    $sql_id_client = "SELECT id FROM `Utilisateur` WHERE `nom`='$name'";
    $result_id     = mysqli_query($conn, $sql_id_client) or die('Erreur SQL (Utilisateur) : '.mysqli_error($conn));

    if (mysqli_num_rows($result_id)) {
        $row       = mysqli_fetch_assoc($result_id);
        $id_client = $row['id'];

        $sql     = "SELECT * FROM `Colis` WHERE `code_colis`='$colis' AND `id_client`='$id_client'";
        $result  = mysqli_query($conn, $sql) or die('Erreur SQL (Colis) : '.mysqli_error($conn));

        if (mysqli_num_rows($result)) {
            /* Stockage session et redirection client */
            $_SESSION['id']   = $id_client;
            $_SESSION['name'] = $name;
            $_SESSION['colis']= $colis;
            header('Location: client.php');
            exit();
        }
        $error_message = 'Numéro de colis incorrect.';
    } else {
        $error_message = 'Nom incorrect.';
    }
}

/* ——— 3) Formulaire « Espace employés » ——— */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_employee'])) {
    $employee_name     = mysqli_real_escape_string($conn, trim($_POST['employee_name']));
    $employee_password = mysqli_real_escape_string($conn, trim($_POST['employee_password']));

    $sql_employee = "SELECT * FROM `Utilisateur` WHERE `nom`='$employee_name' AND `mdp`='$employee_password'";
    $result_employee = mysqli_query($conn, $sql_employee) or die('Erreur SQL (Employés) : '.mysqli_error($conn));

    if (mysqli_num_rows($result_employee)) {
        $row       = mysqli_fetch_assoc($result_employee);
        $user_type = $row['types'];          // 'admin' ou 'employe'

        /* ----------- STOCKAGE SESSION ----------- */
        $_SESSION['employee_name'] = $employee_name;
        $_SESSION['role']          = $user_type;
        $_SESSION['id']            = $row['id'];   

        /* ----------- REDIRECTION ----------- */
        if ($user_type === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: livreur_main.php');
        }
        exit();
    }
    $error_message_employee = 'Nom ou mot de passe incorrect.';
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TransPlac - Page d'Accueil</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="background-image">
    <div class="top-left">
        <a href="index.php" class="icon-home">
            <img src="img/accueil.png" alt="Accueil">
        </a>
    </div>

    <div class="top-center">
        <div class="vertical-bar">
            <span class="menu-item" data-action="search">Recherche Colis</span>
            <span class="menu-item" data-action="employees">Espace Employés</span>
            <span class="menu-item" data-action="clients">Modifier Livraison</span>
        </div>

        <!-- Recherche colis -->
        <?php $search_active = isset($_POST['submit_search']) || isset($colis_data) || isset($error_message_search); ?>
        <div id="search-box" class="<?= $search_active ? '' : 'hidden-content' ?>">
            <h3>Recherche Colis</h3>

            <?php if (isset($colis_data)): ?>
                <div class="colis-result">
                    <p><strong>Client :</strong> <?= htmlspecialchars($colis_data['nom']) ?></p>
                    <p><strong>Adresse :</strong> <?= htmlspecialchars($colis_data['adresse']) ?></p>
                    <p><strong>Code colis :</strong> <?= htmlspecialchars($colis_data['code_colis']) ?></p>
                    <p><strong>Statut :</strong> <?= htmlspecialchars($colis_data['statut']) ?></p>
                    <p><strong>Date réception prévue :</strong> <?= htmlspecialchars($colis_data['date_reception']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message_search)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message_search) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="form-style">
                <label for="colis">Entrer un numéro de colis :</label>
                <input type="text" id="colis" name="colis" placeholder="numéro de colis" required>
                <button type="submit" name="submit_search">Rechercher</button>
            </form>
        </div>

        <!-- Espace employés -->
        <div id="employee-login" class="hidden-content">
            <h3>Espace Employés</h3>
            <?php if (isset($error_message_employee)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message_employee) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="text"     name="employee_name"     placeholder="Nom"          required>
                <input type="password" name="employee_password" placeholder="Mot de passe" required>
                <button type="submit" name="submit_employee">Connexion</button>
            </form>
        </div>

        <!-- Espace clients -->
        <div id="client-login" class="hidden-content">
            <h3>Modifier Livraison</h3>
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="text" name="name"  placeholder="Nom du client" required>
                <input type="text" name="colis" placeholder="Numéro de colis" required>
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
