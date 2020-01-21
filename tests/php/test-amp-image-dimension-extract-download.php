<?php
/**
 * Tests for AMP_Image_Dimension_Extractor.
 *
 * @package AMP
 */

define( 'AMP_IMG_DIMENSION_TEST_INVALID_FILE', __DIR__ . '/assets/not-exists.png' );

// Not ideal to use remote URLs; mocking would be better for performance, but FasterImage doesn't provide means to do this.
define( 'IMG_350', 'http://amp-wp.org/wp-content/plugin-test-files/350x150.png' );
define( 'IMG_1024', 'http://amp-wp.org/wp-content/plugin-test-files/1024x768.png' );
define( 'IMG_SVG', 'https://amp-wp.org/wp-content/plugin-test-files/amp.svg' ); // @todo For some reason, FasterImage times out on this if the XML PI is absent.
define( 'IMG_SVG_VIEWPORT', 'https://gist.githubusercontent.com/kienstra/7aef6fcd42067174fcdc78f5f6110197/raw/a7875528d90db3a427823727ef3ecd4cfe00880e/google.svg' );

/**
 * Tests for AMP_Image_Dimension_Extractor.
 *
 * @covers AMP_Image_Dimension_Extractor
 */
class AMP_Image_Dimension_Extract_Download_Test extends WP_UnitTestCase {

	/**
	 * Test a valid image file.
	 *
	 * @todo: tests for transients, errors, lock
	 * @todo: mocked tests
	 */
	public function test__valid_image_file() {
		$sources  = [
			IMG_350 => false,
		];
		$expected = [
			IMG_350 => [
				'width'  => 350,
				'height' => 150,
			],
		];

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test multiple valid image files.
	 */
	public function test__multiple_valid_image_files() {
		$sources  = [
			IMG_350          => false,
			IMG_1024         => false,
			IMG_SVG          => false,
			IMG_SVG_VIEWPORT => false,
		];
		$expected = [
			IMG_350          => [
				'width'  => 350,
				'height' => 150,
			],
			IMG_1024         => [
				'width'  => 1024,
				'height' => 768,
			],
			IMG_SVG          => [
				'width'  => 175,
				'height' => 60,
			],
			IMG_SVG_VIEWPORT => [
				'width'  => 251,
				'height' => 80,
			],
		];

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test an invalid image file.
	 */
	public function test__invalid_image_file() {
		$sources  = [
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		];
		$expected = [
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		];

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test a mix of valid and invalid image files.
	 */
	public function test__mix_of_valid_and_invalid_image_file() {
		$sources  = [
			IMG_350                             => false,
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
			IMG_1024                            => false,
		];
		$expected = [
			IMG_350                             => [
				'width'  => 350,
				'height' => 150,
			],
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
			IMG_1024                            => [
				'width'  => 1024,
				'height' => 768,
			],
		];

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}
}
