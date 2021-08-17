<?php
/**
 * Test AMP_Script_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Dom\Options;
use AmpProject\Dom\Document;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;

/**
 * Test AMP_Script_Sanitizer.
 *
 * @covers AMP_Script_Sanitizer
 */
class AMP_Script_Sanitizer_Test extends TestCase {

	use MarkupComparison;

	/**
	 * Data for testing noscript handling.
	 *
	 * @return array
	 */
	public function get_sanitizer_data() {
		return [
			'document_write'           => [
				'<html><head><meta charset="utf-8"></head><body>Has script? <script>document.write("Yep!")</script><noscript>Nope!</noscript></body></html>',
				'<html><head><meta charset="utf-8"></head><body>Has script? <!--noscript-->Nope!<!--/noscript--></body></html>',
			],
			'nested_elements'          => [
				'<html><head><meta charset="utf-8"></head><body><noscript>before <em><strong>middle</strong> end</em></noscript></body></html>',
				'<html><head><meta charset="utf-8"></head><body><!--noscript-->before <em><strong>middle</strong> end</em><!--/noscript--></body></html>',
			],
			'head_noscript_style'      => [
				'<html><head><meta charset="utf-8"><noscript><style>body{color:red}</style></noscript></head><body></body></html>',
				'<html><head><meta charset="utf-8"><!--noscript--><style>body{color:red}</style><!--/noscript--></head><body></body></html>',
			],
			'head_noscript_span'       => [
				'<html><head><meta charset="utf-8"><noscript><span>No script</span></noscript></head><body></body></html>',
				'<html><head><meta charset="utf-8"></head><body><!--noscript--><span>No script</span><!--/noscript--></body></html>',
			],
			'test_with_dev_mode'       => [
				'<html data-ampdevmode=""><head><meta charset="utf-8"></head><body><noscript data-ampdevmode="">hey</noscript></body></html>',
				null,
			],
			'noscript_no_unwrap_attr'  => [
				'<html><head><meta charset="utf-8"></head><body><noscript data-amp-no-unwrap><span>No script</span></noscript></body></html>',
				null,
			],
			'noscript_no_unwrap_arg'   => [
				'<html><head><meta charset="utf-8"></head><body><noscript><span>No script</span></noscript></body></html>',
				null,
				[
					'unwrap_noscripts' => false,
				],
			],
			'script_kept_no_unwrap'    => [
				'
					<html><head><meta charset="utf-8"></head><body>
						<script>document.write("Hey.")</script>
						<noscript data-amp-no-unwrap><span>No script</span></noscript>
					</body></html>',
				'
					<html data-ampdevmode=""><head><meta charset="utf-8"></head><body>
						<script data-ampdevmode="">document.write("Hey.")</script>
						<noscript data-amp-no-unwrap><span>No script</span></noscript>
					</body></html>
				',
				[
					'sanitize_js_scripts'       => true,
					'unwrap_noscripts'          => true, // This will be ignored because of the kept script.
					'validation_error_callback' => '__return_false',
				],
				[
					AMP_Script_Sanitizer::CUSTOM_INLINE_SCRIPT,
				],
			],
			'inline_scripts_removed'   => [
				'
					<html><head><meta charset="utf-8"></head><body>
						<script>document.write("Hey.")</script>
						<script type="application/json">{"data":1}</script>
						<script type="application/ld+json">{"data":2}</script>
					</body></html>
				',
				'
					<html><head><meta charset="utf-8"></head><body>
					<script type="application/ld+json">{"data":2}</script>
					</body></html>
				',
				[
					'sanitize_js_scripts' => true,
				],
				[
					AMP_Script_Sanitizer::CUSTOM_INLINE_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_JSON_SCRIPT,
				],
			],
			'external_scripts_removed' => [
				'
					<html>
					<head><meta charset="utf-8"></head>
					<body>
						<script src="https://example.com/1"></script>
						<script type="text/javascript" src="https://example.com/2"></script>
						<script type="module" src="https://example.com/3"></script>
					</body></html>
				',
				'
					<html>
					<head><meta charset="utf-8"></head>
					<body></body></html>
				',
				[
					'sanitize_js_scripts' => true,
				],
				[
					AMP_Script_Sanitizer::CUSTOM_EXTERNAL_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_EXTERNAL_SCRIPT,
					AMP_Script_Sanitizer::CUSTOM_EXTERNAL_SCRIPT,
				],
			],
		];
	}

