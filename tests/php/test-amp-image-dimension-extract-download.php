<?php
/**
 * Tests for AMP_Image_Dimension_Extractor.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Image_Dimension_Extractor.
 *
 * @covers AMP_Image_Dimension_Extractor
 */
class AMP_Image_Dimension_Extract_Download_Test extends WP_UnitTestCase {

	const AMP_IMG_DIMENSION_TEST_INVALID_FILE = __DIR__ . '/assets/not-exists.png';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		/*
		 * Start PHP built-in server to host images to test.
		 * Not ideal to use remote URLs; mocking would be better for performance, but FasterImage doesn't provide means to do this.
		 */
		$host      = self::get_host();
		$host_root = self::get_host_root();

		exec( "php -S {$host} -t {$host_root} >/dev/null 2>/dev/null &" ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
	}

	private static function get_host() {
		return getenv( 'AMP_TEST_HOST_URL' ) ?: '127.0.0.1:8891';
	}

	private static function get_host_root() {
		return getenv( 'AMP_TEST_HOST_ROOT' ) ?: __DIR__ . '/data/images';
	}

	private static function get_image_url( $file ) {
		return self::get_host() . "/{$file}";
	}

	/**
	 * Test a valid image file.
	 *
	 * @todo: tests for transients, errors, lock
	 * @todo: mocked tests
	 */
	public function test__valid_image_file() {
		$sources  = [
			self::get_image_url( '350x150.png' ) => false,
		];
		$expected = [
			self::get_image_url( '350x150.png' ) => [
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
			self::get_image_url( '350x150.png' )  => false,
			self::get_image_url( '1024x768.png' ) => false,
			self::get_image_url( 'amp.svg' )      => false,
			self::get_image_url( 'google.svg' )   => false,
		];
		$expected = [
			self::get_image_url( '350x150.png' )  => [
				'width'  => 350,
				'height' => 150,
			],
			self::get_image_url( '1024x768.png' ) => [
				'width'  => 1024,
				'height' => 768,
			],
			self::get_image_url( 'amp.svg' )      => [
				'width'  => 175,
				'height' => 60,
			],
			self::get_image_url( 'google.svg' )   => [
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
			self::AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		];
		$expected = [
			self::AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		];

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test a mix of valid and invalid image files.
	 */
	public function test__mix_of_valid_and_invalid_image_file() {
		$sources  = [
			self::get_image_url( '350x150.png' )      => false,
			self::get_image_url( '1024x768.png' )     => false,
			self::AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		];
		$expected = [
			self::get_image_url( '350x150.png' )      => [
				'width'  => 350,
				'height' => 150,
			],
			self::get_image_url( '1024x768.png' )     => [
				'width'  => 1024,
				'height' => 768,
			],
			self::AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		];

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}
}
