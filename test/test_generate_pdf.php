<?php
use PHPUnit\Framework\TestCase;
use Dompdf\Dompdf;

class OfferPdfTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = $this->createMock(mysqli::class);
    }

    public function test_generate_pdf_with_valid_id()
    {
        $_GET['id'] = 1;

        $stmt = $this->createMock(mysqli_stmt::class);
        $result = $this->createMock(mysqli_result::class);

        $offer = [
            'title' => 'Special Offer',
            'description' => 'This is a special offer.',
            'price' => 99.99,
            'available_from' => '2023-01-01',
            'available_to' => '2023-12-31'
        ];

        $this->conn->expects($this->once())
            ->method('prepare')
            ->with($this->equalTo('SELECT * FROM offers WHERE id = ?'))
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('bind_param')
            ->with($this->equalTo('i'), $this->equalTo($_GET['id']));

        $stmt->expects($this->once())
            ->method('execute');

        $stmt->expects($this->once())
            ->method('get_result')
            ->willReturn($result);

        $result->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn($offer);

        $stmt->expects($this->once())
            ->method('close');

        ob_start();
        include 'path/to/your/script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('offer.pdf', $output);
    }

    public function test_handle_missing_or_invalid_id()
    {
        unset($_GET['id']);

        ob_start();
        include 'path/to/your/script.php';
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    public function test_handle_database_errors()
    {
        $_GET['id'] = 1;

        $this->conn->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new Exception('Database error')));

        ob_start();
        include 'path/to/your/script.php';
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }
}
?>