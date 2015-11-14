<?php

class AMP_Sanitizer_Test extends WP_UnitTestCase {
	function test_strip_empty() {
		$source = '';
		$expected = '';
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}

	function test_strip_blacklisted_tags_with_innertext() {
		$source = '<script>alert("")</script>';
		$expected = '';
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}

	function test_strip_blacklisted_tags_only() {
		$source = '<input type="text" /><script>alert("")</script><style>body{ color: red; }</style>';
		$expected = '';
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}

	function test_strip_whitelisted_tags_only() {
		$source = '<p>Text</p><img src="/path/to/file.jpg" />';
		$expected = '<p>Text</p><img src="/path/to/file.jpg"></img>'; // LIBXML_NOEMPTYTAG
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}

	function test_strip_mixed_tags() {
		$source = '<input type="text" /><p>Text</p><script>alert("")</script><style>body{ color: red; }</style>';
		$expected = '<p>Text</p>';
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}

	function test_strip_blacklisted_attributes() {
		$source = '<a href="/path/to/file.jpg" style="border: 1px solid red;">Link</a>';
		$expected = '<a href="/path/to/file.jpg">Link</a>';
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}

	function test_strip_on_attribute() {
		$source = '<a href="/path/to/file.jpg" onclick="alert(e);">Link</a>';
		$expected = '<a href="/path/to/file.jpg">Link</a>';
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}

	function test_strip_javascript_protocol() {
		$source = '<a href="javascript:alert(\'Hello\');">Click</a>';
		$expected = '<a>Click</a>';
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}

	function test_strip_attribute_recursive() {
		$source = '<div style="border: 1px solid red;"><a href="/path/to/file.jpg" onclick="alert(e);">Hello World</a></div>';
		$expected = '<div><a href="/path/to/file.jpg">Hello World</a></div>';
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}
}
