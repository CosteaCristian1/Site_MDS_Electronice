<?php

use PHPUnit\Framework\TestCase;

class ProductPageTest extends TestCase
{
    protected function setUp(): void
    {
        // Mock session and database connection
        $_SESSION = [];
        $this->conn = $this->createMock(mysqli::class);
    }

    public function test_session_start_and_cart_initialization()
    {
        session_start();
        include 'db_config.php';

        $this->assertArrayHasKey('cart', $_SESSION);
        $this->assertIsArray($_SESSION['cart']);
    }

    public function test_fetch_product_details()
    {
        $_GET['id'] = 1;

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($this->createMock(mysqli_result::class));

        $this->conn->method('prepare')->willReturn($stmt);

        include 'product.php';

        $this->assertNotEmpty($product);
        $this->assertArrayHasKey('name', $product);
        $this->assertArrayHasKey('price', $product);
    }

    public function test_invalid_rating_submission()
    {
        $_SESSION['username'] = 'testuser';
        $_SESSION['id'] = 1;
        $_POST['submit_rating'] = true;
        $_POST['rating'] = 6; // Invalid rating
        $_GET['id'] = 1;

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($this->createMock(mysqli_result::class));

        $this->conn->method('prepare')->willReturn($stmt);

        ob_start();
        include 'product.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Evaluarea trebuie să fie între 1 și 5.', $output);
    }
}
?>