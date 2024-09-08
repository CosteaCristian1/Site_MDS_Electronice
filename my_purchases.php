<?php
session_start();
include 'db_config.php';

// User login check
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['id'];

// Fetch pentru toate order-urile în funcție de user
$sql = "
    SELECT o.orderid, o.userid, SUM(oi.cost) AS total_cost
    FROM orders o
    INNER JOIN order_items oi ON o.orderid = oi.orderid
    WHERE o.userid = ?
    GROUP BY o.orderid
    ORDER BY o.orderid DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
$stmt->close();

// Fetch detalii a fiecărui item
$orders = [];
while ($order = $orders_result->fetch_assoc()) {
    $order_id = $order['orderid'];

    // Fetch items pentru fiecare order
    $sql = "
        SELECT oi.orderitemid, oi.offerid, o.name, o.photo, o.price, oi.cost
        FROM order_items oi
        INNER JOIN offers o ON oi.offerid = o.id
        WHERE oi.orderid = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();

    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }

    $orders[] = [
        'order' => $order,
        'items' => $items
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchases</title>
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
<div class="header">
<div class="nav-left">
    <nav>
        <ul>
            <!-- Dropdown Menu pentru oferte -->
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
        <nav>
            <ul>
                <?php if (isset($_SESSION['username'])): ?>
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Contul Meu</a>
                    <div class="dropdown-content">
                        <a href="account_info.php">Info Cont</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
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
<main>

    <h1>My Purchases</h1>
    <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $order_data): ?>
            <section class="order-summary">
                <h2>Order ID: <?php echo htmlspecialchars($order_data['order']['orderid']); ?></h2>
                <p><strong>Total Cost:</strong> <?php echo htmlspecialchars($order_data['order']['total_cost']); ?> Lei</p>

                <h3>Items in this Order:</h3>
                <table border="1" cellpadding="5" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_data['items'] as $item): ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($item['photo']); ?>" alt="Product Image" width="100"></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['price']); ?> Lei</td>
                                <td><?php echo htmlspecialchars($item['cost']); ?> Lei</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You have not made any purchases yet.</p>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Blank Electronics. All rights reserved.</p>
</footer>
</div>
</body>
</html>
