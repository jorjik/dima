<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 2) . '/app/Helpers/sanitize.php';
    }

    // --- sanitizeHtml ---

    public function test_removes_script_tags(): void
    {
        $input = 'Hello <script>alert("xss")</script> World';
        $this->assertSame('Hello  World', sanitizeHtml($input));
    }

    public function test_removes_script_tags_with_multiline_content(): void
    {
        $input = "<div>text</div>\n<script>\n  alert('xss');\n</script>\n<p>end</p>";
        $expected = "<div>text</div>\n\n<p>end</p>";
        $this->assertSame($expected, sanitizeHtml($input));
    }

    public function test_removes_case_insensitive_script(): void
    {
        $input = '<SCRIPT>alert(1)</SCRIPT>';
        $this->assertSame('', sanitizeHtml($input));
    }

    public function test_removes_iframe_tags(): void
    {
        $input = '<iframe src="https://evil.com"></iframe>';
        $this->assertSame('', sanitizeHtml($input));
    }

    public function test_removes_object_tags(): void
    {
        $input = '<object data="evil.swf"></object>';
        $this->assertSame('', sanitizeHtml($input));
    }

    public function test_removes_embed_tags(): void
    {
        $input = '<embed src="evil.swf">';
        $this->assertSame('', sanitizeHtml($input));
    }

    public function test_removes_form_tags(): void
    {
        $input = '<form action="https://phish.com"><input type="submit"></form>';
        $this->assertSame('<input type="submit">', sanitizeHtml($input));
    }

    public function test_removes_meta_tags(): void
    {
        $input = '<meta http-equiv="refresh" content="0;url=https://evil.com">';
        $this->assertSame('', sanitizeHtml($input));
    }

    public function test_removes_link_tags(): void
    {
        $input = '<link rel="stylesheet" href="style.css">';
        $this->assertSame('', sanitizeHtml($input));
    }

    public function test_removes_svg_tags(): void
    {
        $input = '<svg onload="alert(1)"></svg>';
        $this->assertSame('', sanitizeHtml($input));
    }

    public function test_removes_event_handlers_with_double_quotes(): void
    {
        $input = '<img src="x" onerror="alert(1)">';
        $this->assertSame('<img src="x">', sanitizeHtml($input));
    }

    public function test_removes_event_handlers_with_single_quotes(): void
    {
        $input = "<img src='x' onerror='alert(1)'>";
        $this->assertSame("<img src='x'>", sanitizeHtml($input));
    }

    public function test_removes_event_handlers_without_quotes(): void
    {
        $input = '<img src=x onerror=alert(1)>';
        $this->assertSame('<img src=x>', sanitizeHtml($input));
    }

    public function test_removes_on_event_handler_any_word(): void
    {
        $input = '<button onclick="xss()" onmouseover="xss()">Click</button>';
        $this->assertSame('<button>Click</button>', sanitizeHtml($input));
    }

    public function test_removes_javascript_protocol(): void
    {
        $input = '<a href="javascript:alert(1)">click</a>';
        $this->assertSame('<a href="alert(1)">click</a>', sanitizeHtml($input));
    }

    public function test_removes_javascript_protocol_case_insensitive(): void
    {
        $input = '<a href="JavaScript:alert(1)">click</a>';
        $this->assertSame('<a href="alert(1)">click</a>', sanitizeHtml($input));
    }

    public function test_removes_vbscript_protocol(): void
    {
        $input = '<a href="vbscript:msgbox(1)">click</a>';
        $this->assertSame('<a href="msgbox(1)">click</a>', sanitizeHtml($input));
    }

    public function test_removes_javascript_colon_spaces(): void
    {
        $input = '<a href="javascript :alert(1)">click</a>';
        $this->assertSame('<a href="alert(1)">click</a>', sanitizeHtml($input));
    }

    public function test_preserves_legitimate_html(): void
    {
        $input = '<p>Hello <strong>world</strong>!</p>';
        $this->assertSame($input, sanitizeHtml($input));
    }

    public function test_preserves_links_and_images(): void
    {
        $input = '<a href="https://example.com">Link</a> <img src="photo.jpg" alt="Photo">';
        $this->assertSame($input, sanitizeHtml($input));
    }

    public function test_handles_empty_string(): void
    {
        $this->assertSame('', sanitizeHtml(''));
    }

    public function test_handles_text_with_no_html(): void
    {
        $this->assertSame('Hello, world!', sanitizeHtml('Hello, world!'));
    }

    // --- addLazyLoading ---

    public function test_adds_lazy_loading_to_img(): void
    {
        $input = '<img src="photo.jpg" alt="Photo">';
        $result = addLazyLoading($input);
        $this->assertStringContainsString('loading="lazy"', $result);
        $this->assertStringContainsString('src="photo.jpg"', $result);
    }

    public function test_does_not_override_existing_loading(): void
    {
        $input = '<img src="photo.jpg" loading="eager" alt="Hero">';
        $result = addLazyLoading($input);
        $this->assertStringNotContainsString('loading="lazy" loading="eager"', $result);
        $this->assertStringContainsString('loading="eager"', $result);
    }

    public function test_does_not_override_existing_loading_case_insensitive(): void
    {
        $input = '<img src="photo.jpg" LOADING="eager" alt="Hero">';
        $result = addLazyLoading($input);
        $this->assertStringContainsString('LOADING="eager"', $result);
    }

    public function test_adds_lazy_to_multiple_images(): void
    {
        $input = '<img src="a.jpg"><img src="b.jpg">';
        $result = addLazyLoading($input);
        $this->assertSame(2, substr_count($result, 'loading="lazy"'));
    }

    public function test_adds_lazy_to_img_with_attributes(): void
    {
        $input = '<img src="photo.jpg" width="800" height="600" alt="Photo" class="my-img">';
        $result = addLazyLoading($input);
        $this->assertStringContainsString('loading="lazy"', $result);
        $this->assertStringContainsString('width="800"', $result);
        $this->assertStringContainsString('class="my-img"', $result);
    }

    public function test_add_lazy_does_not_affect_non_img_tags(): void
    {
        $input = '<div><p>Hello</p></div>';
        $this->assertSame($input, addLazyLoading($input));
    }
}
