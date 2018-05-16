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
			'no_ul'                               => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'no_a_no_amp_img'                     => array(
				'<ul class="amp-carousel"><div></div></ul>',
				'<ul class="amp-carousel"><div></div></ul>',
			),

			'no_amp_carousel'                     => array(
				'<ul><a><amp-img></amp-img></a></ul>',
				'<ul><a><amp-img></amp-img></a></ul>',
			),

			'data_amp_with_carousel'              => array(
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<amp-carousel height="400" type="slides" layout="fixed-height"><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></amp-carousel>',
			),

			'data_amp_with_lightbox'              => array(
				'<ul data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<ul data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400" data-amp-lightbox="" on="tap:amp-image-lightbox-1" role="button"></amp-img></a></figure></li></ul><amp-image-lightbox id="amp-image-lightbox-1" layout="nodisplay" data-close-button-aria-label="Close"></amp-image-lightbox>',
			),

			'data_amp_with_lightbox_and_carousel' => array(
				'<ul data-amp-lightbox="true" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<amp-carousel height="400" type="slides" layout="fixed-height"><amp-img src="http://example.com/img.png" width="600" height="400" data-amp-lightbox="" on="tap:amp-image-lightbox-1" role="button"></amp-img></amp-carousel><amp-image-lightbox id="amp-image-lightbox-1" layout="nodisplay" data-close-button-aria-label="Close"></amp-image-lightbox>',
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
