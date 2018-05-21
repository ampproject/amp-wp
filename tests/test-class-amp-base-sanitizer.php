<?php
/**
 * Class AMP_Base_Sanitizer_Test
 *
 * @package AMP
 */

/**
 * Test AMP_Base_Sanitizer_Test
 *
 * @covers AMP_Base_Sanitizer
 */
class AMP_Base_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Gets data for test_set_layout().
	 *
	 * @return array
	 */
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
					'width'  => '',
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
				),
			),
		);
	}

	/**
	 * Test AMP_Base_Sanitizer::set_layout().
	 *
	 * @dataProvider get_data
	 * @param array $source_attributes   Source Attrs.
	 * @param array $expected_attributes Expected Attrs.
	 * @param array $args                Args.
	 * @covers AMP_Base_Sanitizer::enforce_fixed_height()
	 */
	public function test_set_layout( $source_attributes, $expected_attributes, $args = array() ) {
		$sanitizer           = new AMP_Test_Stub_Sanitizer( new DOMDocument(), $args );
		$returned_attributes = $sanitizer->set_layout( $source_attributes );
		$this->assertEquals( $expected_attributes, $returned_attributes );
	}

	/**
	 * Get sanitize_dimension data.
	 *
	 * @return array Data.
	 */
	public function get_sanitize_dimension_data() {
		return array(
			'empty'                => array(
				array( '', 'width' ),
				'',
			),

			'empty_space'          => array(
				array( ' ', 'width' ),
				'',
			),

			'int'                  => array(
				array( 123, 'width' ),
				123,
			),

			'int_as_string'        => array(
				array( '123', 'width' ),
				123,
			),

			'with_px'              => array(
				array( '567px', 'width' ),
				567,
			),

			'100%_width__with_max' => array(
				array( '100%', 'width' ),
				600,
				array( 'content_max_width' => 600 ),
			),

			'100%_width__no_max'   => array(
				array( '100%', 'width' ),
				'',
			),

			'50%_width__with_max'  => array(
				array( '50%', 'width' ),
				300,
				array( 'content_max_width' => 600 ),
			),

			'%_height'             => array(
				array( '100%', 'height' ),
				'',
			),

			'non_int'              => array(
				array( 'abcd', 'width' ),
				'',
			),
		);
	}

	/**
	 * Test AMP_Base_Sanitizer::sanitize_dimension().
	 *
	 * @param array $source_params  Source Attrs.
	 * @param array $expected_value Expected Attrs.
	 * @param array $args           Args.
	 * @dataProvider get_sanitize_dimension_data
	 * @covers AMP_Base_Sanitizer::sanitize_dimension()
	 */
	public function test_sanitize_dimension( $source_params, $expected_value, $args = array() ) {
		$sanitizer                 = new AMP_Test_Stub_Sanitizer( new DOMDocument(), $args );
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
		AMP_Validation_Utils::reset_validation_results();
		$parent_tag_name = 'div';
		$dom_document    = new DOMDocument( '1.0', 'utf-8' );
		$parent          = $dom_document->createElement( $parent_tag_name );
		$child           = $dom_document->createElement( 'h1' );
		$parent->appendChild( $child );

		$this->assertEquals( $child, $parent->firstChild );
		$sanitizer = new AMP_Iframe_Sanitizer(
			$dom_document, array(
				'validation_error_callback' => 'AMP_Validation_Utils::add_validation_error',
			)
		);
		$sanitizer->remove_invalid_child( $child );
		$this->assertEquals( null, $parent->firstChild );
		$this->assertCount( 1, AMP_Validation_Utils::$validation_errors );
		$this->assertEquals( $child->nodeName, AMP_Validation_Utils::$validation_errors[0]['node_name'] );

		$parent->appendChild( $child );
		$this->assertEquals( $child, $parent->firstChild );
		$sanitizer->remove_invalid_child( $child );

		$this->assertEquals( null, $parent->firstChild );
		$this->assertEquals( null, $child->parentNode );
		AMP_Validation_Utils::$validation_errors = null;
	}

	/**
	 * Tests remove_child.
	 *
	 * @covers AMP_Base_Sanitizer::remove_invalid_child()
	 */
	public function test_remove_attribute() {
		AMP_Validation_Utils::reset_validation_results();
		$video_name   = 'amp-video';
		$attribute    = 'onload';
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$video        = $dom_document->createElement( $video_name );
		$video->setAttribute( $attribute, 'someFunction()' );
		$attr_node = $video->getAttributeNode( $attribute );
		$args      = array(
			'validation_error_callback' => 'AMP_Validation_Utils::add_validation_error',
		);
		$sanitizer = new AMP_Video_Sanitizer( $dom_document, $args );
		$sanitizer->remove_invalid_attribute( $video, $attribute );
		$this->assertEquals( null, $video->getAttribute( $attribute ) );
		$this->assertEquals(
			array(
				'code'               => AMP_Validation_Utils::INVALID_ATTRIBUTE_CODE,
				'node_name'          => $attr_node->nodeName,
				'parent_name'        => $video->nodeName,
				'sources'            => array(),
				'element_attributes' => array(
					'onload' => 'someFunction()',
				),
			),
			AMP_Validation_Utils::$validation_errors[0]
		);
		AMP_Validation_Utils::reset_validation_results();
	}

	/**
	 * Tests get_data_amp_attributes.
	 *
	 * @covers AMP_Base_Sanitizer::get_data_amp_attributes()
	 */
	public function test_get_data_amp_attributes() {
		$tag          = 'figure';
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$figure       = $dom_document->createElement( $tag );
		$amp_img      = $dom_document->createElement( 'amp-img' );
		$figure->appendChild( $amp_img );
		$figure->setAttribute( 'data-amp-noloading', 'true' );
		$figure->setAttribute( 'data-amp-layout', 'fixed' );

		$sanitizer = new AMP_Test_Stub_Sanitizer( new DOMDocument(), array() );
		$amp_args  = $sanitizer->get_data_amp_attributes( $amp_img );

		$expected_args = array(
			'layout'    => 'fixed',
			'noloading' => 'true',
		);

		$this->assertEquals( $expected_args, $amp_args );
	}

	/**
	 * Tests set_data_amp_attributes.
	 *
	 * @covers AMP_Base_Sanitizer::filter_data_amp_attributes()
	 */
	public function test_filter_data_amp_attributes() {
		$amp_data   = array(
			'noloading' => true,
			'invalid'   => 123,
		);
		$attributes = array(
			'width' => 100,
		);
		$sanitizer  = new AMP_Test_Stub_Sanitizer( new DOMDocument(), array() );
		$attributes = $sanitizer->filter_data_amp_attributes( $attributes, $amp_data );

		$expected = array(
			'width'              => 100,
			'data-amp-noloading' => '',
		);
		$this->assertEquals( $expected, $attributes );
	}

	/**
	 * Tests set_attachment_layout_attributes.
	 *
	 * @covers AMP_Base_Sanitizer::set_attachment_layout_attributes()
	 */
	public function test_filter_attachment_layout_attributes() {
		$sanitizer    = new AMP_Test_Stub_Sanitizer( new DOMDocument(), array() );
		$tag          = 'figure';
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$figure       = $dom_document->createElement( $tag );
		$amp_img      = $dom_document->createElement( 'amp-img' );
		$layout       = 'fixed-height';
		$figure->appendChild( $amp_img );
		$attributes = array(
			'src' => '',
		);

		$attributes    = $sanitizer->filter_attachment_layout_attributes( $amp_img, $attributes, $layout );
		$expected_atts = array(
			'src'    => '',
			'height' => 400,
			'width'  => 'auto',
		);
		$this->assertEquals( $expected_atts, $attributes );

		$parent_style   = $figure->getAttribute( 'style' );
		$expected_style = 'height: 400px; width: auto;';
		$this->assertEquals( $expected_style, $parent_style );
	}
}
