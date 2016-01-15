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
	function test__no_dimensions_provided( $source_url, $expected ) {
		$dimensions = AMP_Image_Dimension_Extractor::extract_from_filename( false, $source_url );
		$this->assertEquals( $expected, $dimensions );
	}

	function test__dimensions_already_passed_in() {
		$source_dimensions = array( 1, 1 );
		$source_url = '';
		$expected = array( 1, 1 );

		$dimensions = AMP_Image_Dimension_Extractor::extract_from_filename( $source_dimensions, $source_url );
		$this->assertEquals( $expected, $dimensions );
	}
}

class AMP_Image_Dimension_Extractor__From_Metadata__Test extends WP_UnitTestCase {
	private $_attachment_id = null;

	function setUp() {
		parent::setUp();

		$file_path = dirname( __FILE__ ) . '/assets/wordpress-logo.png';
		$this->_attachment_id = $this->factory->attachment->create_upload_object( $file_path );
	}

	function test__dimensions_already_passed_in() {
		$source_dimensions = array( 1, 1 );
		$source = wp_get_attachment_url( $this->_attachment_id );
		$expected = array( 1, 1 );

		$dimensions = AMP_Image_Dimension_Extractor::extract_from_attachment_metadata(  $source_dimensions, $source );
		$this->assertEquals( $expected, $dimensions );
	}

	function test__invalid_attachment() {
		$source = 'https://example.com/path/to/file.jpg';
		$expected = false;

		$dimensions = AMP_Image_Dimension_Extractor::extract_from_attachment_metadata(  false, $source );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__valid_attachment_missing_metadata() {
		$source = wp_get_attachment_url( $this->_attachment_id );
		$expected = false;

		delete_post_meta( $this->_attachment_id, '_wp_attachment_metadata' );
		$dimensions = AMP_Image_Dimension_Extractor::extract_from_attachment_metadata( false, $source );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__valid_attachment_and_dimensions_with_query() {
		$source = wp_get_attachment_url( $this->_attachment_id ) . '?rand=1';
		$expected = array( 498, 113 );

		$dimensions = AMP_Image_Dimension_Extractor::extract_from_attachment_metadata( false, $source );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__valid_attachment_and_dimensions() {
		$source = wp_get_attachment_url( $this->_attachment_id );
		$expected = array( 498, 113 );

		$dimensions = AMP_Image_Dimension_Extractor::extract_from_attachment_metadata( false, $source );

		$this->assertEquals( $expected, $dimensions );
	}
}
