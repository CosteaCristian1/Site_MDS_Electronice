<?php
session_start();
include 'db_config.php';

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $id = $_SESSION['id'];
    $message = $_POST['message'];
    
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
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
    $mail->addAddress('torchleone5914@gmail.com', 'Recipient Name'); 

  
    $mail->isHTML(true);                      
    $mail->Subject = 'Contact Form';
    $mail->Body    = "<h2>Contact Form Submission</h2>
    <p><b>Name:</b> {$name}</p>
    <p><b>Email:</b> {$user['email']}</p>
    <p><b>Message:</b><br>{$message}</p>";
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
}
?>
