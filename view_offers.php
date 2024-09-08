<?php
session_start();
include 'db_config.php';

// Initialize cart session if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if an item is being added to the cart
if (isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    // Add the item to the cart session array
    $_SESSION['cart'][] = $item_id;
    // Redirect back to avoid resubmission of form on page reload
    header('Location: view_offers.php');
    exit();
}

// Verifică dacă există categoria setată în sesiune
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Filtre din formularul utilizatorului
$subcategory_filter = isset($_GET['subcategory']) ? $_GET['subcategory'] : '';
$price_order = isset($_GET['price_order']) ? $_GET['price_order'] : '';
$rating_order = isset($_GET['rating_order']) ? $_GET['rating_order'] : '';
$buyers_order = isset($_GET['buyers_order']) ? $_GET['buyers_order'] : '';
$maker_filter = isset($_GET['maker']) ? $_GET['maker'] : '';

// Search query
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Construirea query-ului dinamic pentru subcategorii și maker
$query = "SELECT * FROM offers WHERE 1=1";

// Parametrii pentru bind_param
$params = [];
$types = '';

// Filtru pentru căutare
if (!empty($search_query)) {
    $query .= " AND (LOWER(name) LIKE LOWER(?) OR LOWER(description) LIKE LOWER(?))";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= 'ss'; // Add two string types for the LIKE clauses
}

// Filtru pentru categoria din sesiune, dacă este setată
if (!empty($category_filter)) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

// Filtru pentru subcategorie
if (!empty($subcategory_filter)) {
    $query .= " AND subcategory = ?";
    $params[] = $subcategory_filter;
    $types .= 's';
}

// Filtru pentru maker
if (!empty($maker_filter)) {
    $query .= " AND maker = ?";
    $params[] = $maker_filter;
    $types .= 's';
}

// Sortare automată în funcție de cumpărători (implicită)
$order_clause = " ORDER BY buyers DESC";

// Anularea sortării după cumpărători dacă se aplică sortarea după preț sau rating
if (!empty($price_order)) {
    $order_clause = " ORDER BY price " . ($price_order === 'asc' ? 'ASC' : 'DESC');
    if (!empty($rating_order)) {
        $order_clause = " ORDER BY rating " . ($rating_order === 'asc' ? 'ASC' : 'DESC');
    }
} elseif (!empty($rating_order)) {
    $order_clause = " ORDER BY rating " . ($rating_order === 'asc' ? 'ASC' : 'DESC');
}

// Aplică clauza de sortare la query
$query .= $order_clause;

// Pregătirea interogării
$stmt = $conn->prepare($query);

if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

// Execută interogarea și primește rezultatele
$stmt->execute();
$result = $stmt->get_result();

// Obținerea subcategoriilor și producătorilor unici
if (empty($category_filter)) {
    $subcategory_query = "SELECT DISTINCT subcategory FROM offers";
} else {
    // Asigură-te că valoarea este securizată și ghilimelele sunt corect folosite
    $safe_category_filter = $conn->real_escape_string($category_filter);
    $subcategory_query = "SELECT DISTINCT subcategory FROM offers WHERE category = '$safe_category_filter'";
}
$subcategory_result = $conn->query($subcategory_query);

if (empty($category_filter)) {
    $maker_query = "SELECT DISTINCT maker FROM offers";
} else {
    $safe_category_filter = $conn->real_escape_string($category_filter);
    $maker_query = "SELECT DISTINCT maker FROM offers WHERE category = '$safe_category_filter'";
}

$maker_result = $conn->query($maker_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vizualizare Oferte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-image: url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; margin: 0; padding: 0;">
<div class="logo-container">
    <a href="index.php"><img src="logo.jpg" alt="Logo" class="logo"></a>
</div>
<div class="search-bar">
    <form action="view_offers.php" method="get">
        <input type="text" name="search" placeholder="Cauta oferte..." value="<?php echo htmlspecialchars($search_query); ?>">
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

<main>
    <section id="filters" class="main-content">
        <h2>Filtrează ofertele</h2>
        <form method="get" action="view_offers.php">
            <label for="subcategory">Subcategorie:</label>
            <select name="subcategory" id="subcategory">
                <option value="">Toate</option>
                <?php while ($row = $subcategory_result->fetch_assoc()) { ?>
                    <option value="<?php echo htmlspecialchars($row['subcategory']); ?>" 
                        <?php echo ($subcategory_filter === $row['subcategory']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['subcategory']); ?>
                    </option>
                <?php } ?>
            </select>

            <label for="maker">Producător:</label>
            <select name="maker" id="maker">
                <option value="">Toți</option>
                <?php while ($row = $maker_result->fetch_assoc()) { ?>
                    <option value="<?php echo htmlspecialchars($row['maker']); ?>" 
                        <?php echo ($maker_filter === $row['maker']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['maker']); ?>
                    </option>
                <?php } ?>
            </select>

            <label for="price_order">Preț:</label>
            <select name="price_order" id="price_order">
                <option value="">Fără sortare</option>
                <option value="asc" <?php echo ($price_order === 'asc') ? 'selected' : ''; ?>>Crescător</option>
                <option value="desc" <?php echo ($price_order === 'desc') ? 'selected' : ''; ?>>Descrescător</option>
            </select>

            <label for="rating_order">Rating:</label>
            <select name="rating_order" id="rating_order">
                <option value="">Fără sortare</option>
                <option value="asc" <?php echo ($rating_order === 'asc') ? 'selected' : ''; ?>>Crescător</option>
                <option value="desc" <?php echo ($rating_order === 'desc') ? 'selected' : ''; ?>>Descrescător</option>
            </select>

            <input type="submit" value="Aplică filtre">
        </form>
    </section>

    <section id="offers" class="main-content">
        <?php if (isset($_SESSION['role']) && $_SESSION['role']=='admin') { ?>
                    <a href="add_offer.php" class="button">Add Offer</a><br>
                <?php } ?>
        <h2>Ofertele Disponibile</h2>
        <?php if ($result->num_rows > 0) { ?>
            <div class="offers-container">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="offer-item">
                        <img src="<?php echo htmlspecialchars($row['photo']); ?>" alt="Product Image">
                        <h3><a href="product.php?id=<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['name']); ?></a></h3>
                        <p>Preț: <?php echo htmlspecialchars($row['price']); ?> Lei</p>
                        <p><strong>Rating:</strong> <?php echo number_format($row['rating'], 2); ?> / 5</p>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role']=='admin') { ?>
                            <td>
                                <a href="edit_offer.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="button">Edit</a>
                                <a>&nbsp;&nbsp;</a>
                                <a href="delete_offer.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="button">Delete</a>
                            </td>
                        <?php } else { ?>
                        <form method="post" action="view_offers.php">
                            <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <button type="submit" name="add_to_cart">Adaugă în coș</button>
                        </form>
                        <?php } ?>
                        <br><br>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p>Nu există oferte disponibile în acest moment.</p>
        <?php } ?>
    </section>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Blank Electronics. All rights reserved.</p>
    </footer>
</main>
</body>
</html>