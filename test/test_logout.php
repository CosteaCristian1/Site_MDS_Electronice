<?php

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function test_session_start()
    {
        $this->assertTrue(session_start());
    }

    public function test_session_unset()
    {
        session_start();
        $_SESSION['test'] = 'value';
        session_unset();
        $this->assertEmpty($_SESSION);
    }

    public function test_header_redirect()
    {
        $this->expectOutputString('');
        header("Location: index.php");
        $headers = xdebug_get_headers();
        $this->assertContains('Location: index.php', $headers);
    }
}
?>