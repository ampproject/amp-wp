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
		return array(
			'no_figures'           => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'more_than_one_child'  => array(
				'<figure class="wp-block-embed"><amp-facebook></amp-facebook><amp-facebook></amp-facebook></figure>',
				'<figure class="wp-block-embed"><amp-facebook></amp-facebook><amp-facebook></amp-facebook></figure>',
			),

			'no_wp_block_embed'    => array(
				'<figure><amp-facebook></amp-facebook></figure>',
				'<figure><amp-facebook></amp-facebook></figure>',
			),

			'data_amp_noloading'   => array(
				'<figure class="wp-block-embed" data-amp-noloading="true"><amp-facebook></amp-facebook></figure>',
				'<figure class="wp-block-embed" data-amp-noloading="true"><amp-facebook noloading="" layout="fixed-height"></amp-facebook></figure>',
			),

			'data_amp_layout_fill' => array(
				'<figure class="wp-block-embed" data-amp-layout="fill"><amp-facebook width="100"></amp-facebook></figure>',
				'<figure class="wp-block-embed" data-amp-layout="fill" style="position:relative; width: 100%; height: 400px;"><amp-facebook layout="fill"></amp-facebook></figure>',
			),
		);
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
