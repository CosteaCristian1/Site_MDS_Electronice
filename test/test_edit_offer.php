<?php

use PHPUnit\Framework\TestCase;

class EditOfferTest extends TestCase
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
        $this->expectOutputRegex('/Location: index\.php/');
        include 'edit_offer.php';
    }

    public function test_fetch_display_offers()
    {
        $_SESSION['role'] = 'admin'; // Admin role
        $result = $this->createMock(mysqli_result::class);
        $result->method('fetch_assoc')->willReturnOnConsecutiveCalls(
            ['id' => 1, 'name' => 'Offer 1'],
            ['id' => 2, 'name' => 'Offer 2'],
            null
        );

        $this->conn->method('query')->willReturn($result);
        $GLOBALS['conn'] = $this->conn;

        ob_start();
        include 'edit_offer.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('<option value="1">Offer 1</option>', $output);
        $this->assertStringContainsString('<option value="2">Offer 2</option>', $output);
    }

    public function test_update_offer_details()
    {
        $_SESSION['role'] = 'admin'; // Admin role
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'update_offer' => true,
            'offer_id' => 1,
            'name' => 'Updated Offer',
            'description' => 'Updated Description',
            'price' => 99.99,
            'category' => 'Updated Category',
            'subcategory' => 'Updated Subcategory',
            'maker' => 'Updated Maker',
            'rating' => 4.5,
            'buyers' => 100,
            'photo' => 'updated_photo.jpg'
        ];

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('close')->willReturn(true);

        $this->conn->method('prepare')->willReturn($stmt);
        $GLOBALS['conn'] = $this->conn;

        ob_start();
        include 'edit_offer.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Offer updated successfully.', $output);
    }
}
?>