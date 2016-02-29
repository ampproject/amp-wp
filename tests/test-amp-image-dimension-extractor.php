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

class AMP_Image_Dimension_Extractor__From_Metadata__Test extends WP_UnitTestCase {
	private $_attachment_id = null;

	function setUp() {
		parent::setUp();

		$this->_attachment_id = $this->factory->attachment->create_upload_object( AMP_IMG_DIMENSION_TEST_VALID_FILE );
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

// TODO: tests for transients, errors, lock
// TODO: mocked tests
class AMP_Image_Dimension_Extractor__By_Downloading__Test extends WP_UnitTestCase {
	function test__dimensions_already_passed_in() {
		$source_dimensions = array( 1, 1 );
		$source = AMP_IMG_DIMENSION_TEST_VALID_FILE;
		$expected = array( 1, 1 );

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_image( $source_dimensions, $source );

		$this->assertEquals( $expected, $dimensions );
	}

	function test__valid_image_file() {
		$source = AMP_IMG_DIMENSION_TEST_VALID_FILE;
		$expected = array( 498, 113 );

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_image( false, $source );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Warning
	 */
	function test__invalid_image_file() {
		$source = AMP_IMG_DIMENSION_TEST_INVALID_FILE;
		$expected = false;

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_image( false, $source );

		$this->assertEquals( $expected, $dimensions );
	}
}
