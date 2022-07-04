<?php
/**
 * Test cases for AMP_Native_Img_Attributes_Sanitizer
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\Tests\TestCase;

/**
 * Class AMP_Native_Img_Attributes_Sanitizer_Test
 *
 * @since 2.3.1
 * @coversDefaultClass AMP_Native_Img_Attributes_Sanitizer
 */
class AMP_Native_Img_Attributes_Sanitizer_Test extends TestCase {

	/**
	 * Test an native img tag has not layout or object-fit attributes.
	 *
	 * `layout` and `object-fit` will be replaced with a style attribute.
	 *
	 * @covers \AMP_Native_Img_Attributes_Sanitizer::sanitize()
	 */
	public function test_native_img_tag_has_not_layout_or_object_fit_attrs() {
		$source   = '<amp-carousel width="600" height="400" type="slides" layout="responsive" lightbox=""><figure class="slide"><img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></figure></amp-carousel>';
		$expected = '<amp-carousel width="600" height="400" type="slides" layout="responsive" lightbox=""><figure class="slide"><img src="http://example.com/img.png" width="600" height="400" style="position:absolute; left:0; right:0; top:0; bottom: 0; width:100%; height:100%; object-fit:cover;"></figure></amp-carousel>';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Native_Img_Attributes_Sanitizer(
			$dom,
			[
				'native_img_used'   => true,
				'carousel_required' => true,
			]
		);
		$sanitizer->sanitize();

		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}
}
