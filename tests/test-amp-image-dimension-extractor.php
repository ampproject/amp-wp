<?php

define( 'AMP_IMG_DIMENSION_TEST_VALID_FILE', dirname( __FILE__ ) . '/assets/wordpress-logo.png' );
define( 'AMP_IMG_DIMENSION_TEST_INVALID_FILE', dirname( __FILE__ ) . '/assets/not-exists.png' );

class AMP_Image_Dimension_Extractor__Normalize_URL__Test extends WP_UnitTestCase {
	function get_data() {
		$site_url = site_url();

		return array(
			'empty_url' => array(
				'',
				false
			),
			'data_url' => array(
				'data:image/gif;base64,R0lGODl...',
				false
			),
			'protocol-less_url' => array(
				'//example.com/file.jpg',
				'http://example.com/file.jpg'
			),
			'path_only' => array(
				'/path/to/file.png',
				$site_url . '/path/to/file.png'
			),
			'query_only' => array(
				'?file=file.png',
				$site_url . '/?file=file.png'
			),
			'path_and_query' => array(
				'/path/file.jpg?query=1',
				$site_url . '/path/file.jpg?query=1'
			),
			'normal_url' => array(
				'https://example.com/path/to/file.jpg',
				'https://example.com/path/to/file.jpg'
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
		$source = 'https://placehold.it/350x150.png';
		$expected = array(
		    'https://placehold.it/350x150.png' => array(
		        'width' => 350,
                'height' => 150 ),
        );

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( array ( $source ) );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__multiple_valid_image_files() {
        $sources = array (
            'https://placehold.it/350x150.png',
            'https://placehold.it/1024x768.png',
        );
        $expected = array(
            'https://placehold.it/350x150.png' => array(
                'width' => 350,
                'height' => 150 ),
            'https://placehold.it/1024x768.png' => array(
                'width' => 1024,
                'height' => 768 ),
        );

        $dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

        $this->assertEquals( $expected, $dimensions );
    }

	function test__invalid_image_file() {
		$source = AMP_IMG_DIMENSION_TEST_INVALID_FILE;
        $expected = array(
            AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
        );

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( array( $source ) );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__mix_of_valid_and_invalid_image_file() {
        $sources = array (
            'https://placehold.it/350x150.png',
            AMP_IMG_DIMENSION_TEST_INVALID_FILE,
            'https://placehold.it/1024x768.png',
        );
        $expected = array(
            'https://placehold.it/350x150.png' => array(
                'width' => 350,
                'height' => 150 ),
            AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
            'https://placehold.it/1024x768.png' => array(
                'width' => 1024,
                'height' => 768 ),
        );

        $dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

        $this->assertEquals( $expected, $dimensions );
    }
}
