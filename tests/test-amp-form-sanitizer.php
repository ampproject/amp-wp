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
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		$this->go_to( '/current-page/' );
	}

	/**
	 * Data strings for testing converter.
	 *
	 * @return array
	 */
	public function get_data() {
		return array(
			'no_form' => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				null, // Same.
			),
			'form_with_get_method_http_action_and_no_target' => array(
				'<form method="get" action="http://example.org/example-page/"></form>',
				'<form method="get" action="//example.org/example-page/" target="_top"></form>',
			),
			'form_with_implicit_method_http_action_and_no_action_or_target' => array(
				'<form></form>',
				sprintf( '<form method="get" action="%s" target="_top"></form>', preg_replace( '#^https?:#', '', home_url( '/current-page/' ) ) ),
			),
			'form_with_empty_method_http_action_and_no_action_or_target' => array(
				'<form method="" action="https://example.com/" target="_top"></form>',
				'<form method="get" action="https://example.com/" target="_top"></form>',
			),
			'form_with_post_method_http_action_and_no_target' => array(
				'<form method="post" action="http://example.org/example-page/"></form>',
				'<form method="post" action-xhr="//example.org/example-page/?_wp_amp_action_xhr_converted=1" target="_top"></form>',
			),
			'form_with_post_method_http_action_and_blank_target' => array(
				'<form method="post" action-xhr="http://example.org/example-page/" target="_blank"></form>',
				'<form method="post" action-xhr="//example.org/example-page/" target="_blank"></form>',
			),
			'form_with_post_method_http_action_and_self_target' => array(
				'<form method="get" action="https://example.org/" target="_self"></form>',
				'<form method="get" action="https://example.org/" target="_top"></form>',
			),
			'form_with_post_method_https_action_and_custom_target' => array(
				'<form method="post" action="https://example.org/" target="some_other_target"></form>',
				'<form method="post" target="_blank" action-xhr="https://example.org/?_wp_amp_action_xhr_converted=1"></form>',
			),
		);
	}

	/**
	 * Test html conversion.
	 *
	 * @param string      $source   The source HTML.
	 * @param string|null $expected The expected HTML after conversion. Null means same as $source.
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected = null ) {
		if ( is_null( $expected ) ) {
			$expected = $source;
		}
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );

		$sanitizer = new AMP_Form_Sanitizer( $dom );
		$sanitizer->sanitize();

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test scripts.
	 */
	public function test_scripts() {
		$source   = '<form method="post" action-xhr="//example.org/example-page/" target="_top"></form>';
		$expected = array( 'amp-form' => 'https://cdn.ampproject.org/v0/amp-form-latest.js' );

		$dom                 = AMP_DOM_Utils::get_dom_from_content( $source );
		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = $whitelist_sanitizer->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
