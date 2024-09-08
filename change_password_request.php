<?php
session_start();
include 'db_config.php';

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$id = $_SESSION['id'];

// Generează parolă random
function generateRandomPassword($length = 6) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
}

$newPassword = generateRandomPassword();

// Face rost de emailul user-ului
$sql = "SELECT email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $email = $user['email'];
} else {
    echo "Error: User not found.";
    exit();
}

// Trimitere parolă randomizată pe email
$mail = new PHPMailer(true);
try {

    $mail->SMTPDebug = 0;                     
    $mail->isSMTP();                          
    $mail->Host       = 'smtp.gmail.com';      
    $mail->SMTPAuth   = true;                  
    $mail->Username   = 'cristiancostea1@gmail.com';
    $mail->Password   = 'aguiugbyodskoyau '; 
    $mail->SMTPSecure = 'tls';                
    $mail->Port       = 587;                
    
    $mail->setFrom('cristiancostea1@gmail.com', 'Mailer');
    $mail->addAddress($email, 'Recipient Name');

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body    = "Your temporary password is: <b>$newPassword</b><br>Please use this password to reset your password on our website.";
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $mail->send();

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    exit();
}

// Salvează parolă random în sesiune
$_SESSION['reset_password'] = $newPassword;

header('Location: change_password.php');
exit();
?>
