<?php
/**
 * Test AMP_Script_Sanitizer.
 *
 * @package AMP
 */

/**
 * Test AMP_Script_Sanitizer.
 *
 * @covers AMP_Script_Sanitizer
 */
class AMP_Script_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Data for testing noscript handling.
	 *
	 * @return array
	 */
	public function get_noscript_data() {
		return array(
			'document_write'      => array(
				'<html><head></head><body>Has script? <script>document.write("Yep!")</script><noscript>Nope!</noscript></body></html>',
				'<html><head></head><body>Has script? <!--noscript-->Nope!<!--/noscript--></body></html>',
			),
			'nested_elements'     => array(
				'<html><head></head><body><noscript>before <em><strong>middle</strong> end</em></noscript></body></html>',
				'<html><head></head><body><!--noscript-->before <em><strong>middle</strong> end</em><!--/noscript--></body></html>',
			),
			'head_noscript_style' => array(
				'<html><head><noscript><style>body{color:red}</style></noscript></head><body></body></html>',
				'<html><head><!--noscript--><style>body{color:red}</style><!--/noscript--></head><body></body></html>',
			),
			'head_noscript_span'  => array(
				'<html><head><noscript><span>No script</span></noscript></head><body></body></html>',
				'<html><head></head><body><!--noscript--><span>No script</span><!--/noscript--></body></html>',
			),
		);
	}

	/**
	 * Test that noscript elements get replaced with their children.
	 *
	 * @dataProvider get_noscript_data
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 * @covers AMP_Script_Sanitizer::sanitize()
	 */
	public function test_noscript_promotion( $source, $expected = null ) {
		$dom = AMP_DOM_Utils::get_dom( $source );
		$this->assertSame( 1, $dom->getElementsByTagName( 'noscript' )->length );
		$sanitizer = new AMP_Script_Sanitizer( $dom );
		$sanitizer->sanitize();
		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );
		$this->assertEquals( $expected, $content );
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
		$args = array(
			'use_document_element' => true,
		);

		$dom = AMP_DOM_Utils::get_dom( $html );
		AMP_Content_Sanitizer::sanitize_document( $dom, amp_get_content_sanitizers(), $args );

		$content = AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );

		$this->assertRegExp( '/<!-- Google Tag Manager -->\s*<!-- End Google Tag Manager -->/', $content );
		$this->assertContains( '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>', $content );
		$this->assertContains( 'Has script? <!--noscript-->Nope!<!--/noscript-->', $content );
		$this->assertContains( '<!--noscript--><amp-iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXX" height="400" sandbox="allow-scripts allow-same-origin" layout="fixed-height" class="amp-wp-b3bfe1b"><span placeholder="" class="amp-wp-iframe-placeholder"></span><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXX" height="0" width="0" class="amp-wp-b3bfe1b"></iframe></noscript></amp-iframe><!--/noscript-->', $content );
	}
}
