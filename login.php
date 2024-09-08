<?php
session_start();
include 'db_config.php';

//Dacă sunt luate date din post se realizează login-ul
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
//Query și fetch în variabilele id, username, hashed_password, role 
    $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $hashed_password, $role);
    $stmt->fetch();
//Verifică dacă parola din form este aceeași cu parola din baza de date
    if ($stmt->num_rows > 0) {
        if (password_verify($password, $hashed_password)) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['id']= $id;
            header("Location: index.php");
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that username.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-image: url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; margin: 0; padding: 0;">
    <div class="main-content">
    <form method="post" action="login.php" >
    <h2>Login</h2>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>
    <br><br>
    <h3>Don't have an account?</h3>
    <br>
    <a href="register.php">REGISTER NOW</a>
    </div>
</body>
</html>
