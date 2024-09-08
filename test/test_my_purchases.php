<?php

use PHPUnit\Framework\TestCase;

class OrdersPageTest extends TestCase
{
    protected function setUp(): void
    {
        // Mock session and database connection
        $_SESSION = [];
        $this->conn = $this->createMock(mysqli::class);
    }

    public function test_redirect_if_not_logged_in()
    {
        $_SESSION = [];
        ob_start();
        include 'orders_page.php';
        $output = ob_get_clean();
        $this->assertStringContainsString('Location: login.php', xdebug_get_headers());
    }

    public function test_fetch_and_display_orders()
    {
        $_SESSION['username'] = 'testuser';
        $_SESSION['id'] = 1;

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->method('get_result')->willReturn($this->createMock(mysqli_result::class));

        $this->conn->method('prepare')->willReturn($stmt);

        ob_start();
        include 'orders_page.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('<h1>My Purchases</h1>', $output);
        $this->assertStringContainsString('<section class="order-summary">', $output);
    }

    public function test_no_orders_message()
    {
        $_SESSION['username'] = 'testuser';
        $_SESSION['id'] = 1;

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->method('get_result')->willReturn($this->createMock(mysqli_result::class));

        $this->conn->method('prepare')->willReturn($stmt);

        ob_start();
        include 'orders_page.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('You have not made any purchases yet.', $output);
    }
}
?>