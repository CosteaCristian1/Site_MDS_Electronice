<?php
use PHPUnit\Framework\TestCase;

class DatabaseConnectionTest extends TestCase
{
    public function test_successful_connection()
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "blank_electronics";

        $conn = new mysqli($servername, $username, $password, $dbname);

        $this->assertFalse($conn->connect_error, "Connection should be established successfully.");
    }

    public function test_connection_error_handling()
    {
        $servername = "invalid_host";
        $username = "root";
        $password = "";
        $dbname = "blank_electronics";

        $conn = @new mysqli($servername, $username, $password, $dbname);

        $this->assertTrue($conn->connect_error, "Connection error should be handled gracefully.");
    }

    public function test_incorrect_credentials()
    {
        $servername = "localhost";
        $username = "wrong_user";
        $password = "wrong_password";
        $dbname = "blank_electronics";

        $conn = @new mysqli($servername, $username, $password, $dbname);

        $this->assertTrue($conn->connect_error, "Connection should fail with incorrect credentials.");
    }
}
?>