<?php

class AMP_Base_Sanitizer__Enforce_Sizes_Attribute__Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'already_has_sizes' => array(
				array(
					'sizes' => 'blah',
				),
				array(
					'sizes' => 'blah',
				),
			),

			'empty' => array(
				array(),
				array(),
			),

			'no_width' => array(
				array(
					'height' => 100,
				),
				array(
					'height' => 100,
				),
			),

			'no_height' => array(
				array(
					'width' => 200,
				),
				array(
					'width' => 200,
				),
			),

			'enforce_sizes_no_class' => array(
				array(
					'width' => 200,
					'height' => 100,
				),
				array(
					'width' => 200,
					'height' => 100,
					'sizes' => '(min-width: 200px) 200px, 100vw',
					'class' => 'amp-wp-enforced-sizes',
				),
			),

			'enforce_sizes_has_class' => array(
				array(
					'width' => 200,
					'height' => 100,
					'class' => 'my-class',
				),
				array(
					'width' => 200,
					'height' => 100,
					'sizes' => '(min-width: 200px) 200px, 100vw',
					'class' => 'my-class amp-wp-enforced-sizes',
				),
			),

			'enforce_sizes_with_bigger_content_max_width' => array(
				array(
					'width' => 250,
					'height' => 100,
				),
				array(
					'width' => 250,
					'height' => 100,
					'sizes' => '(min-width: 250px) 250px, 100vw',
					'class' => 'amp-wp-enforced-sizes',
				),
				array(
					'content_max_width' => 500,
				),
			),

			'enforce_sizes_with_smaller_content_max_width' => array(
				array(
					'width' => 800,
					'height' => 350,
				),
				array(
					'width' => 800,
					'height' => 350,
					'sizes' => '(min-width: 675px) 675px, 100vw',
					'class' => 'amp-wp-enforced-sizes',
				),
				array(
					'content_max_width' => 675,
				),
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_enforce_sizes_attribute( $source_attributes, $expected_attributes, $args = array() ) {
		$sanitizer = new AMP_Test_Stub_Sanitizer( new DOMDocument, $args );
		$returned_attributes = $sanitizer->enforce_sizes_attribute( $source_attributes );

		$this->assertEquals( $expected_attributes, $returned_attributes );
	}
}

class AMP_Base_Sanitizer__Enforce_Fixed_Height__Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'both_dimensions_included' => array(
				array(
					'width' => 100,
					'height' => 100,
				),
				array(
					'width' => 100,
					'height' => 100,
				),
			),

			'both_dimensions_missing' => array(
				array(),
				array(
					'height' => 400,
					'layout' => 'fixed-height',
				),
			),

			'both_dimensions_empty' => array(
				array(
					'width' => '',
					'height' => '',
				),
				array(
					'height' => 400,
					'layout' => 'fixed-height',
				),
			),

			'no_width' => array(
				array(
					'height' => 100,
				),
				array(
					'height' => 100,
					'layout' => 'fixed-height',
				),
			),

			'no_height' => array(
				array(
					'width' => 200,
				),
				array(
					'height' => 400,
					'layout' => 'fixed-height',
				),
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_enforce_fixed_height( $source_attributes, $expected_attributes, $args = array() ) {
		$sanitizer = new AMP_Test_Stub_Sanitizer( new DOMDocument, $args );
		$returned_attributes = $sanitizer->enforce_fixed_height( $source_attributes );

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
	 * @covers AMP_Base_Sanitizer::remove_invalid_child()
	 */
	public function test_remove_child() {
		$parent_tag_name = 'div';
		$dom_document    = new DOMDocument( '1.0', 'utf-8' );
		$parent          = $dom_document->createElement( $parent_tag_name );
		$child           = $dom_document->createElement( 'h1' );
		$parent->appendChild( $child );

		$this->assertEquals( $child, $parent->firstChild );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom_document, array(
			'remove_invalid_callback' => 'AMP_Validation_Utils::track_removed',
		) );
		$sanitizer->remove_invalid_child( $child );
		$this->assertEquals( null, $parent->firstChild );
		$this->assertCount( 1, AMP_Validation_Utils::$removed_nodes );
		$this->assertEquals( $child, AMP_Validation_Utils::$removed_nodes[0] );

		$parent->appendChild( $child );
		$this->assertEquals( $child, $parent->firstChild );
		$sanitizer->remove_invalid_child( $child );

		$this->assertEquals( null, $parent->firstChild );
		$this->assertEquals( null, $child->parentNode );
		AMP_Validation_Utils::$removed_nodes = null;
	}

	/**
	 * Tests remove_child.
	 *
	 * @covers AMP_Base_Sanitizer::remove_invalid_child()
	 */
	public function test_remove_attribute() {
		AMP_Validation_Utils::reset_removed();
		$video_name   = 'amp-video';
		$attribute    = 'onload';
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$video        = $dom_document->createElement( $video_name );
		$video->setAttribute( $attribute, 'someFunction()' );
		$attr_node = $video->getAttributeNode( $attribute );
		$args      = array(
			'remove_invalid_callback' => 'AMP_Validation_Utils::track_removed',
		);
		$sanitizer = new AMP_Video_Sanitizer( $dom_document, $args );
		$sanitizer->remove_invalid_attribute( $video, $attribute );
		$this->assertEquals( null, $video->getAttribute( $attribute ) );
		$this->assertEquals( array( $attr_node ), AMP_Validation_Utils::$removed_nodes );
		AMP_Validation_Utils::reset_removed();
	}

	/**
	 * Test set_plugin_output.
	 *
	 * @covers AMP_Base_Sanitizer::set_plugin_output()
	 */
	public function test_set_plugin_output() {
		$dom             = new DomDocument();
		$closing_comment = 'after:amp';
		$node            = $dom->createComment( $closing_comment );
		$sanitizer       = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$this->assertEquals( null, $sanitizer->current_plugin_output );
		$sanitizer->set_plugin_output( $node );
		$this->assertEquals( 'amp', $sanitizer->current_plugin_output );
		$opening_comment = $dom->createComment( 'before:amp' );
		$sanitizer->set_plugin_output( $opening_comment );
		$this->assertEquals( null, $sanitizer->current_plugin_output );
	}

}
