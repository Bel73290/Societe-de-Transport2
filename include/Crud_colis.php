<?php
/
 
Gestion des Livraisons*/,
include '../db/db_connect.php'; 
$debeug = true;

function insert_Livraison($conn, $id_colis, $id_employe, $id_tranche_horaire, $statut, $date_livraison, $id_depot) {
    $sql = "INSERT INTO Livraison (id_colis, id_employe, id_tranche_horaire, statut, date_livraison, id_depot) 
            VALUES ('$id_colis', '$id_employe', '$id_tranche_horaire', '$statut', '$date_livraison', '$id_depot')";
    global $debeug;
    if ($debeug) echo $sql . "<br>";
    return mysqli_query($conn, $sql);
}

function update_Livraison($conn, $id, $id_colis, $id_employe, $id_tranche_horaire, $statut, $date_livraison, $id_depot) {
    $sql = "UPDATE Livraison SET 
                id_colis='$id_colis', 
                id_employe='$id_employe', 
                id_tranche_horaire='$id_tranche_horaire', 
                statut='$statut', 
                date_livraison='$date_livraison', 
                id_depot='$id_depot' 
            WHERE id = $id";
    global $debeug;
    if ($debeug) echo $sql . "<br>";
    return mysqli_query($conn, $sql);
}

function delete_Livraison($conn, $id) {
    $sql = "DELETE FROM Livraison WHERE id = $id";
    global $debeug;
    if ($debeug) echo $sql . "<br>";
    return mysqli_query($conn, $sql);
}

function select_Livraison($conn, $id) {
    $sql = "SELECT * FROM Livraison WHERE id = $id";
    global $debeug;
    if ($debeug) echo $sql . "<br>";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($res);
}

function list_Livraisons($conn) {
    $sql = "SELECT * FROM Livraison";
    global $debeug;
    if ($debeug) echo $sql . "<br>";
    $res = mysqli_query($conn, $sql);
    return rs_to_tab($res);
}

/
 
Fonction auxiliaire pour transformer un result set en tableau*/,
function rs_to_tab($rs) {
    $tab = [];
    while ($row = mysqli_fetch_assoc($rs)) {
        $tab[] = $row;
    }
    return $tab;
}
?>