	/**
	 * Test that noscript elements get replaced with their children.
	 *
	 * @dataProvider get_sanitizer_data
	 * @param string $source        Source.
	 * @param string $expected      Expected.
	 * @param array $sanitizer_args Sanitizer args.
	 * @covers AMP_Script_Sanitizer::sanitize()
	 */
	public function test_sanitize( $source, $expected = null, $sanitizer_args = [], $expected_error_codes = [] ) {
		if ( null === $expected ) {
			$expected = $source;
		}
		$dom = Document::fromHtml( $source, Options::DEFAULTS );
		$this->assertSame( substr_count( $source, '<noscript' ), $dom->getElementsByTagName( 'noscript' )->length );

		$validation_error_callback_arg = isset( $sanitizer_args['validation_error_callback'] ) ? $sanitizer_args['validation_error_callback'] : null;

		$actual_error_codes = [];

		$sanitizer_args['validation_error_callback'] = static function ( $error ) use ( &$actual_error_codes, $validation_error_callback_arg ) {
			$actual_error_codes[] = $error['code'];

			if ( $validation_error_callback_arg ) {
				return $validation_error_callback_arg();
			} else {
				return true;
			}
		};

		$sanitizer = new AMP_Script_Sanitizer( $dom, $sanitizer_args );
		$sanitizer->sanitize();
		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();
		$content = $dom->saveHTML( $dom->documentElement );
		$this->assertSimilarMarkup( $expected, $content );

		$this->assertSame( $expected_error_codes, $actual_error_codes );
	}

	/**
	 * Test style[amp-boilerplate] preservation.
	 */
	public function test_boilerplate_preservation() {
		ob_start();
		?>
		<!doctype html>
		<html amp>
			<head>
				<meta charset="utf-8">
				<link rel="canonical" href="self.html" />
				<meta name="viewport" content="width=device-width,minimum-scale=1">
				<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
				<script async src="https://cdn.ampproject.org/v0.js"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>

				<!-- Google Tag Manager -->
				<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
				new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
				j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
				'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
				})(window,document,'script','dataLayer','GTM-XXXX');</script>
				<!-- End Google Tag Manager -->
			</head>
			<body>
				<!-- Google Tag Manager (noscript) -->
				<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-XXXX"
				height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
				<!-- End Google Tag Manager (noscript) -->

				Hello, AMP world.
				Has script? <script>document.write("Yep!")</script><noscript>Nope!</noscript>
			</body>
		</html>
		<?php
		$html = ob_get_clean();
		$args = [
			'use_document_element' => true,
		];

		$dom = Document::fromHtml( $html, Options::DEFAULTS );
		AMP_Content_Sanitizer::sanitize_document( $dom, amp_get_content_sanitizers(), $args );

		$content = $dom->saveHTML( $dom->documentElement );

		$this->assertMatchesRegularExpression( '/<!-- Google Tag Manager -->\s*<!-- End Google Tag Manager -->/', $content );
		$this->assertStringContainsString( '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>', $content );
		$this->assertStringContainsString( 'Has script? <!--noscript-->Nope!<!--/noscript-->', $content );
		$this->assertStringContainsString( '<!--noscript--><amp-iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXX" height="400" layout="fixed-height" width="auto" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" data-amp-original-style="display:none;visibility:hidden" class="amp-wp-b3bfe1b"><span placeholder="" class="amp-wp-iframe-placeholder"></span><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXX" height="0" width="0"></iframe></noscript></amp-iframe><!--/noscript-->', $content );
	}
}
