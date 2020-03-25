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
		add_theme_support( 'amp' );
		$this->go_to( '/current-page/' );
	}

	/**
	 * Tear down.
	 */
	public function tearDown() {
		remove_theme_support( 'amp' );
		parent::tearDown();
	}

	/**
	 * Data strings for testing converter.
	 *
	 * @return array
	 */
	public function get_data() {
		$form_template_pattern = (
			preg_quote( '<div class="amp-wp-default-form-message" submit-error=""><template type="amp-mustache">', '#' ) . '.+?</template></div>' .
			preg_quote( '<div class="amp-wp-default-form-message" submit-success=""><template type="amp-mustache">', '#' ) . '.+?</template></div>' .
			preg_quote( '<div class="amp-wp-default-form-message" submitting=""><template type="amp-mustache">', '#' ) . '.+?</template></div>'
		);

		return [
			'no_form' => [
				'<p>Lorem Ipsum Demet Delorit.</p>',
				null, // Same.
			],
			'form_with_get_method_http_action_and_no_target' => [
				'<form method="get" action="http://example.org/example-page/"></form>',
				'<form method="get" action="//example.org/example-page/" target="_top"></form>',
			],
			'form_with_http_action_and_port' => [
				'<form method="get" action="http://example.org:8080/example-page/"></form>',
				'<form method="get" action="//example.org:8080/example-page/" target="_top"></form>',
			],
			'form_with_http_action_and_user' => [
				'<form method="get" action="http://user@example.org:8080/example-page/"></form>',
				'<form method="get" action="//user@example.org:8080/example-page/" target="_top"></form>',
			],
			'form_with_http_action_and_user_pass' => [
				'<form method="get" action="http://user:pass@example.org:8080/example-page/"></form>',
				'<form method="get" action="//user:pass@example.org:8080/example-page/" target="_top"></form>',
			],
			'form_with_implicit_method_http_action_and_no_action_or_target' => [
				'<form></form>',
				sprintf( '<form method="get" action="%s" target="_top"></form>', preg_replace( '#^https?:#', '', home_url( '/current-page/' ) ) ),
			],
			'form_with_empty_method_http_action_and_no_action_or_target' => [
				'<form method="" action="https://example.com/" target="_top"></form>',
				'<form method="get" action="https://example.com/" target="_top"></form>',
			],
			'form_with_post_method_http_action_and_no_target' => [
				'<form method="post" action="http://example.org/example-page/"></form>',
				'#' . preg_quote( '<form method="post" action-xhr="//example.org/example-page/?_wp_amp_action_xhr_converted=1" target="_top">', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_post_method_http_action_and_blank_target' => [
				'<form method="post" action-xhr="http://example.org/example-page/" target="_blank"></form>',
				'<form method="post" action-xhr="//example.org/example-page/" target="_blank"></form>',
			],
			'form_with_post_method_http_action_and_self_target' => [
				'<form method="get" action="https://example.org/" target="_self"></form>',
				'<form method="get" action="https://example.org/" target="_top"></form>',
			],
			'form_with_post_method_https_action_and_custom_target' => [
				'<form method="post" action="https://example.org/" target="some_other_target"></form>',
				'#' . preg_quote( '<form method="post" target="_blank" action-xhr="https://example.org/?_wp_amp_action_xhr_converted=1">', '#' ) . $form_template_pattern . '</form>#s',
			],
			'jetpack_contact_form' => [
				'<form action="https://src.wordpress-develop.test/contact/#contact-form-9" method="post" class="contact-form commentsblock"><div class="element-has-attributes">hello</div><div><label for="g9-favoritenumber" class="grunion-field-label text">Favorite number</label><input type="text" name="g9-favoritenumber" id="g9-favoritenumber" value="" class="text"></div><p class="contact-submit"><input type="submit" value="Submit" class="pushbutton-wide"><input type="hidden" id="_wpnonce" name="_wpnonce" value="640996fb1e"><input type="hidden" name="_wp_http_referer" value="/contact/"><input type="hidden" name="contact-form-id" value="9"><input type="hidden" name="action" value="grunion-contact-form"><input type="hidden" name="contact-form-hash" value="df9f9136763f5eb819f433e4fe4af3447534e8cc"></p></form>',
				'#' . preg_quote( '<form method="post" class="contact-form commentsblock" action-xhr="https://src.wordpress-develop.test/contact/?_wp_amp_action_xhr_converted=1#contact-form-9" target="_top"><div class="element-has-attributes">hello</div><div><label for="g9-favoritenumber" class="grunion-field-label text">Favorite number</label><input type="text" name="g9-favoritenumber" id="g9-favoritenumber" value="" class="text"></div><p class="contact-submit"><input type="submit" value="Submit" class="pushbutton-wide"><input type="hidden" id="_wpnonce" name="_wpnonce" value="640996fb1e"><input type="hidden" name="_wp_http_referer" value="/contact/"><input type="hidden" name="contact-form-id" value="9"><input type="hidden" name="action" value="grunion-contact-form"><input type="hidden" name="contact-form-hash" value="df9f9136763f5eb819f433e4fe4af3447534e8cc"></p>', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_upload' => [
				'<form action="https://src.wordpress-develop.test/upload/" method="post"><input type="file" name="upload"><button type="submit">Submit</button></form>',
				'#' . preg_quote( '<form method="post" action-xhr="https://src.wordpress-develop.test/upload/?_wp_amp_action_xhr_converted=1" target="_top"><input type="file" name="upload"><button type="submit">Submit</button>', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_password' => [
				'<form action="https://src.wordpress-develop.test/login/" method="post"><input type="password" name="password"><button type="submit">Submit</button></form>',
				'#' . preg_quote( '<form method="post" action-xhr="https://src.wordpress-develop.test/login/?_wp_amp_action_xhr_converted=1" target="_top"><input type="password" name="password"><button type="submit">Submit</button>', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_relative_action_url' => [
				'<form method="post" action="/login/"></form>',
				'#' . preg_quote( '<form method="post" action-xhr="//example.org/login/?_wp_amp_action_xhr_converted=1" target="_top">', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_relative_path_action_url' => [
				'<form method="post" action="../"></form>',
				'#' . preg_quote( '<form method="post" action-xhr="//example.org/current-page/../?_wp_amp_action_xhr_converted=1" target="_top">', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_another_relative_path_action_url' => [
				'<form method="post" action="foo/"></form>',
				'#' . preg_quote( '<form method="post" action-xhr="//example.org/current-page/foo/?_wp_amp_action_xhr_converted=1" target="_top">', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_relative_query_action_url' => [
				'<form method="post" action="?foo=bar"></form>',
				'#' . preg_quote( '<form method="post" action-xhr="//example.org/current-page/?foo=bar&amp;_wp_amp_action_xhr_converted=1" target="_top">', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_multiple_relative_queries_action_url' => [
				'<form method="post" action="?foo=bar&baz=buzz"></form>',
				'#' . preg_quote( '<form method="post" action-xhr="//example.org/current-page/?foo=bar&amp;baz=buzz&amp;_wp_amp_action_xhr_converted=1" target="_top">', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_relative_fragment_action_url' => [
				'<form method="post" action="#foo"></form>',
				'#' . preg_quote( '<form method="post" action-xhr="//example.org/current-page/?_wp_amp_action_xhr_converted=1#foo" target="_top">', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_relative_query_and_fragment_action_url' => [
				'<form method="post" action="?foo=bar#baz"></form>',
				'#' . preg_quote( '<form method="post" action-xhr="//example.org/current-page/?foo=bar&amp;_wp_amp_action_xhr_converted=1#baz" target="_top">', '#' ) . $form_template_pattern . '</form>#s',
			],
			'form_with_pathless_url' => [
				'<form method="post" action="//example.com"></form>',
				'#' . preg_quote( '<form method="post" action-xhr="//example.com?_wp_amp_action_xhr_converted=1" target="_top">', '#' ) . $form_template_pattern . '</form>#s',
			],
			'test_with_dev_mode' => [
				'<form data-ampdevmode="" action="javascript:"></form>',
				null, // No change.
				[
					'add_dev_mode' => true,
				],
			],
		];
	}

	/**
	 * Test html conversion.
	 *
	 * @param string      $source   The source HTML.
	 * @param string|null $expected The expected HTML after conversion. Null means same as $source.
	 * @param array       $args     Args.
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected = null, $args = [] ) {
		if ( is_null( $expected ) ) {
			$expected = $source;
		}
		if ( '#' !== $expected[0] ) {
			$expected = '#' . preg_quote( $expected, '#' ) . '#s';
		}
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		if ( ! empty( $args['add_dev_mode'] ) ) {
			$dom->ampDevModeActive = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$dom->documentElement->setAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE, '' );
		}

		$sanitizer = new AMP_Form_Sanitizer( $dom );
		$sanitizer->sanitize();

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );

		$this->assertRegExp( $expected, $content );
	}

	/**
	 * Test scripts.
	 */
	public function test_scripts() {
		$source   = '<form method="post" action-xhr="//example.org/example-page/" target="_top"></form>';
		$expected = [ 'amp-form' => true ];

		$dom                 = AMP_DOM_Utils::get_dom_from_content( $source );
		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = $whitelist_sanitizer->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
