<?php
include "../db/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_livraison = intval($_POST["id_livraison"]);
    $status = $_POST["status"] === "livré" ? "livré" : "non livré";

    $sql = "UPDATE Livraisons SET status = '$status' WHERE id_livraison = $id_livraison";
    mysqli_query($conn, $sql);
}

include "../db/db_disconnect.php";
?>
