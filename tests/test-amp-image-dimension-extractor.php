<?php
/**
 * Tests for AMP_Image_Dimension_Extractor.
 *
 * @package AMP
 */

define( 'AMP_IMG_DIMENSION_TEST_INVALID_FILE', dirname( __FILE__ ) . '/assets/not-exists.png' );

// Not ideal to use remote URLs (since the remote service can change); mocking would be better.
define( 'IMG_350', 'http://i0.wp.com/amptest.files.wordpress.com/2017/03/350x150.png' );
define( 'IMG_1024', 'http://i0.wp.com/amptest.files.wordpress.com/2017/03/1024x768.png' );
define( 'IMG_SVG', 'https://gist.githubusercontent.com/westonruter/90fbaaced3851bf6ef762996c8c4375d/raw/fd58ec3fc426645885f6a3afa58ad64fbc70ea89/amp.svg' ); // @todo For some reason, FasterImage times out on this if the XML PI is absent.
define( 'IMG_SVG_VIEWPORT', 'https://gist.githubusercontent.com/westonruter/90fbaaced3851bf6ef762996c8c4375d/raw/fd58ec3fc426645885f6a3afa58ad64fbc70ea89/google.svg' );

/**
 * Tests for AMP_Image_Dimension_Extractor.
 *
 * @covers AMP_Image_Dimension_Extractor
 */
class AMP_Image_Dimension_Extractor_Extract_Test extends WP_UnitTestCase {
	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		// We don't want to actually download the images; just testing the extract method.
		add_action( 'amp_extract_image_dimensions_batch_callbacks_registered', array( $this, 'disable_downloads' ) );
	}

	/**
	 * Disable downloads.
	 */
	public function disable_downloads() {
		remove_all_filters( 'amp_extract_image_dimensions_batch' );
	}

	/**
	 * Test where processed URLs should match originals.
	 */
	public function test__should_return_original_urls() {
		$source_urls = array(
			'https://example.com',
			'//example.com/no-protocol',
			'/absolute-url/no-host',
			'data:image/gif;base64,R0lGODl...', // can't normalize.
		);
		$expected    = array(
			'https://example.com'              => false,
			'//example.com/no-protocol'        => false,
			'/absolute-url/no-host'            => false,
			'data:image/gif;base64,R0lGODl...' => false,
		);

		$actual = AMP_Image_Dimension_Extractor::extract( $source_urls );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Data strings for testing converter.
	 *
	 * @return array
	 */
	public function get_data() {
		$site_url = site_url();

		return array(
			'empty_url'         => array(
				'',
				false,
			),
			'data_url'          => array(
				'data:image/gif;base64,R0lGODl...',
				false,
			),
			'protocol-less_url' => array(
				'//example.com/file.jpg',
				'http://example.com/file.jpg',
			),
			'path_only'         => array(
				'/path/to/file.png',
				$site_url . '/path/to/file.png',
			),
			'query_only'        => array(
				'?file=file.png',
				$site_url . '/?file=file.png',
			),
			'path_and_query'    => array(
				'/path/file.jpg?query=1',
				$site_url . '/path/file.jpg?query=1',
			),
			'normal_url'        => array(
				'https://example.com/path/to/file.jpg',
				'https://example.com/path/to/file.jpg',
			),
		);
	}

	/**
	 * Test normalizing a URL
	 *
	 * @param string $source_url Source.
	 * @param string $expected_url Expected result.
	 *
	 * @dataProvider get_data
	 */
	public function test__normalize_url( $source_url, $expected_url ) {
		$result_url = AMP_Image_Dimension_Extractor::normalize_url( $source_url );

		$this->assertEquals( $expected_url, $result_url );
	}

	/**
	 * Test a valid image file.
	 *
	 * TODO: tests for transients, errors, lock
	 * TODO: mocked tests
	 */
	public function test__valid_image_file() {
		$sources  = array(
			IMG_350 => false,
		);
		$expected = array(
			IMG_350 => array(
				'width'  => 350,
				'height' => 150,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test a valid image file synchronously.
	 */
	public function test__valid_image_file_synchronous() {
		$sources  = array(
			IMG_350 => false,
		);
		$expected = array(
			IMG_350 => array(
				'width'  => 350,
				'height' => 150,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources, 'synchronous' );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test multiple valid image files.
	 */
	public function test__multiple_valid_image_files() {
		$sources  = array(
			IMG_350          => false,
			IMG_1024         => false,
			IMG_SVG          => false,
			IMG_SVG_VIEWPORT => false,
		);
		$expected = array(
			IMG_350          => array(
				'width'  => 350,
				'height' => 150,
			),
			IMG_1024         => array(
				'width'  => 1024,
				'height' => 768,
			),
			IMG_SVG          => array(
				'width'  => 175,
				'height' => 60,
			),
			IMG_SVG_VIEWPORT => array(
				'width'  => 251,
				'height' => 80,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test multiple valid image files synchronously.
	 */
	public function test__multiple_valid_image_files_synchronous() {
		$sources  = array(
			IMG_350          => false,
			IMG_1024         => false,
			IMG_SVG          => false,
			IMG_SVG_VIEWPORT => false,
		);
		$expected = array(
			IMG_350          => array(
				'width'  => 350,
				'height' => 150,
			),
			IMG_1024         => array(
				'width'  => 1024,
				'height' => 768,
			),
			IMG_SVG          => array(
				'width'  => 175,
				'height' => 60,
			),
			IMG_SVG_VIEWPORT => array(
				'width'  => 251,
				'height' => 80,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources, 'synchronous' );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test an invalid image file.
	 */
	public function test__invalid_image_file() {
		$sources  = array(
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		);
		$expected = array(
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test an invalid image file synchronously.
	 */
	public function test__invalid_image_file_synchronous() {
		$sources  = array(
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		);
		$expected = array(
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources, 'synchronous' );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test a mix of valid and invalid image files.
	 */
	public function test__mix_of_valid_and_invalid_image_file() {
		$sources  = array(
			IMG_350                             => false,
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
			IMG_1024                            => false,
		);
		$expected = array(
			IMG_350                             => array(
				'width'  => 350,
				'height' => 150,
			),
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
			IMG_1024                            => array(
				'width'  => 1024,
				'height' => 768,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test a mix of valid and invalid image files synchronously.
	 */
	public function test__mix_of_valid_and_invalid_image_file_synchronous() {
		$sources  = array(
			IMG_350                             => false,
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
			IMG_1024                            => false,
		);
		$expected = array(
			IMG_350                             => array(
				'width'  => 350,
				'height' => 150,
			),
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
			IMG_1024                            => array(
				'width'  => 1024,
				'height' => 768,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources, 'synchronous' );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test get_default_user_agent()
	 *
	 * @covers \AMP_Image_Dimension_Extractor::get_default_user_agent()
	 */
	public function test__amp_wp_user_agent() {
		$expected   = 'amp-wp, v' . AMP__VERSION . ', ';
		$user_agent = AMP_Image_Dimension_Extractor::get_default_user_agent();
		$user_agent = substr( $user_agent, 0, strlen( $expected ) );

		$this->assertEquals( $expected, $user_agent );
	}
}
