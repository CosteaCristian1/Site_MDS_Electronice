<?php

use PHPUnit\Framework\TestCase;

class ChangePasswordTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function test_redirect_if_reset_password_not_set()
    {
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        include 'change_password.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Location: login.php', xdebug_get_headers());
    }

    public function test_password_hash_and_update()
    {
        $_SESSION['reset_password'] = 'correct_password';
        $_SESSION['username'] = 'test_user';
        $_POST['entered_password'] = 'correct_password';
        $_POST['new_password'] = 'new_secure_password';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $mockConn = $this->createMock(mysqli::class);
        $mockStmt = $this->createMock(mysqli_stmt::class);

        $mockConn->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);

        $GLOBALS['conn'] = $mockConn;

        ob_start();
        include 'change_password.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Password changed successfully.', $output);
    }

    public function test_incorrect_entered_password()
    {
        $_SESSION['reset_password'] = 'correct_password';
        $_POST['entered_password'] = 'wrong_password';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        include 'change_password.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('The password you entered is incorrect.', $output);
    }
}
?>