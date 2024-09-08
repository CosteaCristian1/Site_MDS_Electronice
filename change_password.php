<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['reset_password'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredPassword = $_POST['entered_password'];
    $newPassword = $_POST['new_password'];
    
    // Validare parolă
    if ($enteredPassword === $_SESSION['reset_password']) {
        
        
        // Hash parolă nouă
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update la parolă în baza de date
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $hashedNewPassword, $_SESSION['username']);
        
        if ($stmt->execute()) {
            echo "Password changed successfully.";
            unset($_SESSION['reset_password']); // Clear din sesiune la variabilă
            header('Location: account_info.php');
            exit();
        } else {
            echo "Error updating password.";
        }
    } else {
        echo "The password you entered is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-image: url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; margin: 0; padding: 0;">
<main>
    <div class="main-content">
        <h1>An email has been sent with a password that you have to enter.</h1><br>
        <h2>Change password</h2>
        <form action="change_password.php" method="post">
            <label for="entered_password">Enter the password in your email:</label>
            <input type="password" id="entered_password" name="entered_password" required>
            
            <label for="new_password">New account password:</label>
            <input type="password" id="new_password" name="new_password" required><br><br>
            
            <input type="submit" value="Change Password">
        </form>
    </div>
</main>
</body>
</html>
