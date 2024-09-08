<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}
// Face rost de date din post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $maker = $_POST['maker'];
    $rating = $_POST['rating'];
    $buyers = $_POST['buyers'];
    $photo = $_POST['photo']; 
    // Pregatește statement-ul SQL 
    $sql = "INSERT INTO offers (name, description, price, category, subcategory, maker, rating, buyers, photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsssdss", $name, $description, $price, $category, $subcategory, $maker, $rating, $buyers, $photo);

    // Statement execute
    if ($stmt->execute()) {
        echo "<script>alert('Offer added successfully.'); window.location.href = 'view_offers.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Închide statement-ul
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Offer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-image: url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; margin: 0; padding: 0;">
    
    <form method="post" action="add_offer.php" class="main-content">
        <h2>Add Offer</h2>
        
        <label for="name">Name:</label>
        <input type="text" name="name" required><br>

        <label for="description">Description:</label>
        <textarea name="description" required></textarea><br>

        <label for="price">Price:</label>
        <input type="number" step="0.01" name="price" required><br>

        <label for="category">Category:</label>
        <input type="text" name="category" required><br>

        <label for="subcategory">Subcategory:</label>
        <input type="text" name="subcategory" required><br>

        <label for="maker">Maker:</label>
        <input type="text" name="maker" required><br>

        <label for="rating">Rating:</label>
        <input type="number" step="0.1" name="rating" ><br>

        <label for="buyers">Number of Buyers:</label>
        <input type="number" name="buyers" ><br>

        <label for="photo">Photo Path:</label>
        <input type="text" name="photo" required><br> 

        <input type="submit" value="Add Offer">
    </form>
</body>
</html>
