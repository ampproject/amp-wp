<?php
/**
 * Tests for ElementList and CaptionedSlide classes.
 *
 * @package AMP
 */

use AmpProject\Dom\Document;
use AmpProject\AmpWP\Dom\ElementList;
use AmpProject\AmpWP\Component\CaptionedSlide;

/**
 * Tests for AMP carousel and slide classes.
 *
 * @covers AmpProject\AmpWP\Dom\ElementList
 * @covers AmpProject\AmpWP\Component\CaptionedSlide
 */
class Test_DOM_Element_List extends \WP_UnitTestCase {

	/**
	 * Gets the data to test adding images.
	 *
	 * @return array[] An associative array, including the images and the expected count.
	 */
	public function get_dom_element_list_data() {
		$dom = new Document();
		return [
			'no_image' => [
				[],
				0,
			],
			'1_image'  => [
				[ AMP_DOM_Utils::create_node( $dom, 'amp-img', [] ) ],
				1,
			],
			'4_images' => [
				[
					AMP_DOM_Utils::create_node( $dom, 'amp-img', [] ),
					AMP_DOM_Utils::create_node( $dom, 'amp-img', [] ),
					AMP_DOM_Utils::create_node( $dom, 'amp-img', [] ),
					AMP_DOM_Utils::create_node( $dom, 'amp-img', [] ),
				],
				4,
			],
		];
	}

	/**
	 * Test adding images and counting them.
	 *
	 * @dataProvider get_dom_element_list_data
	 * @covers AmpProject\AmpWP\Dom\ElementList::add()
	 * @covers AmpProject\AmpWP\Dom\ElementList::count()
	 *
	 * @param DOMElement[] $images         The images to add.
	 * @param string       $expected_count The expected count after adding the images.
	 */
	public function test_dom_element_list_add( $images, $expected_count ) {
		$dom_element_list = new ElementList();
		foreach ( $images as $image ) {
			$dom_element_list = $dom_element_list->add( $image, null );
		}

		$this->assertEquals( $expected_count, $dom_element_list->count() );
	}

	/**
	 * Test the iteration of the images.
	 *
	 * @dataProvider get_dom_element_list_data
	 * @covers AmpProject\AmpWP\Dom\ElementList::add()
	 * @covers AmpProject\AmpWP\Dom\ElementList::getIterator()
	 *
	 * @param DOMElement[] $images         The images to add.
	 * @param string       $expected_count The expected count after adding the images.
	 */
	public function test_dom_element_list_get_iterator( $images, $expected_count ) {
		$dom_element_list = new ElementList();
		foreach ( $images as $image ) {
			$dom_element_list = $dom_element_list->add( $image );
		}

		$iteration_count = 0;
		foreach ( $dom_element_list as $dom_element_list_image ) {
			unset( $dom_element_list_image );
			$iteration_count++;
		}

		$this->assertEquals( $expected_count, $iteration_count );
	}

	/**
	 * Test get_caption.
	 *
	 * @covers \AmpProject\AmpWP\Component\CaptionedSlide::get_caption_element()
	 */
	public function test_get_caption() {
		$dom = new Document();

		$image_node      = AMP_DOM_Utils::create_node( $dom, 'amp-img', [] );
		$caption_element = AMP_DOM_Utils::create_node( $dom, 'span', [] );
		$caption_element->appendChild( new DOMText( 'This is a caption' ) );

		$captioned_image = new CaptionedSlide( $image_node, $caption_element );
		$this->assertEquals( $caption_element, $captioned_image->get_caption_element() );
	}

	/**
	 * Test get_slide_element.
	 *
	 * @covers \AmpProject\AmpWP\Component\CaptionedSlide::get_slide_element()
	 */
	public function test_get_slide_element() {
		$image_node = AMP_DOM_Utils::create_node( new Document(), 'amp-img', [] );
		$amp_image  = new CaptionedSlide( $image_node, new DOMElement( 'foo' ) );
		$this->assertEquals( $image_node, $amp_image->get_slide_element() );
	}
}
