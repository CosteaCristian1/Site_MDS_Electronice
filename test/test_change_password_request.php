<?php
use PHPUnit\Framework\TestCase;

class GenerateRandomPasswordTest extends TestCase {
    
    public function test_generate_random_password_default_length() {
        $password = generateRandomPassword();
        $this->assertEquals(6, strlen($password));
        $this->assertMatchesRegularExpression('/^[A-Z]{6}$/', $password);
    }

    public function test_generate_random_password_specified_length() {
        $length = 10;
        $password = generateRandomPassword($length);
        $this->assertEquals($length, strlen($password));
        $this->assertMatchesRegularExpression('/^[A-Z]{10}$/', $password);
    }

    public function test_generate_random_password_edge_cases() {
        $password = generateRandomPassword(0);
        $this->assertEquals('', $password);

        $password = generateRandomPassword(-5);
        $this->assertEquals('', $password);
    }
}
?>