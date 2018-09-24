<?php
/**
 * Tests for form sanitization.
 *
 * @package AMP
 */

// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

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
				'<form method="post" action-xhr="//example.org/example-page/?_wp_amp_action_xhr_converted=1" target="_top"><div submit-error=""><template type="amp-mustache">{{{error}}}</template></div></form>',
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
				'<form method="post" target="_blank" action-xhr="https://example.org/?_wp_amp_action_xhr_converted=1"><div submit-error=""><template type="amp-mustache">{{{error}}}</template></div></form>',
			),
			'jetpack_contact_form' => array(
				'<form action="https://src.wordpress-develop.test/contact/#contact-form-9" method="post" class="contact-form commentsblock"><div class="element-has-attributes">hello</div><div><label for="g9-favoritenumber" class="grunion-field-label text">Favorite number</label><input type="text" name="g9-favoritenumber" id="g9-favoritenumber" value="" class="text"></div><p class="contact-submit"><input type="submit" value="Submit" class="pushbutton-wide"><input type="hidden" id="_wpnonce" name="_wpnonce" value="640996fb1e"><input type="hidden" name="_wp_http_referer" value="/contact/"><input type="hidden" name="contact-form-id" value="9"><input type="hidden" name="action" value="grunion-contact-form"><input type="hidden" name="contact-form-hash" value="df9f9136763f5eb819f433e4fe4af3447534e8cc"></p></form>',
				'<form method="post" class="contact-form commentsblock" action-xhr="https://src.wordpress-develop.test/contact/?_wp_amp_action_xhr_converted=1#contact-form-9" target="_top"><div class="element-has-attributes">hello</div><div><label for="g9-favoritenumber" class="grunion-field-label text">Favorite number</label><input type="text" name="g9-favoritenumber" id="g9-favoritenumber" value="" class="text"></div><p class="contact-submit"><input type="submit" value="Submit" class="pushbutton-wide"><input type="hidden" id="_wpnonce" name="_wpnonce" value="640996fb1e"><input type="hidden" name="_wp_http_referer" value="/contact/"><input type="hidden" name="contact-form-id" value="9"><input type="hidden" name="action" value="grunion-contact-form"><input type="hidden" name="contact-form-hash" value="df9f9136763f5eb819f433e4fe4af3447534e8cc"></p><div submit-error=""><template type="amp-mustache">{{{error}}}</template></div></form>',
			),
			'form_with_upload' => array(
				'<form action="https://src.wordpress-develop.test/upload/" method="post"><input type="file" name="upload"><button type="submit">Submit</button></form>',
				'<form method="post" action-xhr="https://src.wordpress-develop.test/upload/?_wp_amp_action_xhr_converted=1" target="_top"><input type="file" name="upload"><button type="submit">Submit</button><div submit-error=""><template type="amp-mustache">{{{error}}}</template></div></form>',
			),
			'form_with_password' => array(
				'<form action="https://src.wordpress-develop.test/login/" method="post"><input type="password" name="password"><button type="submit">Submit</button></form>',
				'<form method="post" action-xhr="https://src.wordpress-develop.test/login/?_wp_amp_action_xhr_converted=1" target="_top"><input type="password" name="password"><button type="submit">Submit</button><div submit-error=""><template type="amp-mustache">{{{error}}}</template></div></form>',
			),
			'form_with_relative_action_url' => array(
				'<form method="post" action="/login/"></form>',
				'<form method="post" action-xhr="//example.org/login/?_wp_amp_action_xhr_converted=1" target="_top"><div submit-error=""><template type="amp-mustache">{{{error}}}</template></div></form>',
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
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );

		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test scripts.
	 */
	public function test_scripts() {
		$source   = '<form method="post" action-xhr="//example.org/example-page/" target="_top"></form>';
		$expected = array( 'amp-form' => true );

		$dom                 = AMP_DOM_Utils::get_dom_from_content( $source );
		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = $whitelist_sanitizer->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
