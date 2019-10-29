<?php
/**
 * Tests for AMP_Carousel class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Carousel class.
 *
 * @covers AMP_Carousel
 */
class Test_AMP_Carousel extends WP_UnitTestCase {

	/**
	 * Gets the data to test the carousel.
	 *
	 * @return array[] An associative array, including the images and captions, the DOM, and the expected markup.
	 */
	public function get_carousel_data() {
		$dom     = new DOMDocument();
		$src     = 'https://example.com/img.png';
		$width   = '1200';
		$height  = '800';
		$image   = AMP_DOM_Utils::create_node(
			$dom,
			'amp-img',
			compact( 'src', 'width', 'height' )
		);
		$caption = 'Example caption';

		return [
			'image_without_caption' => [
				[ [ $image, null ] ],
				$dom,
				'<amp-carousel width="' . $width . '" height="' . $height . '" type="slides" layout="responsive"><div class="slide"><amp-img src="' . $src . '" width="' . $width . '" height="' . $height . '" layout="fill" object-fit="cover"></amp-img></div></amp-carousel>',
			],
			'image_with_caption'    => [
				[ [ $image, $caption ] ],
				$dom,
				'<amp-carousel width="' . $width . '" height="' . $height . '" type="slides" layout="responsive"><div class="slide"><amp-img src="' . $src . '" width="' . $width . '" height="' . $height . '" layout="fill" object-fit="cover"></amp-img><div class="amp-wp-gallery-caption"><span>' . $caption . '</span></div></div></amp-carousel>',
			],
		];
	}

	/**
	 * Test getting the amp-carousel.
	 *
	 * @dataProvider get_carousel_data
	 * @covers \AMP_Carousel::create_and_get()
	 *
	 * @param array[]     $images_and_captions An array of arrays, with images and their captions (if any).
	 * @param DOMDocument $dom The representation of the DOM.
	 * @param string      $expected The expected return value of the tested function.
	 */
	public function test_create_and_get( $images_and_captions, $dom, $expected ) {
		$amp_carousel        = new AMP_Carousel( $dom );
		$actual_amp_carousel = $amp_carousel->create_and_get( $images_and_captions );

		// Prevent an error in get_content_from_dom_node().
		$dom->appendChild( $actual_amp_carousel );

		$this->assertEquals(
			$expected,
			AMP_DOM_Utils::get_content_from_dom_node( $dom, $actual_amp_carousel )
		);
	}

	/**
	 * Gets the testing data for test_get_dimensions.
	 *
	 * @return array[] An associative array, including the $images argument and the expected return value.
	 */
	public function get_data_carousel_dimensions() {
		$dom                 = new DOMDocument();
		$narrow_image_width  = '300';
		$narrow_image_height = '600';
		$narrow_image        = AMP_DOM_Utils::create_node(
			$dom,
			'amp-img',
			[
				'width'  => $narrow_image_width,
				'height' => $narrow_image_height,
			]
		);

		$wide_image_width  = 1400;
		$wide_image_height = 1000;
		$wide_image        = AMP_DOM_Utils::create_node(
			$dom,
			'amp-img',
			[
				'width'  => $wide_image_width,
				'height' => $wide_image_height,
			]
		);

		return [
			'empty_array_passed_as_argument'               => [
				[],
				[ AMP_Carousel::FALLBACK_WIDTH, AMP_Carousel::FALLBACK_HEIGHT ],
			],
			'null_passed_as_argument'                      => [
				null,
				[ AMP_Carousel::FALLBACK_WIDTH, AMP_Carousel::FALLBACK_HEIGHT ],
			],
			'single_small_image_passed_as_argument'        => [
				[ $narrow_image ],
				[ $narrow_image_width, $narrow_image_height ],
			],
			'single_large_image_passed_as_argument'        => [
				[ $wide_image ],
				[ $wide_image_width, $wide_image_height ],
			],
			'two_images_passed_as_arguments'               => [
				[ $narrow_image, $wide_image ],
				[ $wide_image_width, $wide_image_height ],
			],
			'two_images_passed_as_arguments_order_changed' => [
				[ $wide_image, $narrow_image ],
				[ $wide_image_width, $wide_image_height ],
			],
		];
	}

	/**
	 * Test get_dimensions.
	 *
	 * @dataProvider get_data_carousel_dimensions
	 * @covers \AMP_Carousel::get_dimensions()
	 *
	 * @param DOMElement[] $images The images to get the dimensions from.
	 * @param array $expected The expected return value of the tested function.
	 */
	public function test_get_dimensions( $images, $expected ) {
		$amp_carousel = new AMP_Carousel( new DOMDocument() );
		$this->assertEquals(
			$expected,
			$amp_carousel->get_dimensions( $images )
		);
	}
}
