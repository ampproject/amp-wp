<?php
/**
 * Tests for form sanitisation.
 *
 * @package AMP
 */

/**
 * Class AMP_Form_Sanitizer_Test
 *
 * @group amp-comments
 * @group amp-form
 */
class AMP_Form_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Data strings for testing converter.
	 *
	 * @return array
	 */
	public function get_data() {
		return array(
			'no_form'                           => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),
			'form_with_get_method_http_action'  => array(
				'<form method="GET" action="http://example.org/example-page/"></form>',
				'<form method="GET" action="//example.org/example-page/" target="_top"></form>',
			),
			'form_with_post_method_http_action' => array(
				'<form method="POST" action="http://example.org/example-page/"></form>',
				'<form method="POST" action-xhr="//example.org/example-page/" target="_top"></form>',
			),

		);
	}

	/**
	 * Test html conversion.
	 *
	 * @param string $source The source HTML.
	 * @param string $expected The expected HTML after conversion.
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Form_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	public function test_scripts() {
		$source   = '<form method="POST" action-xhr="//example.org/example-page/" target="_top"></form>';
		$expected = array( 'amp-form' => 'https://cdn.ampproject.org/v0/amp-form-latest.js' );

		$dom                 = AMP_DOM_Utils::get_dom_from_content( $source );
		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = $whitelist_sanitizer->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
