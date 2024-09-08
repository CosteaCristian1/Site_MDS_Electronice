<?php
//Configurare conexiune bază de date
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blank_electronics";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>
