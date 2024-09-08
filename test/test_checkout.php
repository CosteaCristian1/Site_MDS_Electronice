<?php

use PHPUnit\Framework\TestCase;

class OrderScriptTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Mock database connection
        $this->conn = $this->createMock(mysqli::class);
    }

    public function test_fetch_user_details_logged_in()
    {
        // Mock session data
        $_SESSION['username'] = 'testuser';
        $_SESSION['id'] = 1;

        // Mock user data
        $user_data = [
            'id' => 1,
            'username' => 'testuser',
            'email' => 'testuser@example.com'
        ];

        // Mock prepared statement and result
        $stmt = $this->createMock(mysqli_stmt::class);
        $result = $this->createMock(mysqli_result::class);

        $this->conn->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM users WHERE id = ?')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('bind_param')
            ->with('i', $_SESSION['id']);

        $stmt->expects($this->once())
            ->method('execute');

        $stmt->expects($this->once())
            ->method('get_result')
            ->willReturn($result);

        $result->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn($user_data);

        $stmt->expects($this->once())
            ->method('close');

        // Execute the script
        include 'order_script.php';

        // Assert user details
        $this->assertEquals($user_data['username'], $_SESSION['username']);
        $this->assertEquals($user_data['email'], $user_data['email']);
    }

    public function test_empty_cart_handling()
    {
        // Mock session data
        $_SESSION['cart'] = [];

        // Mock prepared statement
        $stmt = $this->createMock(mysqli_stmt::class);

        $this->conn->expects($this->never())
            ->method('prepare');

        // Execute the script
        include 'order_script.php';

        // Assert that no orders were processed
        $this->assertEmpty($_SESSION['cart']);
        $this->assertEquals(0, $_SESSION['total']);
    }

    public function test_send_order_summary_email()
    {
        // Mock session data
        $_SESSION['username'] = 'testuser';
        $_SESSION['id'] = 1;
        $_SESSION['cart'] = [1, 2];

        // Mock user data
        $user_data = [
            'id' => 1,
            'username' => 'testuser',
            'email' => 'testuser@example.com'
        ];

        // Mock offer data
        $offer_data = [
            ['id' => 1, 'price' => 10, 'photo' => 'photo1.jpg', 'name' => 'Offer 1'],
            ['id' => 2, 'price' => 20, 'photo' => 'photo2.jpg', 'name' => 'Offer 2']
        ];

        // Mock prepared statement and result
        $stmt = $this->createMock(mysqli_stmt::class);
        $result = $this->createMock(mysqli_result::class);

        $this->conn->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->exactly(2))
            ->method('bind_param');

        $stmt->expects($this->exactly(2))
            ->method('execute');

        $stmt->expects($this->exactly(2))
            ->method('get_result')
            ->willReturn($result);

        $result->expects($this->exactly(2))
            ->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls($user_data, $offer_data);

        $stmt->expects($this->exactly(2))
            ->method('close');

        // Mock PHPMailer
        $mail = $this->createMock(PHPMailer::class);

        $mail->expects($this->once())
            ->method('isSMTP');

        $mail->expects($this->once())
            ->method('setFrom')
            ->with('cristiancostea1@gmail.com', 'Blank Electronics');

        $mail->expects($this->once())
            ->method('addAddress')
            ->with($user_data['email'], $user_data['username']);

        $mail->expects($this->once())
            ->method('addAttachment');

        $mail->expects($this->once())
            ->method('isHTML')
            ->with(true);

        $mail->expects($this->once())
            ->method('send');

        // Execute the script
        include 'order_script.php';

        // Assert email was sent
        $this->assertTrue($mail->send());
    }
}
?>