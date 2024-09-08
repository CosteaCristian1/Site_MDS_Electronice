<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $role = 'client';

    $sql = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $password, $email, $role);

    

    if ($stmt->execute()) {
        $sql = "SELECT id, username, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $role);
    $stmt->fetch();
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
    $_SESSION['id']= $id;
    echo "Registration successful." ;
    header("Location: index.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-image: url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; margin: 0; padding: 0;">
    
    <form method="post" action="register.php" class="main-content">
    <h2>Register</h2>
        Username: <input type="text" name="username" required><br>
        Parola: <input type="password" name="password" required><br>
        Email: <input type="email" name="email" required><br><br>
        <input type="submit" value="Register">
    </form>
</body>
</html>
