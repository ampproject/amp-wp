<?php
/**
 * Tests for ElementList and CaptionedSlide classes.
 *
 * @package AMP
 */

use Amp\Dom\Document;
use Amp\AmpWP\Dom\ElementList;
use Amp\AmpWP\Component\CaptionedSlide;

/**
 * Tests for AMP carousel and slide classes.
 *
 * @covers Amp\AmpWP\Dom\ElementList, Amp\AmpWP\Component\CaptionedSlide
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
	 * @covers Amp\AmpWP\Dom\ElementList::add()
	 * @covers Amp\AmpWP\Dom\ElementList::count()
	 *
	 * @param DOMElement[] $images         The images to add.
	 * @param string       $expected_count The expected count after adding the images.
	 */
	public function test_dom_element_list_add( $images, $expected_count ) {
		$dom_element_list = new ElementList();
		foreach ( $images as $image ) {
			$dom_element_list = $dom_element_list->add( $image, '' );
		}

		$this->assertEquals( $expected_count, $dom_element_list->count() );
	}

	/**
	 * Test the iteration of the images.
	 *
	 * @dataProvider get_dom_element_list_data
	 * @covers Amp\AmpWP\Dom\ElementList::add()
	 * @covers Amp\AmpWP\Dom\ElementList::getIterator()
	 *
	 * @param DOMElement[] $images         The images to add.
	 * @param string       $expected_count The expected count after adding the images.
	 */
	public function test_dom_element_list_get_iterator( $images, $expected_count ) {
		$dom_element_list = new ElementList();
		foreach ( $images as $image ) {
			$dom_element_list = $dom_element_list->add( $image, '' );
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
	 * @covers \Amp\AmpWP\Component\CaptionedSlide::get_caption()
	 */
	public function test_get_caption() {
		$image_node      = AMP_DOM_Utils::create_node( new Document(), 'amp-img', [] );
		$caption         = 'This is a caption';
		$captioned_image = new CaptionedSlide( $image_node, $caption );
		$this->assertEquals( $caption, $captioned_image->get_caption() );
	}

	/**
	 * Test get_slide_node.
	 *
	 * @covers \Amp\AmpWP\Component\CaptionedSlide::get_slide_node()
	 */
	public function test_get_slide_node() {
		$image_node = AMP_DOM_Utils::create_node( new Document(), 'amp-img', [] );
		$amp_image  = new CaptionedSlide( $image_node, '' );
		$this->assertEquals( $image_node, $amp_image->get_slide_node() );
	}
}
