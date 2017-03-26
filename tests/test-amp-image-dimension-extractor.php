<?php

define( 'AMP_IMG_DIMENSION_TEST_VALID_FILE', dirname( __FILE__ ) . '/assets/wordpress-logo.png' );
define( 'AMP_IMG_DIMENSION_TEST_INVALID_FILE', dirname( __FILE__ ) . '/assets/not-exists.png' );

// Not ideal to use remote URLs (since the remote service can change); mocking would be better.
define( 'IMG_350', 'http://i0.wp.com/amptest.files.wordpress.com/2017/03/350x150.png' );
define( 'IMG_1024', 'http://i0.wp.com/amptest.files.wordpress.com/2017/03/1024x768.png' );

class AMP_Image_Dimension_Extractor__Normalize_URL__Test extends WP_UnitTestCase {
	function get_data() {
		$site_url = site_url();

		return array(
			'empty_url' => array(
				'',
				false,
			),
			'data_url' => array(
				'data:image/gif;base64,R0lGODl...',
				false,
			),
			'protocol-less_url' => array(
				'//example.com/file.jpg',
				'http://example.com/file.jpg',
			),
			'path_only' => array(
				'/path/to/file.png',
				$site_url . '/path/to/file.png',
			),
			'query_only' => array(
				'?file=file.png',
				$site_url . '/?file=file.png',
			),
			'path_and_query' => array(
				'/path/file.jpg?query=1',
				$site_url . '/path/file.jpg?query=1',
			),
			'normal_url' => array(
				'https://example.com/path/to/file.jpg',
				'https://example.com/path/to/file.jpg',
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	function test__normalize_url( $source_url, $expected_url ) {
		$result_url = AMP_Image_Dimension_Extractor::normalize_url( $source_url );

		$this->assertEquals( $expected_url, $result_url );
	}
}

// TODO: tests for transients, errors, lock
// TODO: mocked tests
class AMP_Image_Dimension_Extractor__By_Downloading__Test extends WP_UnitTestCase {

	function test__valid_image_file() {
		$sources = array(
		    IMG_350 => false,
		);
		$expected = array(
		    IMG_350 => array(
		        'width' => 350,
				'height' => 150,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__valid_image_file_synchronous() {
		$sources = array(
			IMG_350 => false,
		);
		$expected = array(
			IMG_350 => array(
				'width' => 350,
				'height' => 150,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources, 'synchronous' );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__multiple_valid_image_files() {
		$sources = array(
			IMG_350 => false,
			IMG_1024 => false,
		);
		$expected = array(
			IMG_350 => array(
				'width' => 350,
				'height' => 150,
			),
			IMG_1024 => array(
				'width' => 1024,
				'height' => 768,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__multiple_valid_image_files_synchronous() {
		$sources = array(
			IMG_350 => false,
			IMG_1024 => false,
		);
		$expected = array(
			IMG_350 => array(
				'width' => 350,
				'height' => 150,
			),
			IMG_1024 => array(
				'width' => 1024,
				'height' => 768,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources, 'synchronous' );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__invalid_image_file() {
		$sources = array(
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		);
		$expected = array(
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__invalid_image_file_synchronous() {
		$sources = array(
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		);
		$expected = array(
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources, 'synchronous' );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__mix_of_valid_and_invalid_image_file() {
		$sources = array(
			IMG_350 => false,
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
			IMG_1024 => false,
		);
		$expected = array(
			IMG_350 => array(
				'width' => 350,
				'height' => 150,
			),
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
			IMG_1024 => array(
				'width' => 1024,
				'height' => 768,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__mix_of_valid_and_invalid_image_file_synchronous() {
		$sources = array(
			IMG_350 => false,
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
			IMG_1024 => false,
		);
		$expected = array(
			IMG_350 => array(
				'width' => 350,
				'height' => 150,
			),
			AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
			IMG_1024 => array(
				'width' => 1024,
				'height' => 768,
			),
		);

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources, 'synchronous' );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__amp_wp_user_agent() {
		$expected = 'amp-wp, v' . AMP__VERSION . ', ';
		$user_agent = AMP_Image_Dimension_Extractor::get_default_user_agent( '' );
		$user_agent = substr( $user_agent, 0, strlen( $expected ) );

		$this->assertEquals( $expected, $user_agent );
	}
}
