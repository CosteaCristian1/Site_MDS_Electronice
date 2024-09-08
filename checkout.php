<?php
session_start();
include 'db_config.php';

// Include PHPMailer si Dompdf
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';
require 'dompdf/autoload.inc.php';  // Updated line for Dompdf autoloading

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // User details în funcție de id dacă utilizatorul este logat.
    if (!empty($_SESSION['username'])) {
        $user_id = $_SESSION['id'];

        // Fetch la user_details
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
    } else { // User details din post daca utilizatorul este guest
        $user['email'] = $_POST['email'];
        $user['username'] = $_POST['username'];
    }

    // Inițializează totalul și cart_items
    $total = 0;
    $cart_items = [];

    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        // Fetch-ul pentru oferte folosind query din 'cart'
        $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
        $sql = "SELECT * FROM offers WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($_SESSION['cart'])), ...$_SESSION['cart']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Procesarea rezultatului și pregătirea cart_items
        while ($offer = $result->fetch_assoc()) {
            $offer_id = $offer['id'];
            $count = array_count_values($_SESSION['cart'])[$offer_id];
            $offer['count'] = $count;
            $total += $offer['price'] * $count;
            $cart_items[] = $offer;
        }
        $stmt->close();
    }

    // Salvează totalul din sesiune
    $_SESSION['total'] = $total;

    // Insert order into `orders` table
    if (!empty($_SESSION['username'])){
    $sql = "INSERT INTO orders (userid) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();
    }
    else{
        $sql = "INSERT INTO orders (userid) VALUES (0)";
        $stmt = $conn->prepare($sql);
    }
    
    // Inserează order-ul în order_items
    $sql = "INSERT INTO order_items (orderid, offerid, cost) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    foreach ($cart_items as $item) {
        $offer_id = $item['id'];
        $cost = $item['price'] * $item['count'];
        $stmt->bind_param("iid", $order_id, $offer_id, $cost);
        $stmt->execute();
    }
    $stmt->close();

    // Updateaza numărul de cumpărători pentru item
    $sql = "UPDATE offers SET buyers = buyers + ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    foreach ($cart_items as $item) {
        $offer_id = $item['id'];
        $count = $item['count'];  // Număr de cumpărături pentru fiecare item
        $stmt->bind_param("ii", $count, $offer_id);
        $stmt->execute();
    }
    $stmt->close();

    // Generează bon/tichet/factură pdf cu detaliile cumpărăturilor
    $dompdf = new Dompdf();
    $html = "<h1>Order Summary</h1>
             <p><strong>Customer Name:</strong> {$user['username']}</p>
             <p><strong>Email:</strong> {$user['email']}</p>
             <p><strong>Total Amount:</strong> {$total} Lei</p>
             <h2>Cart Items:</h2>
             <table border='1' cellpadding='5' cellspacing='0'>
             <thead>
                 <tr>
                     <th>Photo</th>
                     <th>Name</th>
                     <th>Price</th>
                     <th>Quantity</th>
                 </tr>
             </thead>
             <tbody>";

    foreach ($cart_items as $item) {
        $html .= "<tr>
                      <td><img src='{$item['photo']}' alt='Product Image' width='100'></td>
                      <td>{$item['name']}</td>
                      <td>{$item['price']} Lei</td>
                      <td>{$item['count']}</td>
                  </tr>";
    }

    $html .= "</tbody></table>";

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdf_output = $dompdf->output();
    $pdf_file = 'order_summary_' . time() . '.pdf';
    file_put_contents($pdf_file, $pdf_output);

     // Trimite confirmarea e-mail cu attachment pdf
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();                         
        $mail->Host = 'smtp.gmail.com';         
        $mail->SMTPAuth = true;                   
        $mail->Username = 'cristiancostea1@gmail.com'; 
        $mail->Password = 'aguiugbyodskoyau';  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587;                       

        $mail->setFrom('cristiancostea1@gmail.com', 'Blank Electronics');
        $mail->addAddress($user['email'], $user['username']);
        $mail->addAttachment($pdf_file);

        $mail->isHTML(true);
        $mail->Subject = "Hello {$user['username']}, Your Order Summary";
        $mail->Body    = 'Thank you for your purchase. Please find your order summary attached.';

        $mail->send();
        echo "<script>alert('Order placed successfully. Please check your email for the summary.'); window.location.href = 'index.php';</script>";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    // Clean up
    unlink($pdf_file);

   // Unset la date după finalizarea procesului
    $_SESSION['cart'] = [];
    $_SESSION['total'] = 0;
}
?>
