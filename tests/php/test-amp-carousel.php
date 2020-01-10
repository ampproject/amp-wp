<?php
/**
 * Tests for Carousel class.
 *
 * @package AMP
 */

use Amp\AmpWP\Component\Carousel;
use Amp\AmpWP\Dom\Document;
use Amp\AmpWP\Dom\ElementList;
use Amp\AmpWP\Tests\PrivateAccess;

/**
 * Tests for Carousel class.
 *
 * @covers \Amp\AmpWP\Component\Carousel
 */
class Test_Carousel extends \WP_UnitTestCase {

	use PrivateAccess;

	/**
	 * Gets the data to test the carousel.
	 *
	 * @return array[] An associative array, including the slides and captions, the DOM, and the expected markup.
	 */
	public function get_carousel_data() {
		$dom     = new Document();
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
				( new ElementList() )->add( $image, '' ),
				$dom,
				'<amp-carousel width="' . $width . '" height="' . $height . '" type="slides" layout="responsive"><span class="slide"><amp-img src="' . $src . '" width="' . $width . '" height="' . $height . '" layout="fill" object-fit="cover"></amp-img></span></amp-carousel>',
			],
			'image_with_caption'    => [
				( new ElementList() )->add( $image, $caption ),
				$dom,
				'<amp-carousel width="' . $width . '" height="' . $height . '" type="slides" layout="responsive"><span class="slide"><amp-img src="' . $src . '" width="' . $width . '" height="' . $height . '" layout="fill" object-fit="cover"></amp-img><span class="amp-wp-gallery-caption"><span>' . $caption . '</span></span></span></amp-carousel>',
			],
		];
	}

	/**
	 * Test getting the amp-carousel.
	 *
	 * @dataProvider get_carousel_data
	 * @covers \Amp\AmpWP\Component\Carousel::get_dom_element()
	 *
	 * @param ElementList $slides   An array of arrays, with images and their captions (if any).
	 * @param Document    $dom      The representation of the DOM.
	 * @param string      $expected The expected return value of the tested function.
	 */
	public function test_get_dom_element( $slides, $dom, $expected ) {
		$amp_carousel        = new Carousel( $dom, $slides );
		$actual_amp_carousel = $amp_carousel->get_dom_element();

		// Prevent an error in get_content_from_dom_node().
		$dom->body->appendChild( $actual_amp_carousel );

		$this->assertEquals(
			$expected,
			$dom->saveHTML( $actual_amp_carousel )
		);
	}

	/**
	 * Gets the testing data for test_get_dimensions.
	 *
	 * @return array[] An associative array, including the ElementList and the expected return value.
	 */
	public function get_data_carousel_dimensions() {
		$dom                 = new Document();
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
				( new ElementList() ),
				[ Carousel::FALLBACK_WIDTH, Carousel::FALLBACK_HEIGHT ],
			],
			'element_no_width_or_height'                  => [
				( new ElementList() )->add( AMP_DOM_Utils::create_node( $dom, 'div', [] ) ),
				[ Carousel::FALLBACK_WIDTH, Carousel::FALLBACK_HEIGHT ],
			],
			'single_small_image_passed_as_argument'       => [
				( new ElementList() )->add( $narrow_image ),
				[ $narrow_image_width, $narrow_image_height ],
			],
			'single_large_image_passed_as_argument'       => [
				( new ElementList() )->add( $wide_image ),
				[ $wide_image_width, $wide_image_height ],
			],
			'image_with_0_height_should_not_affect_ratio' => [
				( new ElementList() )->add( $image_with_0_height )->add( $wide_image, '' ),
				[ $wide_image_width, $wide_image_height ],
			],
			'two_images'                                  => [
				( new ElementList() )->add( $narrow_image )->add( $wide_image, '' ),
				[ $wide_image_width, $wide_image_height ],
			],
			'two_images_order_changed'                    => [
				( new ElementList() )->add( $wide_image )->add( $narrow_image, '' ),
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
	 * @param ElementList $slides   The slides to get the dimensions from.
	 * @param array       $expected The expected return value of the tested function.
	 * @throws ReflectionException If invoking the method reflection fails.
	 */
	public function test_get_dimensions( $slides, $expected ) {
		$carousel = new Carousel( new Document(), $slides );

		$this->assertEquals(
			$expected,
			$this->call_private_method( $carousel, 'get_dimensions' )
		);
	}
}
