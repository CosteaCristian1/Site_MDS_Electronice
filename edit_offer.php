<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Toate ofertele sunt fetch-uite pentru meniul dropdown pentru a le putea selecta individual
$offers = [];
$sql = "SELECT id, name FROM offers";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $offers[] = $row;
}

// Dacă o ofertă e selectată atunci se fetch-uiesc detaliile
$offer = null;
if (isset($_GET['id']) || isset($_POST['offer_id'])) {
    $id = isset($_GET['id']) ? $_GET['id'] : $_POST['offer_id'];
    $sql = "SELECT * FROM offers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $offer = $result->fetch_assoc();
    $stmt->close();
}

// Update offer dacă se dă submit
$update_success = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_offer'])) {
    $id = $_POST['offer_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $maker = $_POST['maker'];
    $rating = $_POST['rating'];
    $buyers = $_POST['buyers'];
    $photo = $_POST['photo']; 
//Statement update
    $sql = "UPDATE offers SET name = ?, description = ?, price = ?, category = ?, subcategory = ?, maker = ?, rating = ?, buyers = ?, photo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsdssisi", $name, $description, $price, $category, $subcategory, $maker, $rating, $buyers, $photo, $id);
//Execute
    if ($stmt->execute()) {
        $update_success = true;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
//Te duce înapoi pe pagina de view_offers după update
    if ($update_success) {
        echo "<script>alert('Offer updated successfully.'); window.location.href = 'view_offers.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Offer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-image: url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; margin: 0; padding: 0;">
    <main class="main-content">
        <h2>Edit Offer</h2>

        <!-- Form de selecție offer -->
        <form method="get" action="edit_offer.php">
            <label for="offer_id">Select Offer to Edit:</label>
            <select name="id" id="offer_id" onchange="this.form.submit()">
                <option value="">--Select an Offer--</option>
                <?php foreach ($offers as $offer_option): ?>
                    <option value="<?php echo $offer_option['id']; ?>" <?php echo isset($offer['id']) && $offer['id'] == $offer_option['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($offer_option['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Form update offer -->
        <?php if ($offer): ?>
            <form method="post" action="edit_offer.php">
                <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">

                <label for="name">Name:</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($offer['name']); ?>" required><br>

                <label for="description">Description:</label>
                <textarea name="description" id="description" required><?php echo htmlspecialchars($offer['description']); ?></textarea><br>

                <label for="price">Price:</label>
                <input type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars($offer['price']); ?>" required><br>

                <label for="category">Category:</label>
                <input type="text" name="category" id="category" value="<?php echo htmlspecialchars($offer['category']); ?>" required><br>

                <label for="subcategory">Subcategory:</label>
                <input type="text" name="subcategory" id="subcategory" value="<?php echo htmlspecialchars($offer['subcategory']); ?>" required><br>

                <label for="maker">Maker:</label>
                <input type="text" name="maker" id="maker" value="<?php echo htmlspecialchars($offer['maker']); ?>" required><br>

                <label for="rating">Rating:</label>
                <input type="number" step="0.1" name="rating" id="rating" value="<?php echo htmlspecialchars($offer['rating']); ?>" required><br>

                <label for="buyers">Number of Buyers:</label>
                <input type="number" name="buyers" id="buyers" value="<?php echo htmlspecialchars($offer['buyers']); ?>" required><br>

                <label for="photo">Photo (URL or File Path):</label>
                <input type="text" name="photo" id="photo" value="<?php echo htmlspecialchars($offer['photo']); ?>" required><br>
                <?php if (!empty($offer['photo'])): ?>
                    <img src="<?php echo htmlspecialchars($offer['photo']); ?>" alt="Offer Photo" style="max-width: 150px;"><br>
                <?php endif; ?>

                <input type="submit" name="update_offer" value="Update Offer">
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
