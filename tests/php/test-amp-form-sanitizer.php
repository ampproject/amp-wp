<?php
/**
 * Tests for form sanitization.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\Dom\Document;

// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

/**
 * Class AMP_Form_Sanitizer_Test
 *
 * @group amp-comments
 * @group amp-form
 */
class AMP_Form_Sanitizer_Test extends WP_UnitTestCase {

	use MarkupComparison;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->go_to( '/current-page/' );
	}

	/**
	 * Tear down.
	 */
	public function tearDown() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		parent::tearDown();
	}

	/**
	 * Data strings for testing converter.
	 *
	 * @return array
	 */
	public function get_data() {
		$form_templates = '
			<div class="amp-wp-default-form-message" submit-error=""><template type="amp-mustache">...</template></div>
			<div class="amp-wp-default-form-message" submit-success=""><template type="amp-mustache">...</template></div>
			<div class="amp-wp-default-form-message" submitting=""><template type="amp-mustache">...</template></div>
		';

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
				'<form method="post" action-xhr="//example.org/example-page/?_wp_amp_action_xhr_converted=1" target="_top">' . $form_templates . '</form>',
			],
			'form_with_exiting_mustache_templates_left_intact' => [
				'
				<form method="post" action="http://example.org/example-page/">
					<div class="amp-wp-default-form-message" data-mine submit-error=""><template type="amp-mustache">...</template></div>
					<div class="amp-wp-default-form-message" data-mine submit-success=""><template type="amp-mustache">...</template></div>
					<div class="amp-wp-default-form-message" data-mine submitting=""><template type="amp-mustache">...</template></div>
				</form>
				',
				'
				<form method="post" action-xhr="//example.org/example-page/?_wp_amp_action_xhr_converted=1" target="_top">
					<div class="amp-wp-default-form-message" data-mine submit-error=""><template type="amp-mustache">...</template></div>
					<div class="amp-wp-default-form-message" data-mine submit-success=""><template type="amp-mustache">...</template></div>
					<div class="amp-wp-default-form-message" data-mine submitting=""><template type="amp-mustache">...</template></div>
				</form>
				',
			],
			'form_with_exiting_mustache_plain_text_scripts_left_intact' => [
				'
				<form method="post" action="http://example.org/example-page/">
					<div class="amp-wp-default-form-message" data-mine submit-error=""><script type="text/plain" template="amp-mustache">...</script></div>
					<div class="amp-wp-default-form-message" data-mine submit-success=""><script type="text/plain" template="amp-mustache">...</script></div>
					<div class="amp-wp-default-form-message" data-mine submitting=""><script type="text/plain" template="amp-mustache">...</script></div>
				</form>
				',
				'
				<form method="post" action-xhr="//example.org/example-page/?_wp_amp_action_xhr_converted=1" target="_top">
					<div class="amp-wp-default-form-message" data-mine submit-error=""><script type="text/plain" template="amp-mustache">...</script></div>
					<div class="amp-wp-default-form-message" data-mine submit-success=""><script type="text/plain" template="amp-mustache">...</script></div>
					<div class="amp-wp-default-form-message" data-mine submitting=""><script type="text/plain" template="amp-mustache">...</script></div>
				</form>
				',
			],
			'form_with_not_all_templates_templates_supplied' => [
				'
				<form method="post" action="http://example.org/example-page/">
					<div class="amp-wp-default-form-message" data-mine submit-success=""><script type="text/plain" template="amp-mustache">...</script></div>
				</form>
				',
				'
				<form method="post" action-xhr="//example.org/example-page/?_wp_amp_action_xhr_converted=1" target="_top">
					<div class="amp-wp-default-form-message" data-mine submit-success=""><script type="text/plain" template="amp-mustache">...</script></div>
					<div class="amp-wp-default-form-message" submit-error=""><template type="amp-mustache">...</template></div>
					<div class="amp-wp-default-form-message" submitting=""><template type="amp-mustache">...</template></div>
				</form>
				',
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
				'<form method="post" target="_blank" action-xhr="https://example.org/?_wp_amp_action_xhr_converted=1">' . $form_templates . '</form>',
			],
			'jetpack_contact_form' => [
				'<form action="https://src.wordpress-develop.test/contact/#contact-form-9" method="post" class="contact-form commentsblock"><div class="element-has-attributes">hello</div><div><label for="g9-favoritenumber" class="grunion-field-label text">Favorite number</label><input type="text" name="g9-favoritenumber" id="g9-favoritenumber" value="" class="text"></div><p class="contact-submit"><input type="submit" value="Submit" class="pushbutton-wide"><input type="hidden" id="_wpnonce" name="_wpnonce" value="640996fb1e"><input type="hidden" name="_wp_http_referer" value="/contact/"><input type="hidden" name="contact-form-id" value="9"><input type="hidden" name="action" value="grunion-contact-form"><input type="hidden" name="contact-form-hash" value="df9f9136763f5eb819f433e4fe4af3447534e8cc"></p></form>',
				'<form method="post" class="contact-form commentsblock" action-xhr="https://src.wordpress-develop.test/contact/?_wp_amp_action_xhr_converted=1#contact-form-9" target="_top"><div class="element-has-attributes">hello</div><div><label for="g9-favoritenumber" class="grunion-field-label text">Favorite number</label><input type="text" name="g9-favoritenumber" id="g9-favoritenumber" value="" class="text"></div><p class="contact-submit"><input type="submit" value="Submit" class="pushbutton-wide"><input type="hidden" id="_wpnonce" name="_wpnonce" value="640996fb1e"><input type="hidden" name="_wp_http_referer" value="/contact/"><input type="hidden" name="contact-form-id" value="9"><input type="hidden" name="action" value="grunion-contact-form"><input type="hidden" name="contact-form-hash" value="df9f9136763f5eb819f433e4fe4af3447534e8cc"></p>' . $form_templates . '</form>',
			],
			'form_with_upload' => [
				'<form action="https://src.wordpress-develop.test/upload/" method="post"><input type="file" name="upload"><button type="submit">Submit</button></form>',
				'<form method="post" action-xhr="https://src.wordpress-develop.test/upload/?_wp_amp_action_xhr_converted=1" target="_top"><input type="file" name="upload"><button type="submit">Submit</button>' . $form_templates . '</form>',
			],
			'form_with_password' => [
				'<form action="https://src.wordpress-develop.test/login/" method="post"><input type="password" name="password"><button type="submit">Submit</button></form>',
				'<form method="post" action-xhr="https://src.wordpress-develop.test/login/?_wp_amp_action_xhr_converted=1" target="_top"><input type="password" name="password"><button type="submit">Submit</button>' . $form_templates . '</form>',
			],
			'form_with_relative_action_url' => [
				'<form method="post" action="/login/"></form>',
				'<form method="post" action-xhr="//example.org/login/?_wp_amp_action_xhr_converted=1" target="_top">' . $form_templates . '</form>',
			],
			'form_with_relative_path_action_url' => [
				'<form method="post" action="../"></form>',
				'<form method="post" action-xhr="//example.org/current-page/../?_wp_amp_action_xhr_converted=1" target="_top">' . $form_templates . '</form>',
			],
			'form_with_another_relative_path_action_url' => [
				'<form method="post" action="foo/"></form>',
				'<form method="post" action-xhr="//example.org/current-page/foo/?_wp_amp_action_xhr_converted=1" target="_top">' . $form_templates . '</form>',
			],
			'form_with_relative_query_action_url' => [
				'<form method="post" action="?foo=bar"></form>',
				'<form method="post" action-xhr="//example.org/current-page/?foo=bar&amp;_wp_amp_action_xhr_converted=1" target="_top">' . $form_templates . '</form>',
			],
			'form_with_multiple_relative_queries_action_url' => [
				'<form method="post" action="?foo=bar&baz=buzz"></form>',
				'<form method="post" action-xhr="//example.org/current-page/?foo=bar&amp;baz=buzz&amp;_wp_amp_action_xhr_converted=1" target="_top">' . $form_templates . '</form>',
			],
			'form_with_relative_fragment_action_url' => [
				'<form method="post" action="#foo"></form>',
				'<form method="post" action-xhr="//example.org/current-page/?_wp_amp_action_xhr_converted=1#foo" target="_top">' . $form_templates . '</form>',
			],
			'form_with_relative_query_and_fragment_action_url' => [
				'<form method="post" action="?foo=bar#baz"></form>',
				'<form method="post" action-xhr="//example.org/current-page/?foo=bar&amp;_wp_amp_action_xhr_converted=1#baz" target="_top">' . $form_templates . '</form>',
			],
			'form_with_pathless_url' => [
				'<form method="post" action="//example.com"></form>',
				'<form method="post" action-xhr="//example.com?_wp_amp_action_xhr_converted=1" target="_top">' . $form_templates . '</form>',
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
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		if ( ! empty( $args['add_dev_mode'] ) ) {
			$dom->documentElement->setAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE, '' );
		}

		$sanitizer = new AMP_Form_Sanitizer( $dom );
		$sanitizer->sanitize();

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		// Normalize the contents of the templates.
		foreach ( $dom->xpath->query( Document::XPATH_MUSTACHE_TEMPLATE_ELEMENTS_QUERY, $dom->body ) as $template ) {
			while ( $template->firstChild ) {
				$template->removeChild( $template->firstChild );
			}
			$template->appendChild( $dom->createTextNode( '...' ) );
		}

		$this->assertEqualMarkup( AMP_DOM_Utils::get_content_from_dom( $dom ), $expected );
	}

	/**
	 * Test scripts.
	 */
	public function test_scripts() {
		$source   = '<form method="post" action-xhr="//example.org/example-page/" target="_top"></form>';
		$expected = [ 'amp-form' => true ];

		$dom                  = AMP_DOM_Utils::get_dom_from_content( $source );
		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$scripts = $validating_sanitizer->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
