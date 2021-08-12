<?php
/**
 * Class AMP_Srcset_Sanitizer_Test.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Class AMP_Srcset_Sanitizer_Test
 *
 * @coversDefaultClass AMP_Srcset_Sanitizer
 */
class AMP_Srcset_Sanitizer_Test extends TestCase {

	use MarkupComparison;

	/**
	 * Provide the data to test the sanitize() method.
	 *
	 * @return array[] Test data.
	 */
	public function data_sanitize() {
		return [
			'img_with_valid_srcset'                     => [
				'<img src="https://example.com/image.jpg" srcset="https://example.com/image.jpg, https://example.com/image-1.jpg     512w, https://example.com/image-2.jpg 1024w   , https://example.com/image-3.jpg 300w, https://example.com/image-4.jpg 768w" width="350" height="150">',
				null,
			],

			'img_with_duplicate_img_candidate_but_same_url' => [
				'<img src="https://example.com/image.jpg" srcset="https://example.com/image.jpg, https://example.com/image-1.jpg     1024w, https://example.com/image-1.jpg 1024w   , https://example.com/image-2.jpg 300w, https://example.com/image-3.jpg 768w" width="350" height="150">',
				'<img src="https://example.com/image.jpg" srcset="https://example.com/image.jpg 1x, https://example.com/image-1.jpg 1024w, https://example.com/image-2.jpg 300w, https://example.com/image-3.jpg 768w" width="350" height="150">',
			],

			'img_with_duplicate_img_candidate_but_different_url' => [
				'<img src="https://example.com/image.jpg" srcset="https://example.com/image.jpg, https://example.com/image-1.jpg     1024w, https://example.com/image-2.jpg 1024w   , https://example.com/image-2.jpg 300w, https://example.com/image-3.jpg 768w" width="350" height="150">',
				'<img src="https://example.com/image.jpg" srcset="https://example.com/image.jpg 1x, https://example.com/image-1.jpg 1024w, https://example.com/image-2.jpg 300w, https://example.com/image-3.jpg 768w" width="350" height="150">',
				[ AMP_Tag_And_Attribute_Sanitizer::DUPLICATE_DIMENSIONS ],
			],

			'amp_img_srcset_missing_comma'              => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="https://example.com/image-1.jpg 1024w https://example.com/image-2.jpg 1024w">',
				'<img src="https://example.com/image.jpg" height="100" width="200">',
				[ AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE ],
			],

			'amp_img_srcset_empty'                      => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="">',
				null,
			],

			'amp_img_srcset_whitespace'                 => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="    ">',
				'<img src="https://example.com/image.jpg" height="100" width="200">',
				[ AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE ],
			],

			'amp_img_srcset_invalid_bare_number'        => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="1">',
				'<img src="https://example.com/image.jpg" height="100" width="200">',
				[ AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE ],
			],

			'amp_img_srcset_invalid_dimension_unit'     => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="https://example.com/image.jpg 500px">',
				'<img src="https://example.com/image.jpg" height="100" width="200">',
				[ AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE ],
			],

			'amp_img_srcset_invalid_space_in_dimension' => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="https://example.com/image.jpg 2 x">',
				'<img src="https://example.com/image.jpg" height="100" width="200">',
				[ AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE ],
			],

			'amp_img_srcset_invalid_dimension_type'     => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="https://example.com/image.jpg 5kw">',
				'<img src="https://example.com/image.jpg" height="100" width="200">',
				[ AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE ],
			],

			'amp_img_srcset_invalid_width_descriptor'   => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="https://example.com/image.jpg 5.2w">',
				'<img src="https://example.com/image.jpg" height="100" width="200">',
				[ AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE ],
			],

			'amp_img_srcset_invalid_zero_width'         => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="https://example.com/image.jpg 0w">',
				'<img src="https://example.com/image.jpg" height="100" width="200">',
				[ AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE ],
			],

			'amp_img_srcset_invalid_zero_pixel_density' => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="https://example.com/image.jpg 0.0x">',
				'<img src="https://example.com/image.jpg" height="100" width="200">',
				[ AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE ],
			],

			'amp_img_srcset_valid_pixel_density'        => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="https://example.com/image.jpg 5x">',
				null,
			],

			'amp_img_srcset_valid_float_pixel_density'  => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="https://example.com/image.jpg 5.2x">',
				null,
			],

			'amp_img_srcset_valid_float_pixel_density_with_leading_zero' => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="https://example.com/image.jpg 0.002x">',
				null,
			],

			'amp_img_srcset_invalid_tokens'             => [
				'<img src="https://example.com/image.jpg" height="100" width="200" srcset="bad bad">',
				'<img src="https://example.com/image.jpg" height="100" width="200">',
				[ AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE ],
			],
		];
	}

	/**
	 * Test the sanitize() method.
	 *
	 * @covers ::sanitize()
	 * @dataProvider data_sanitize()
	 *
	 * @param string      $source               Source.
	 * @param string|null $expected             Expected. If null, then no change from source.
	 * @param array       $expected_error_codes Expected error codes.
	 */
	public function test_sanitize( $source, $expected = null, $expected_error_codes = [] ) {
		$error_codes = [];
		if ( null === $expected ) {
			$expected = $source;
		}

		$args = [
			'use_document_element'      => true,
			'validation_error_callback' => static function( $error ) use ( &$error_codes ) {
				$error_codes[] = $error['code'];
			},
		];

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );

		$sanitizer = new AMP_Srcset_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$this->assertEqualSets( $error_codes, $expected_error_codes );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $content );
	}
}
