<?php

class AMP_Sanitizer_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'empty' => array(
				'',
				''
			),

			'blacklisted_tag_with_innertext' => array(
				'<script>alert("")</script>',
				''
			),

			'multiple_blacklisted_tags_only' => array(
				'<input type="text" /><script>alert("")</script><style>body{ color: red; }</style>',
				''
			),

			'whitelisted_tag_only' => array(
				'<p>Text</p><img src="/path/to/file.jpg" />',
				'<p>Text</p><img src="/path/to/file.jpg"></img>' // LIBXML_NOEMPTYTAG
			),

			'blacklisted_attributes' => array(
				'<a href="/path/to/file.jpg" style="border: 1px solid red;">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>'
			),

			'on_attribute' => array(
				'<a href="/path/to/file.jpg" onclick="alert(e);">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>'
			),

			'javascript_protocol' => array(
				'<a href="javascript:alert(\'Hello\');">Click</a>',
				'<a>Click</a>'
			),

			'attribute_recursive' => array(
				'<div style="border: 1px solid red;"><a href="/path/to/file.jpg" onclick="alert(e);">Hello World</a></div>',
				'<div><a href="/path/to/file.jpg">Hello World</a></div>'
			),

			'mixed_tags' => array(
				'<input type="text" /><p>Text</p><script>alert("")</script><style>body{ color: red; }</style>',
				'<p>Text</p>'
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_sanitizer( $source, $expected ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Blacklist_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}
}
