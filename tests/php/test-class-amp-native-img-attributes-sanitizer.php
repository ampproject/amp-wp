<?php
/**
 * Test cases for AMP_Native_Img_Attributes_Sanitizer
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;

/**
 * Class AMP_Native_Img_Attributes_Sanitizer_Test
 *
 * @since 2.3.1
 * @coversDefaultClass AMP_Native_Img_Attributes_Sanitizer
 */
class AMP_Native_Img_Attributes_Sanitizer_Test extends TestCase {

	use MarkupComparison;

	public function get_data_to_test_sanitize() {
		$amp_carousel_source    = '<amp-carousel width="600" height="400" type="slides" layout="responsive" lightbox=""><figure class="slide"><img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></figure></amp-carousel>';
		$amp_carousel_sanitized = '<amp-carousel width="600" height="400" type="slides" layout="responsive" lightbox=""><figure class="slide"><img src="http://example.com/img.png" width="600" height="400" style="position:absolute;left:0;right:0;top:0;bottom:0;width:100%;height:100%;object-fit:cover"></figure></amp-carousel>';

		return [
			'disabled'                    => [
				false,
				$amp_carousel_source,
				null, // Same as above.
			],
			'carousel_with_img'           => [
				true,
				$amp_carousel_source,
				$amp_carousel_sanitized,
			],
			'amp_img'                     => [
				true,
				'<amp-img src="https://example.com/img.png" style="border: solid 1px red;" layout="fill" object-fit="cover"></amp-img>',
				null, // Same as above.
			],
			'img_with_layout_fill'        => [
				true,
				'<img src="https://example.com/img.png" style="border: solid 1px red" layout="fill">',
				'<img src="https://example.com/img.png" style="border: solid 1px red;position:absolute;left:0;right:0;top:0;bottom:0;width:100%;height:100%">',
			],
			'img_with_layout_nodisplay'   => [
				true,
				'<img src="https://example.com/img.png" style="border: solid 1px red;" layout="nodisplay">',
				null, // Same as above.
			],
			'img_with_object_fit_cover'   => [
				true,
				'<img src="https://example.com/img.png" style="border: solid 1px red;" object-fit="cover">',
				'<img src="https://example.com/img.png" style="border: solid 1px red;object-fit:cover">',
			],
			'img_with_object_fit_contain' => [
				true,
				'<img src="https://example.com/img.png" style="border: solid 1px red;" object-fit="contain">',
				'<img src="https://example.com/img.png" style="border: solid 1px red;object-fit:contain">',
			],
			'img_with_object_position'    => [
				true,
				'<img src="https://example.com/img.png" style="border: solid 1px red;" object-position="top">',
				'<img src="https://example.com/img.png" style="border: solid 1px red;object-position:top">',
			],
		];
	}

	/**
	 * Test an native img tag has not layout or object-fit attributes.
	 *
	 * `layout` and `object-fit` will be replaced with a style attribute.
	 *
	 * @dataProvider get_data_to_test_sanitize
	 * @covers ::sanitize()
	 */
	public function test_sanitize( $native_img_used, $source, $expected ) {
		if ( null === $expected ) {
			$expected = $source;
		}

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Native_Img_Attributes_Sanitizer(
			$dom,
			compact( 'native_img_used' )
		);
		$sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEqualMarkup( $expected, $content );
	}
}
