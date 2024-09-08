<?php

use PHPUnit\Framework\TestCase;

class UserAccountTest extends TestCase
{
    public function test_redirect_if_not_authenticated()
    {
        $_SESSION = [];
        $_SERVER['REQUEST_URI'] = '/my_account.php';

        ob_start();
        include 'my_account.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Location: login.php', xdebug_get_headers());
    }

    public function test_fetch_user_data()
    {
        $_SESSION['username'] = 'testuser';
        $_SESSION['id'] = 1;

        $mockConn = $this->createMock(mysqli::class);
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockResult = $this->createMock(mysqli_result::class);

        $mockConn->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('get_result')->willReturn($mockResult);
        $mockResult->method('num_rows')->willReturn(1);
        $mockResult->method('fetch_assoc')->willReturn(['username' => 'testuser', 'email' => 'testuser@example.com']);

        $GLOBALS['conn'] = $mockConn;

        ob_start();
        include 'my_account.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('testuser@example.com', $output);
    }

    public function test_user_not_found()
    {
        $_SESSION['username'] = 'testuser';
        $_SESSION['id'] = 1;

        $mockConn = $this->createMock(mysqli::class);
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockResult = $this->createMock(mysqli_result::class);

        $mockConn->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('get_result')->willReturn($mockResult);
        $mockResult->method('num_rows')->willReturn(0);

        $GLOBALS['conn'] = $mockConn;

        ob_start();
        include 'my_account.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('No email found', $output);
    }
}
?>