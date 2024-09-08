<?php

use PHPUnit\Framework\TestCase;

class OfferDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        // Mock session and database connection
        $_SESSION = [];
        $this->conn = $this->createMock(mysqli::class);
    }

    public function test_redirect_non_admin()
    {
        $_SESSION['role'] = 'user'; // Non-admin role

        // Start output buffering to capture header redirection
        ob_start();
        include 'path_to_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Location: index.php', xdebug_get_headers());
    }

    public function test_delete_offer_valid_id()
    {
        $_SESSION['role'] = 'admin'; // Admin role
        $_GET['id'] = 1; // Valid ID

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->expects($this->once())
             ->method('bind_param')
             ->with($this->equalTo('i'), $this->equalTo(1));
        $stmt->expects($this->once())
             ->method('execute')
             ->willReturn(true);
        $stmt->expects($this->once())
             ->method('close');

        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with($this->equalTo('DELETE FROM offers WHERE id = ?'))
                   ->willReturn($stmt);

        // Start output buffering to capture output
        ob_start();
        include 'path_to_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Offer deleted successfully.', $output);
        $this->assertStringContainsString('Location: view_offers.php', xdebug_get_headers());
    }

    public function test_handle_database_error()
    {
        $_SESSION['role'] = 'admin'; // Admin role
        $_GET['id'] = 1; // Valid ID

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->expects($this->once())
             ->method('bind_param')
             ->with($this->equalTo('i'), $this->equalTo(1));
        $stmt->expects($this->once())
             ->method('execute')
             ->willReturn(false);
        $stmt->expects($this->once())
             ->method('close');
        $stmt->error = 'Mocked error message';

        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with($this->equalTo('DELETE FROM offers WHERE id = ?'))
                   ->willReturn($stmt);

        // Start output buffering to capture output
        ob_start();
        include 'path_to_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Error: Mocked error message', $output);
    }
}
?>