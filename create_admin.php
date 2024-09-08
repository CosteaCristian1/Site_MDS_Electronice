<?php
include 'db_config.php';

// Detalii cont admin
$username = 'admin';
$password = 'adminpa55';
$email = 'admin@gmail.com';
$role = 'admin';

// Hash parolă
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare insert
$sql = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssss", $username, $hashed_password, $email, $role);

    // Executare statement
    if ($stmt->execute()) {
        echo "Admin account created successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
} else {
    echo "Error: " . $conn->error;
}

// Close connection
$conn->close();
?>
