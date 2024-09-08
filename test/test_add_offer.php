<?php

use PHPUnit\Framework\TestCase;

class AddOfferTest extends TestCase
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
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        include 'add_offer.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Location: index.php', xdebug_get_headers());
    }

    public function test_successful_offer_insertion()
    {
        $_SESSION['role'] = 'admin';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => 'Test Offer',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'Test Category',
            'subcategory' => 'Test Subcategory',
            'maker' => 'Test Maker',
            'rating' => 4.5,
            'buyers' => 100,
            'photo' => 'test_photo.jpg'
        ];

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->expects($this->once())->method('bind_param')->with(
            $this->equalTo('ssdsssdss'),
            $this->equalTo('Test Offer'),
            $this->equalTo('Test Description'),
            $this->equalTo(99.99),
            $this->equalTo('Test Category'),
            $this->equalTo('Test Subcategory'),
            $this->equalTo('Test Maker'),
            $this->equalTo(4.5),
            $this->equalTo(100),
            $this->equalTo('test_photo.jpg')
        );
        $stmt->expects($this->once())->method('execute')->willReturn(true);
        $stmt->expects($this->once())->method('close');

        $this->conn->expects($this->once())->method('prepare')->willReturn($stmt);

        ob_start();
        include 'add_offer.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Offer added successfully.', $output);
    }

    public function test_sql_insertion_error_handling()
    {
        $_SESSION['role'] = 'admin';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => 'Test Offer',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'Test Category',
            'subcategory' => 'Test Subcategory',
            'maker' => 'Test Maker',
            'rating' => 4.5,
            'buyers' => 100,
            'photo' => 'test_photo.jpg'
        ];

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->expects($this->once())->method('bind_param')->with(
            $this->equalTo('ssdsssdss'),
            $this->equalTo('Test Offer'),
            $this->equalTo('Test Description'),
            $this->equalTo(99.99),
            $this->equalTo('Test Category'),
            $this->equalTo('Test Subcategory'),
            $this->equalTo('Test Maker'),
            $this->equalTo(4.5),
            $this->equalTo(100),
            $this->equalTo('test_photo.jpg')
        );
        $stmt->expects($this->once())->method('execute')->willReturn(false);
        $stmt->expects($this->once())->method('close');
        $stmt->error = 'SQL Error';

        $this->conn->expects($this->once())->method('prepare')->willReturn($stmt);

        ob_start();
        include 'add_offer.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Error: SQL Error', $output);
    }
}
?>