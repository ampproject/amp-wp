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
		$expected = '<p>Text</p><img src="/path/to/file.jpg"/>';
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
		$source = '<img src="/path/to/file.jpg" style="border: 1px solid red;"/>';
		$expected = '<img src="/path/to/file.jpg"/>';
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}

	function test_strip_on_attribute() {
		$source = '<img src="/path/to/file.jpg" onclick="alert(e);" />';
		$expected = '<img src="/path/to/file.jpg"/>';
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
		$source = '<div style="border: 1px solid red;"><img src="/path/to/file.jpg" onclick="alert(e);" />Hello World</div>';
		$expected = '<div><img src="/path/to/file.jpg"/>Hello World</div>';
		$content = AMP_Sanitizer::strip( $source );
		$this->assertEquals( $expected, $content );
	}
}
