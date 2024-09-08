<?php
session_start();
include 'db_config.php';

// Inițializează suma totală
$_SESSION['total'] = 0;

// Verifică dacă coșul este gol
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $cart_items = [];
} else {
    // Numără de câte ori apare fiecare articol în coș
    $cart_items = array_count_values($_SESSION['cart']);
}

// Dacă coșul nu este gol, obține articolele din baza de date
$offers = [];
if (!empty($cart_items)) {
    // Pregătește un șir de plasare pentru interogare, pe baza numărului de articole unice din coș
    $placeholders = implode(',', array_fill(0, count($cart_items), '?'));
    
    // Construiește interogarea pentru a obține ofertele bazate pe ID-urile unice din coș
    $query = "SELECT id, photo, name, price FROM offers WHERE id IN ($placeholders)";
    
    // Pregătește interogarea
    $stmt = $conn->prepare($query);
    
    // Leagă parametrii dinamic
    $stmt->bind_param(str_repeat('i', count($cart_items)), ...array_keys($cart_items));
    
    // Execută interogarea
    $stmt->execute();
    
    // Obține rezultatele
    $result = $stmt->get_result();
    
    // Stochează ofertele într-un array
    while ($row = $result->fetch_assoc()) {
        $offers[] = $row;
    }
}

// Funcție pentru a elimina un articol din coș
function removeFromCart($id) {
    // Găsește indexul primei apariții a articolului în coș
    if (($key = array_search($id, $_SESSION['cart'])) !== false) {
        // Elimină o apariție a articolului
        unset($_SESSION['cart'][$key]);
        // Reindexează coșul
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

// Gestionează eliminarea articolelor din coș
if (isset($_POST['remove_item_id'])) {
    $remove_item_id = $_POST['remove_item_id'];
    removeFromCart($remove_item_id);
    header('Location: shopping_cart.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-image: url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; margin: 0; padding: 0;">
<div class="logo-container">
    <a href="index.php"><img src="logo.jpg" alt="Logo" class="logo"></a>
</div>
<div class="header">
    <div class="nav-left">
        <nav>
            <ul>
                <!-- Înapoi la Oferte -->
                <li><a href="view_offers.php">Continuă cumpărăturile</a></li>
            </ul>
        </nav>
    </div>

    <div class="nav-right">
        <!-- Dropdown pentru Contul Meu -->
        <nav>
            <ul>
                <?php if (isset($_SESSION['username'])): ?>
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Contul Meu</a>
                    <div class="dropdown-content">
                        <a href="account_info.php">Info Cont</a>
                        <a href="my_purchases.php">Cumpărăturile Mele</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
                <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <?php endif; ?>
                <!-- Iconiță Coș de Cumpărături -->
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
    <section id="cart" class="main-content">
        <h2>Coșul de Cumpărături</h2>
        <?php if (empty($offers)) { ?>
            <p>Coșul este gol. <a href="view_offers.php">Înapoi la oferte</a></p>
        <?php } else { ?>
            <div class="cart-container">
                <?php foreach ($offers as $offer) { 
                    $quantity = $cart_items[$offer['id']]; // Numărul de ori în care acest articol a fost adăugat în coș
                    $total_price = $offer['price'] * $quantity; // Calculează prețul total pentru acest articol
                    $_SESSION['total'] += $total_price; // Adaugă la suma totală a sesiunii
                ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($offer['photo']); ?>" alt="Product Image">
                        <h3><?php echo htmlspecialchars($offer['name']); ?> (x<?php echo $quantity; ?>)</h3>
                        <p>Preț pe unitate: <?php echo htmlspecialchars($offer['price']); ?> Lei</p>
                        <p>Total pentru acest produs: <?php echo $total_price; ?> Lei</p>
                        <!-- Formular pentru a elimina o instanță a acestui articol -->
                        <form method="post" action="shopping_cart.php">
                            <input type="hidden" name="remove_item_id" value="<?php echo $offer['id']; ?>">
                            <input type="submit" value="Șterge produs">
                        </form>
                        <br>
                    </div>
                <?php } ?>
            </div>
            <h3>Total Coș: <?php echo $_SESSION['total']; ?> Lei</h3>
            <form method="post" action="checkout.php">
    <?php if (!isset($_SESSION['username'])): ?>
        <label for="name">Nume:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    <?php endif; ?>
    <input type="submit" value="Finalizează comanda">
</form>

        <?php } ?>


    <footer>
        <p>&copy; <?php echo date('Y'); ?> Blank Electronics. All rights reserved.</p>
    </footer>
    </section>
</main>
</body>
</html>
