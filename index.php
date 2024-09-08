<?php
session_start();
include 'db_config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blank Electronics</title>
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
            <!-- Meniu drowdown oferte -->
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
        <!-- Dropdown my account -->
        <nav>
            <ul>
            <?php if (isset($_SESSION['username'])): ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Contul Meu</a>
                <div class="dropdown-content">
                        <a href="account_info.php">Info Cont</a>
                        <a href="my_purchases.php">Cumparaturile Mele</a>
                        <a href="logout.php">Logout</a>
                <div>
            </li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li> 
            <?php endif; ?>
                    
                </li>
                <!-- Shopping Cart  -->
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
   <section id="offers" class="main-content">
        <h2>Ofertele de top!</h2>
        <?php
        $sql = "SELECT * FROM offers ORDER BY buyers DESC LIMIT 3";
        $result = $conn->query($sql);
//Query pentru a afișa ofertele, top 3 în funcție de numărul de buyers
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='offer'>";
                echo "<img src='" . $row['photo'] . "' alt='Product Image'>";
                echo "<h3><a href='product.php?id=" . htmlspecialchars($row['id']) . "'>" . ($row['name']) . "</a></h3>";
                echo "<p>Price: $" . $row['price'] . "</p>";
                echo "</div><br>";
            }
        } else {
            echo "<p>No offers available at the moment.</p>";
        }
        ?>
        <br><br>
        <?php if (isset($_SESSION['role'])): ?>
<!-- Formular de contact care trimite mail companiei -->
            <h2>Formular de contact</h2>
            <form action="send_email.php" method="post">
                <label for="name">Nume:</label>
                <input type="text" id="name" name="name" required>
                <label for="message">Mesaj:</label>
                <textarea id="message" name="message" required></textarea>
                <input type="submit" value="Send">
            </form>
            <br>
        <?php endif; ?>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> Blank Electronics. All rights reserved.</p>
        </footer>
    </section>
</main>
</body>
</html>
