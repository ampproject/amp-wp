<?php
/**
 * Class AMP_Block_Sanitizer_Test.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\TestCase;

/**
 * Class AMP_Block_Sanitizer_Test
 */
class AMP_Block_Sanitizer_Test extends TestCase {

	/**
	 * Get data.
	 *
	 * @return array
	 */
	public function get_data() {
		return [
			'no_figures'           => [
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			],

			'more_than_one_child'  => [
				'<figure class="wp-block-embed wp-embed-aspect-16-9 wp-has-aspect-ratio"><amp-facebook></amp-facebook><amp-facebook></amp-facebook></figure>',
				'<figure class="wp-block-embed"><amp-facebook></amp-facebook><amp-facebook></amp-facebook></figure>',
			],

			'no_wp_block_embed'    => [
				'<figure><amp-facebook></amp-facebook></figure>',
				'<figure><amp-facebook></amp-facebook></figure>',
			],

			'data_amp_noloading'   => [
				'<figure class="wp-block-embed" data-amp-noloading="true"><amp-facebook></amp-facebook></figure>',
				'<figure class="wp-block-embed" data-amp-noloading="true"><amp-facebook noloading="" layout="fixed-height"></amp-facebook></figure>',
			],

			'data_amp_layout_fill' => [
				'<figure class="wp-block-embed" data-amp-layout="fill"><amp-facebook width="100"></amp-facebook></figure>',
				'<figure class="wp-block-embed" data-amp-layout="fill" style="position:relative; width: 100%; height: 400px;"><amp-facebook layout="fill"></amp-facebook></figure>',
			],

			'responsive_layout'    => [
				'<figure class="wp-block-embed-soundcloud wp-block-embed is-type-rich is-provider-soundcloud wp-embed-aspect-4-3 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper"><amp-soundcloud data-trackid="90097394" data-visual="true" height="400" width="640" layout="responsive"></amp-soundcloud></div></figure>',
				'<figure class="wp-block-embed-soundcloud wp-block-embed is-type-rich is-provider-soundcloud"><div class="wp-block-embed__wrapper"><amp-soundcloud data-trackid="90097394" data-visual="true" height="3" width="4" layout="responsive"></amp-soundcloud></div></figure>',
			],

			'intrinsic_layout'     => [
				'<figure class="wp-block-embed-slideshare wp-block-embed is-type-rich is-provider-slideshare wp-embed-aspect-1-1 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper"><amp-iframe title="AMP in WordPress, the WordPress Way" src="https://www.slideshare.net/slideshow/embed_code/key/4wRcvt5FDQhkgB" width="427" height="356" frameborder="0" scrolling="no" allowfullscreen="" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes"><span placeholder="" class="amp-wp-iframe-placeholder"></span></amp-iframe></div></figure>',
				'<figure class="wp-block-embed-slideshare wp-block-embed is-type-rich is-provider-slideshare"><div class="wp-block-embed__wrapper"><amp-iframe title="AMP in WordPress, the WordPress Way" src="https://www.slideshare.net/slideshow/embed_code/key/4wRcvt5FDQhkgB" width="1" height="1" frameborder="0" scrolling="no" allowfullscreen="" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="responsive" class="amp-wp-enforced-sizes"><span placeholder="" class="amp-wp-iframe-placeholder"></span></amp-iframe></div></figure>',
			],
		];
	}

	/**
	 * Test sanitizer.
	 *
	 * @dataProvider get_data
	 * @param string $source Source.
	 * @param string $expected Expected.
	 */
	public function test_sanitizer( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Block_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );
	}
}
