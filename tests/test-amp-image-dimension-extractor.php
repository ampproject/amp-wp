<?php

class AMP_Image_Dimension_Extractor__From_Filename__Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'no_filename' => array(
				'https://example.com/path/to/folder/',
				false
			),
			'no_dimensions_in_file' => array(
				'https://example.com/path/image.jpg',
				false
			),
			'dimensions_with_wrong_ext' => array(
				'https://example.com/path/image-100x100.txt',
				false,
			),
			'valid_dimensions' => array(
				'https://example.com/path/image-100x100.jpg',
				array( 100, 100 ),
			),
			'valid_dimensions_uppercase_ext' => array(
				'https://example.com/path/image-100x100.PNG',
				array( 100, 100 ),
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	function test_extractor( $source, $expected ) {
		$dimensions = AMP_Image_Dimension_Extractor::extract_from_filename( $source );
		$this->assertEquals( $expected, $dimensions );
	}
}
