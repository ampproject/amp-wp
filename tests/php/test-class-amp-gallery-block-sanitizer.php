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
		return [
			'no_ul'                               => [
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			],

			'no_a_no_amp_img'                     => [
				'<ul class="amp-carousel"><div></div></ul>',
				'<ul class="amp-carousel"><div></div></ul>',
			],

			'no_amp_carousel'                     => [
				'<ul><a><amp-img></amp-img></a></ul>',
				'<ul><a><amp-img></amp-img></a></ul>',
			],

			'no_block_class'                      => [
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
			],

			'data_amp_with_carousel'              => [
				'<ul class="wp-block-gallery" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive"><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></amp-carousel>',
			],

			'data_amp_with_lightbox'              => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400" data-amp-lightbox="" on="tap:amp-image-lightbox" role="button" tabindex="0"></amp-img></a></figure></li></ul><amp-image-lightbox id="amp-image-lightbox" layout="nodisplay" data-close-button-aria-label="Close"></amp-image-lightbox>',
			],

			'data_amp_with_lightbox_and_carousel' => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="1234" height="567"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="1234" height="567" type="slides" layout="responsive"><amp-img src="http://example.com/img.png" width="1234" height="567" data-amp-lightbox="" on="tap:amp-image-lightbox" role="button" tabindex="0"></amp-img></amp-carousel><amp-image-lightbox id="amp-image-lightbox" layout="nodisplay" data-close-button-aria-label="Close"></amp-image-lightbox>',
			],
		];
	}

	/**
	 * Test sanitizer.
	 *
	 * This only tests when theme support is present.
	 * Like if Standard or Transitional is selected in AMP Settings > Template Mode,
	 * or if this is added with add_theme_support( 'amp' ).
	 * If there is no theme support, the sanitizer will have the argument array( 'carousel_required' => true ).
	 *
	 * @see amp_get_content_sanitizers()
	 * @dataProvider get_data
	 * @param string $source Source.
	 * @param string $expected Expected.
	 */
	public function test_sanitizer( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Gallery_Block_Sanitizer(
			$dom,
			[ 'content_max_width' => 600 ]
		);
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Get the Reader mode data.
	 *
	 * @return array
	 */
	public function get_reader_mode_data() {
		return [
			'no_block_class'                      => [
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
			],

			'data_amp_with_lightbox'              => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive"><amp-img src="http://example.com/img.png" width="600" height="400" data-amp-lightbox="" on="tap:amp-image-lightbox" role="button" tabindex="0"></amp-img></amp-carousel><amp-image-lightbox id="amp-image-lightbox" layout="nodisplay" data-close-button-aria-label="Close"></amp-image-lightbox>',
			],

			'data_amp_with_lightbox_and_carousel' => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive"><amp-img src="http://example.com/img.png" width="600" height="400" data-amp-lightbox="" on="tap:amp-image-lightbox" role="button" tabindex="0"></amp-img></amp-carousel><amp-image-lightbox id="amp-image-lightbox" layout="nodisplay" data-close-button-aria-label="Close"></amp-image-lightbox>',
			],
		];
	}

	/**
	 * Test the sanitizer in Reader mode (without theme support).
	 *
	 * The tested sanitizer will have an argument of array( 'carousel_required' => true ),
	 * which sometimes causes different output.
	 *
	 * @see amp_get_content_sanitizers()
	 * @dataProvider get_reader_mode_data
	 * @param string $source Source.
	 * @param string $expected Expected.
	 */
	public function test_sanitizer_reader_mode( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Gallery_Block_Sanitizer(
			$dom,
			[ 'carousel_required' => true ]
		);
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );
	}
}
