<?php
/**
 * Tests for Carousel class.
 *
 * @package AMP
 */

use Amp\AmpWP\Component\Carousel;
use Amp\AmpWP\Component\DOMElementList;

/**
 * Tests for Carousel class.
 *
 * @covers \Amp\AmpWP\Component\Carousel
 */
class Test_Carousel extends \WP_UnitTestCase {

	/**
	 * Gets the data to test the carousel.
	 *
	 * @return array[] An associative array, including the slides and captions, the DOM, and the expected markup.
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
				( new DOMElementList() )->add( $image, '' ),
				$dom,
				'<amp-carousel width="' . $width . '" height="' . $height . '" type="slides" layout="responsive"><div class="slide"><amp-img src="' . $src . '" width="' . $width . '" height="' . $height . '" layout="fill" object-fit="cover"></amp-img></div></amp-carousel>',
			],
			'image_with_caption'    => [
				( new DOMElementList() )->add( $image, $caption ),
				$dom,
				'<amp-carousel width="' . $width . '" height="' . $height . '" type="slides" layout="responsive"><div class="slide"><amp-img src="' . $src . '" width="' . $width . '" height="' . $height . '" layout="fill" object-fit="cover"></amp-img><div class="amp-wp-gallery-caption"><span>' . $caption . '</span></div></div></amp-carousel>',
			],
		];
	}

	/**
	 * Test getting the amp-carousel.
	 *
	 * @dataProvider get_carousel_data
	 * @covers \Amp\AmpWP\Component\Carousel::get_dom_element()
	 *
	 * @param DOMElementList $slides   An array of arrays, with images and their captions (if any).
	 * @param DOMDocument    $dom      The representation of the DOM.
	 * @param string         $expected The expected return value of the tested function.
	 */
	public function test_get_dom_element( $slides, $dom, $expected ) {
		$amp_carousel        = new Carousel( $dom, $slides );
		$actual_amp_carousel = $amp_carousel->get_dom_element();

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
	 * @return array[] An associative array, including the DOMElementList and the expected return value.
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

		$image_with_0_height = AMP_DOM_Utils::create_node(
			$dom,
			'amp-img',
			[
				'width'  => 1000,
				'height' => 0,
			]
		);

		return [
			'empty_dom_element_list_as_argument'          => [
				( new DOMElementList() ),
				[ Carousel::FALLBACK_WIDTH, Carousel::FALLBACK_HEIGHT ],
			],
			'element_no_width_or_height'                  => [
				( new DOMElementList() )->add( AMP_DOM_Utils::create_node( $dom, 'div', [] ) ),
				[ Carousel::FALLBACK_WIDTH, Carousel::FALLBACK_HEIGHT ],
			],
			'single_small_image_passed_as_argument'       => [
				( new DOMElementList() )->add( $narrow_image ),
				[ $narrow_image_width, $narrow_image_height ],
			],
			'single_large_image_passed_as_argument'       => [
				( new DOMElementList() )->add( $wide_image ),
				[ $wide_image_width, $wide_image_height ],
			],
			'image_with_0_height_should_not_affect_ratio' => [
				( new DOMElementList() )->add( $image_with_0_height )->add( $wide_image, '' ),
				[ $wide_image_width, $wide_image_height ],
			],
			'two_images'                                  => [
				( new DOMElementList() )->add( $narrow_image )->add( $wide_image, '' ),
				[ $wide_image_width, $wide_image_height ],
			],
			'two_images_order_changed'                    => [
				( new DOMElementList() )->add( $wide_image )->add( $narrow_image, '' ),
				[ $wide_image_width, $wide_image_height ],
			],
		];
	}

	/**
	 * Test get_dimensions.
	 *
	 * @dataProvider get_data_carousel_dimensions
	 * @covers \Amp\AmpWP\Component\Carousel::get_dimensions()
	 *
	 * @param DOMElementList $slides   The slides to get the dimensions from.
	 * @param array          $expected The expected return value of the tested function.
	 */
	public function test_get_dimensions( $slides, $expected ) {
		$amp_carousel = new Carousel( new DOMDocument(), $slides );
		$this->assertEquals(
			$expected,
			$amp_carousel->get_dimensions()
		);
	}
}
