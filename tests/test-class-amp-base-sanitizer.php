<?php

class AMP_Base_Sanitizer__Enforce_Fixed_Height__Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'both_dimensions_included' => array(
				array(
					'width'  => 100,
					'height' => 100,
					'layout' => 'responsive',
				),
				array(
					'width'  => 100,
					'height' => 100,
					'layout' => 'responsive',
				),
			),

			'both_dimensions_missing'  => array(
				array(),
				array(
					'height' => 400,
					'layout' => 'fixed-height',
				),
			),

			'both_dimensions_empty'    => array(
				array(
					'width' => '',
					'height' => '',
				),
				array(
					'height' => 400,
					'layout' => 'fixed-height',
				),
			),

			'no_width'                 => array(
				array(
					'height' => 100,
				),
				array(
					'height' => 100,
					'layout' => 'fixed-height',
				),
			),

			'no_height'                => array(
				array(
					'width' => 200,
				),
				array(
					'height' => 400,
					'layout' => 'fixed-height',
				),
			),

			'no_layout_specified'      => array(
				array(
					'width'  => 100,
					'height' => 100,
				),
				array(
					'width'  => 100,
					'height' => 100,
					'layout' => 'responsive',
				),
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_set_layout( $source_attributes, $expected_attributes, $args = array() ) {
		$sanitizer = new AMP_Test_Stub_Sanitizer( new DOMDocument, $args );
		$returned_attributes = $sanitizer->set_layout( $source_attributes );

		$this->assertEquals( $expected_attributes, $returned_attributes );
	}
}

class AMP_Base_Sanitizer__Sanitize_Dimension__Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'empty' => array(
				array( '', 'width' ),
				'',
			),

			'empty_space' => array(
				array( ' ', 'width' ),
				'',
			),

			'int' => array(
				array( 123, 'width' ),
				123,
			),

			'int_as_string' => array(
				array( '123', 'width' ),
				123,
			),

			'with_px' => array(
				array( '567px', 'width' ),
				567,
			),

			'100%_width__with_max' => array(
				array( '100%', 'width' ),
				600,
				array( 'content_max_width' => 600 ),
			),

			'100%_width__no_max' => array(
				array( '100%', 'width' ),
				'',
			),

			'50%_width__with_max' => array(
				array( '50%', 'width' ),
				300,
				array( 'content_max_width' => 600 ),
			),

			'%_height' => array(
				array( '100%', 'height' ),
				'',
			),

			'non_int' => array(
				array( 'abcd', 'width' ),
				'',
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_enforce_sizes_attribute( $source_params, $expected_value, $args = array() ) {
		$sanitizer = new AMP_Test_Stub_Sanitizer( new DOMDocument, $args );
		list( $value, $dimension ) = $source_params;

		$actual_value = $sanitizer->sanitize_dimension( $value, $dimension );

		$this->assertEquals( $expected_value, $actual_value );
	}

	/**
	 * Tests remove_child.
	 *
	 * @see AMP_Base_Sanitizer::remove_invalid_child()
	 */
	public function test_remove_child() {
		$parent_tag_name = 'div';
		$child_tag_name  = 'h1';
		$dom_document    = new DOMDocument( '1.0', 'utf-8' );
		$parent          = $dom_document->createElement( $parent_tag_name );
		$child           = $dom_document->createElement( 'h1' );
		$parent->appendChild( $child );

		// To ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar.
		// @codingStandardsIgnoreStart

		$this->assertEquals( $child, $parent->firstChild );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom_document, array(
			'remove_invalid_callback' => 'AMP_Validation_Utils::track_removed',
		) );
		$sanitizer->remove_invalid_child( $child );
		$this->assertEquals( null, $parent->firstChild );
		$this->assertEquals( 1, AMP_Validation_Utils::$removed_nodes[ $child_tag_name ] );

		$parent->appendChild( $child );
		$this->assertEquals( $child, $parent->firstChild );
		$sanitizer->remove_invalid_child( $child );

		$this->assertEquals( null, $parent->firstChild );
		$this->assertEquals( null, $child->parentNode );
		// @codingStandardsIgnoreEnd
		AMP_Validation_Utils::$removed_nodes = null;
	}

	/**
	 * Tests remove_child.
	 *
	 * @see AMP_Base_Sanitizer::remove_invalid_child()
	 */
	public function test_remove_attribute() {
		AMP_Validation_Utils::reset_removed();
		$video_name   = 'amp-video';
		$attribute    = 'onload';
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$video        = $dom_document->createElement( $video_name );
		$video->setAttribute( $attribute, 'someFunction()' );

		// To ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar.
		// @codingStandardsIgnoreStart
		$args = array(
			'remove_invalid_callback' => 'AMP_Validation_Utils::track_removed',
		);
		$expected_removed = array(
			$attribute => 1,
		);
		$sanitizer = new AMP_Video_Sanitizer( $dom_document, $args );
		$sanitizer->remove_invalid_attribute( $video, $attribute );
		$this->assertEquals( null, $video->getAttribute( $attribute ) );
		$this->assertEquals( $expected_removed, AMP_Validation_Utils::$removed_attributes );
		// @codingStandardsIgnoreEnd
		AMP_Validation_Utils::reset_removed();
	}

}
