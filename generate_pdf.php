<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;

if (isset($_GET['id'])) {
    $offer_id = $_GET['id'];

    include 'db_config.php';

    $sql = "SELECT * FROM offers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $offer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $offer = $result->fetch_assoc();
    $stmt->close();

    $html = "<h1>" . $offer['title'] . "</h1>";
    $html .= "<p>" . $offer['description'] . "</p>";
    $html .= "<p>Price: $" . $offer['price'] . "</p>";
    $html .= "<p>Available from: " . $offer['available_from'] . " to " . $offer['available_to'] . "</p>";

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("offer.pdf");
}
?>
