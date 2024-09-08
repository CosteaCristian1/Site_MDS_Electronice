<?php
session_start();
include 'db_config.php';
//Verifică rolul utilizatorului ca un guest sau user normal să nu poată da delete
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}
//Găsește id-ul folosind metode 'GET'
if (isset($_GET['id'])) {
    $id = $_GET['id'];
//Pregătește statement delete
    $sql = "DELETE FROM offers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
//Execută statement
    if ($stmt->execute()) {
        echo "Offer deleted successfully.";
        header("view_offers.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
