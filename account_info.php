<?php
session_start();
include 'db_config.php';

// Verifică dacă utilizatorul este conectat
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirecționează la pagina de login dacă nu ești autentificat
    exit();
}

// Obține username-ul din sesiune
$id = $_SESSION['id'];

// Pregătește și execută interogarea pentru a obține datele utilizatorului
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

// Verifică dacă utilizatorul există în baza de date
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $email = $user['email'];
} else {
    $email = 'No email found'; // În cazul în care utilizatorul nu este găsit
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-image: url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; margin: 0; padding: 0;">
<div class="logo-container">
    <a href="index.php"><img src="logo.jpg" alt="Logo" class="logo"></a>
</div>
<div class="search-bar">
    <form action="view_offers.php" method="get">
        <input type="text" name="search" placeholder="Cauta oferte..." required>
        <button type="submit">Cauta</button>
    </form>
</div>
<br>
<div class="header">
<div class="nav-left">
    <nav>
        <ul>
            <!-- Meniu dropdown pentru categorii -->
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Categorii</a>
                <div class="dropdown-content">
                    <a href="view_offers.php?category=TELEFON">Telefoane</a>
                    <a href="view_offers.php?category=LAPTOP">Laptop-uri</a>
                    <a href="view_offers.php?category=PC">PC-uri</a>
                    <a href="view_offers.php?category=TELEVIZOR">Televizoare</a>
                    <a href="view_offers.php?category=ELECTROCASNICE">Electrocasnice</a>
                </div>
            </li>
        </ul>
    </nav>
</div>

    <div class="nav-right">
        <!-- Dropdown contul meu -->
        <nav>
            <ul>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Contul Meu</a>
                <div class="dropdown-content">
                        <a href="my_purchases.php">Cumparaturile Mele</a>
                        <a href="logout.php">Logout</a>
                <div>
            </li>
                    
                </li>
                <!-- Shopping Cart -->
                <li class="cart-icon">
                    <a href="shopping_cart.php">
                        <img src="cart_icon.png" alt="Shopping Cart" class="cart-image">
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<main>
    <div class="main-content">
        <h1>My Account</h1>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p><br><br>
        <h2>Wanna change your password?</h2>
        <a href="change_password_request.php">Change your password</a>
    </div>
</main>
</body>
</html>
