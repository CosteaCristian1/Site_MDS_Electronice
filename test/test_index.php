<?php

use PHPUnit\Framework\TestCase;

class WebPageTest extends TestCase
{
    public function test_session_start_initialization()
    {
        session_start();
        $_SESSION['username'] = 'testuser';
        $this->assertEquals('testuser', $_SESSION['username']);
    }

    public function test_query_top_offers_display()
    {
        $conn = $this->createMock(mysqli::class);
        $result = $this->createMock(mysqli_result::class);

        $conn->method('query')->willReturn($result);
        $result->method('num_rows')->willReturn(3);
        $result->method('fetch_assoc')->willReturnOnConsecutiveCalls(
            ['photo' => 'photo1.jpg', 'id' => 1, 'name' => 'Product 1', 'price' => 100],
            ['photo' => 'photo2.jpg', 'id' => 2, 'name' => 'Product 2', 'price' => 200],
            ['photo' => 'photo3.jpg', 'id' => 3, 'name' => 'Product 3', 'price' => 300]
        );

        ob_start();
        include 'path_to_your_php_file.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Product 1', $output);
        $this->assertStringContainsString('Product 2', $output);
        $this->assertStringContainsString('Product 3', $output);
    }

    public function test_htmlspecialchars_prevents_xss()
    {
        $unsafe_id = '<script>alert("XSS")</script>';
        $safe_id = htmlspecialchars($unsafe_id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8', true);
        $this->assertNotEquals($unsafe_id, $safe_id);
        $this->assertEquals('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', $safe_id);
    }
}
?>