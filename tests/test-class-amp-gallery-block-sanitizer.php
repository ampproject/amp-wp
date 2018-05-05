<?php
/**
 * Class AMP_Gallery_Block_Sanitizer_Test.
 *
 * @package AMP
 */

/**
 * Class AMP_Gallery_Block_Sanitizer_Test
 */
class AMP_Gallery_Block_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Get data.
	 *
	 * @return array
	 */
	public function get_data() {
		return array(
			'no_ul'                 => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'no_a'                  => array(
				'<ul class="amp-carousel"><amp-img></amp-img></ul>',
				'<ul class="amp-carousel"><amp-img></amp-img></ul>',
			),

			'no_amp_carousel'       => array(
				'<ul><a><amp-img></amp-img></a></ul>',
				'<ul><a><amp-img></amp-img></a></ul>',
			),

			'data_amp_with_gallery' => array(
				'<ul class="amp-carousel"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="600" height="480" type="slides" layout="responsive"><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></amp-carousel>',
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
		$sanitizer = new AMP_Gallery_Block_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );
	}
}
