<?php
use PHPUnit\Framework\TestCase;

class AdminAccountTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Mock the database connection
        $this->conn = $this->createMock(mysqli::class);
    }

    public function test_password_hash_success()
    {
        $password = 'adminpa55';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $this->assertTrue(password_verify($password, $hashed_password));
    }

    public function test_sql_prepare_and_bind()
    {
        $username = 'admin';
        $password = 'adminpa55';
        $email = 'admin@gmail.com';
        $role = 'admin';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->expects($this->once())
             ->method('bind_param')
             ->with("ssss", $username, $hashed_password, $email, $role)
             ->willReturn(true);

        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)")
                   ->willReturn($stmt);

        $stmt->expects($this->once())
             ->method('execute')
             ->willReturn(true);

        $stmt->expects($this->once())
             ->method('close');

        $this->conn->expects($this->once())
                   ->method('close');

        // Execute the code to be tested
        include 'path_to_your_php_file.php';
    }

    public function test_sql_execution_error_handling()
    {
        $username = 'admin';
        $password = 'adminpa55';
        $email = 'admin@gmail.com';
        $role = 'admin';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->expects($this->once())
             ->method('bind_param')
             ->with("ssss", $username, $hashed_password, $email, $role)
             ->willReturn(true);

        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)")
                   ->willReturn($stmt);

        $stmt->expects($this->once())
             ->method('execute')
             ->willReturn(false);

        $stmt->expects($this->once())
             ->method('error')
             ->willReturn('Execution error');

        $stmt->expects($this->once())
             ->method('close');

        $this->conn->expects($this->once())
                   ->method('close');

        // Capture the output
        $this->expectOutputString('Error: Execution error');

        // Execute the code to be tested
        include 'path_to_your_php_file.php';
    }
}
?>