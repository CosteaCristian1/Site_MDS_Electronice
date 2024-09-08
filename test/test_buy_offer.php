<?php

use PHPUnit\Framework\TestCase;

class OrderScriptTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_SERVER = [];
    }

    public function test_redirect_if_role_not_set()
    {
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        include 'order_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Location: login.php', xdebug_get_headers());
    }

    public function test_pdf_generation()
    {
        $_SESSION['role'] = 'user';
        $_SESSION['id'] = 1;
        $_SESSION['cart'] = [1, 2];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Mock database connection and queries
        $conn = $this->createMock(mysqli::class);
        $stmt = $this->createMock(mysqli_stmt::class);
        $result = $this->createMock(mysqli_result::class);

        $conn->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);
        $result->method('fetch_assoc')->willReturnOnConsecutiveCalls(
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 1, 'name' => 'Offer 1', 'price' => 100, 'photo' => 'photo1.jpg'],
            ['id' => 2, 'name' => 'Offer 2', 'price' => 200, 'photo' => 'photo2.jpg']
        );

        ob_start();
        include 'order_script.php';
        ob_end_clean();

        $this->assertFileExists('order_summary_' . time() . '.pdf');
    }

    public function test_email_sending_with_attachment()
    {
        $_SESSION['role'] = 'user';
        $_SESSION['id'] = 1;
        $_SESSION['cart'] = [1, 2];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Mock database connection and queries
        $conn = $this->createMock(mysqli::class);
        $stmt = $this->createMock(mysqli_stmt::class);
        $result = $this->createMock(mysqli_result::class);

        $conn->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);
        $result->method('fetch_assoc')->willReturnOnConsecutiveCalls(
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 1, 'name' => 'Offer 1', 'price' => 100, 'photo' => 'photo1.jpg'],
            ['id' => 2, 'name' => 'Offer 2', 'price' => 200, 'photo' => 'photo2.jpg']
        );

        ob_start();
        include 'order_script.php';
        ob_end_clean();

        // Check if email was sent (this is a simplified check, in real scenarios you might need to mock PHPMailer)
        $this->assertStringContainsString('Order placed successfully', ob_get_contents());
    }
}
?>