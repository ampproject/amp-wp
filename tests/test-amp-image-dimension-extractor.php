<?php

define( 'AMP_IMG_DIMENSION_TEST_VALID_FILE', dirname( __FILE__ ) . '/assets/wordpress-logo.png' );
define( 'AMP_IMG_DIMENSION_TEST_INVALID_FILE', dirname( __FILE__ ) . '/assets/not-exists.png' );

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
