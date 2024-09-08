<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}



// Verifică dacă un articol este adăugat în coș
if (isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
// Adaugă articolul în array-ul de sesiune cart
    $_SESSION['cart'][] = $item_id;
// Redirecționează înapoi pentru a evita resubmit-ul formularului la reîncărcarea paginii
    header('Location: view_offers.php');
    exit();
}

// Verifică dacă 'id' este prezent în URL
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

// Interogare pentru a obține detaliile produsului din baza de date
    $query = "SELECT * FROM offers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

// Verifică dacă produsul există
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Produsul nu a fost găsit.";
        exit;
    }
} else {
    echo "ID-ul produsului nu a fost specificat.";
    exit;
}

// Verifică dacă utilizatorul este autentificat și a cumpărat produsul
$purchased = false;
$rating_error = '';

if (isset($_SESSION['username'])) {
    $user_id = $_SESSION['id'];

    $purchase_query = "
        SELECT oi.offerid
        FROM orders o
        JOIN order_items oi ON o.orderid = oi.orderid
        WHERE o.userid = ? AND oi.offerid = ?
    ";
    $stmt = $conn->prepare($purchase_query);
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    $purchase_result = $stmt->get_result();

    if ($purchase_result->num_rows > 0) {
        $purchased = true;
    }
}

// Gestionează trimiterea evaluării
if (isset($_POST['submit_rating']) && $purchased) {
    $rating = intval($_POST['rating']);

    if ($rating >= 1 && $rating <= 5) {
    // Calculează noua evaluare
        $new_rating_query = "
            UPDATE offers 
            SET rating = ((rating * buyers) + ?) / (buyers + 1),
                buyers = buyers + 1
            WHERE id = ?
        ";
        $stmt = $conn->prepare($new_rating_query);
        $stmt->bind_param('ii', $rating, $product_id);
        if ($stmt->execute()) {
            header('Location: product.php?id=' . $product_id);
            exit();
        } else {
            $rating_error = "A apărut o eroare la salvarea evaluării.";
        }
    } else {
        $rating_error = "Evaluarea trebuie să fie între 1 și 5.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Detalii Produs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-image: url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; margin: 0; padding: 0;">
<div class="logo-container">
    <a href="index.php"><img src="logo.jpg" alt="Logo" class="logo"></a>
</div>
<div class="search-bar">
    <form action="view_offers.php" method="get">
        <input type="text" name="search" placeholder="Cauta oferte...">
        <button type="submit">Cauta</button>
    </form>
</div>
<br>
<div class="header">
<div class="nav-left">
    <nav>
        <ul>
            <!-- Dropdown Menu for Offers -->
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
        <!-- My Account Dropdown -->
        <nav>
            <ul>
            <?php if (isset($_SESSION['username'])): ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Contul Meu</a>
                <div class="dropdown-content">
                        <a href="account_info.php">Info Cont</a>
                        <a href="my_purchases.php">Cumparaturile Mele</a>
                        <a href="logout.php">Logout</a>
                </div>
            </li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li> 
            <?php endif; ?>
                    
                </li>
                <!-- Shopping Cart Icon -->
                <li class="cart-icon">
                    <a href="shopping_cart.php">
                        <img src="cart_icon.png" alt="Shopping Cart" class="cart-image">
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>


<div class="main-content">
    <div class="product-image">
        <img src="<?php echo htmlspecialchars($product['photo']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>
    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
    <div class="product-info">
        <p><strong>Preț:</strong> <?php echo htmlspecialchars($product['price']); ?> Lei</p>
        <p><strong>Rating:</strong> <?php echo number_format($product['rating'], 2); ?> / 5</p>
        <p><strong>Descriere:</strong></p>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
    </div>
    <form method="post" action="product.php?id=<?php echo htmlspecialchars($product['id']); ?>">
        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($product['id']); ?>">
        <button type="submit" name="add_to_cart">Adaugă în coș</button>
    </form>

    <?php if (isset($_SESSION['username']) && $purchased): ?>
        <h2>Lasă o evaluare</h2>
        <form method="post" action="product.php?id=<?php echo htmlspecialchars($product_id); ?>">
            <label for="rating">Alege evaluarea de la 1 la 5:</label>
            <select name="rating" id="rating" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
            <button type="submit" name="submit_rating">Trimite Evaluarea</button>
        </form>
        <?php if ($rating_error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($rating_error); ?></p>
        <?php endif; ?>
    <?php elseif (isset($_SESSION['username'])): ?>
        <p>Trebuie să cumperi acest produs pentru a putea lăsa o evaluare.</p>
    <?php else: ?>
        <p><a href="login.php">Conectează-te</a> pentru a lăsa o evaluare.</p>
    <?php endif; ?>

</div>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Blank Electronics. All rights reserved.</p>
</footer>
</body>
</html>
