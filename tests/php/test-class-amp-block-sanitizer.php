<?php
/**
 * Class AMP_Block_Sanitizer_Test.
 *
 * @package AMP
 */

/**
 * Class AMP_Block_Sanitizer_Test
 */
class AMP_Block_Sanitizer_Test extends WP_UnitTestCase {

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
				'<figure class="wp-block-embed  wp-has-aspect-ratio"><amp-facebook></amp-facebook><amp-facebook></amp-facebook></figure>',
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
				'<figure class="wp-block-embed-soundcloud wp-block-embed is-type-rich is-provider-soundcloud  wp-has-aspect-ratio"><div class="wp-block-embed__wrapper"><amp-soundcloud data-trackid="90097394" data-visual="true" height="3" width="4" layout="responsive"></amp-soundcloud></div></figure>',
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
