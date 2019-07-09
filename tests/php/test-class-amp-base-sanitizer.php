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
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		AMP_Validation_Manager::reset_validation_results();
	}

	/**
	 * Tear down.
	 */
	public function tearDown() {
		parent::tearDown();
		AMP_Validation_Manager::reset_validation_results();
		AMP_Validation_Manager::$should_locate_sources = false;
	}

	/**
	 * Gets data for test_set_layout().
	 *
	 * @return array
	 */
	public function get_data() {
		return [
			'both_dimensions_included' => [
				[
					'width'  => 100,
					'height' => 100,
					'layout' => 'responsive',
				],
				[
					'width'  => 100,
					'height' => 100,
					'layout' => 'responsive',
				],
			],

			'both_dimensions_missing'  => [
				[],
				[
					'height' => 400,
					'layout' => 'fixed-height',
					'width'  => 'auto',
				],
			],

			'both_dimensions_empty'    => [
				[
					'width'  => '',
					'height' => '',
				],
				[
					'height' => 400,
					'layout' => 'fixed-height',
					'width'  => 'auto',
				],
			],

			'no_width'                 => [
				[
					'height' => 100,
				],
				[
					'height' => 100,
					'layout' => 'fixed-height',
					'width'  => 'auto',
				],
			],

			'no_height'                => [
				[
					'width' => 200,
				],
				[
					'height' => 400,
					'layout' => 'fixed-height',
					'width'  => 'auto',
				],
			],

			'no_layout_specified'      => [
				[
					'width'  => 100,
					'height' => 100,
				],
				[
					'width'  => 100,
					'height' => 100,
				],
			],
		];
	}

	/**
	 * Test AMP_Base_Sanitizer::set_layout().
	 *
	 * @dataProvider get_data
	 * @param array $source_attributes   Source Attrs.
	 * @param array $expected_attributes Expected Attrs.
	 * @param array $args                Args.
	 * @covers AMP_Base_Sanitizer::set_layout()
	 */
	public function test_set_layout( $source_attributes, $expected_attributes, $args = [] ) {
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
		return [
			'empty'                => [
				[ '', 'width' ],
				'',
			],

			'empty_space'          => [
				[ ' ', 'width' ],
				'',
			],

			'int'                  => [
				[ 123, 'width' ],
				123,
			],

			'int_as_string'        => [
				[ '123', 'width' ],
				123,
			],

			'with_px'              => [
				[ '567px', 'width' ],
				567,
			],

			'100%_width__with_max' => [
				[ '100%', 'width' ],
				600,
				[ 'content_max_width' => 600 ],
			],

			'100%_width__no_max'   => [
				[ '100%', 'width' ],
				'',
			],

			'50%_width__with_max'  => [
				[ '50%', 'width' ],
				300,
				[ 'content_max_width' => 600 ],
			],

			'%_height'             => [
				[ '100%', 'height' ],
				'',
			],

			'non_int'              => [
				[ 'abcd', 'width' ],
				'',
			],
		];
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
	public function test_sanitize_dimension( $source_params, $expected_value, $args = [] ) {
		$sanitizer                 = new AMP_Test_Stub_Sanitizer( new DOMDocument(), $args );
		list( $value, $dimension ) = $source_params;

		$actual_value = $sanitizer->sanitize_dimension( $value, $dimension );

		$this->assertEquals( $expected_value, $actual_value );
	}

	/**
	 * Tests remove_invalid_child.
	 *
	 * @covers AMP_Base_Sanitizer::remove_invalid_child()
	 * @covers AMP_Base_Sanitizer::should_sanitize_validation_error()
	 * @covers AMP_Base_Sanitizer::prepare_validation_error()
	 */
	public function test_remove_invalid_child() {
		$parent_tag_name = 'div';
		$dom_document    = new DOMDocument( '1.0', 'utf-8' );
		$parent          = $dom_document->createElement( $parent_tag_name );
		$child           = $dom_document->createElement( 'script' );
		$child->setAttribute( 'id', 'foo' );
		$child->setAttribute( 'src', 'http://example.com/bad.js?ver=123' );
		$parent->appendChild( $child );

		$expected_error = [
			'code'            => AMP_Validation_Error_Taxonomy::INVALID_ELEMENT_CODE,
			'node_name'       => $child->nodeName,
			'parent_name'     => $parent_tag_name,
			'node_attributes' => [
				'id'  => 'foo',
				'src' => 'http://example.com/bad.js?ver=__normalized__',
			],
			'foo'             => 'bar',
			'sources'         => null,
			'type'            => AMP_Validation_Error_Taxonomy::JS_ERROR_TYPE,
		];

		// Test forcibly sanitized with filter.
		add_filter( 'amp_validation_error_sanitized', '__return_true' );
		$this->assertEquals( $child, $parent->firstChild );
		$sanitizer = new AMP_Iframe_Sanitizer(
			$dom_document,
			[
				'validation_error_callback' => 'AMP_Validation_Manager::add_validation_error',
			]
		);
		$sanitizer->remove_invalid_child( $child, [ 'foo' => 'bar' ] );
		$this->assertEquals( null, $parent->firstChild );
		$this->assertCount( 0, AMP_Validation_Manager::$validation_results );
		remove_filter( 'amp_validation_error_sanitized', '__return_true' );

		// Test unsanitized.
		$child = $dom_document->createElement( 'link' );
		$child->setAttribute( 'id', 'foo' );
		$child->setAttribute( 'href', 'http://example.com/bad.css?ver=123' );
		$expected_error['node_name'] = 'link';
		unset( $expected_error['node_attributes']['src'] );
		$expected_error['node_attributes']['href'] = 'http://example.com/bad.css?ver=__normalized__';
		$expected_error['type']                    = AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE;
		add_filter( 'amp_validation_error_sanitized', '__return_false' );
		AMP_Validation_Manager::reset_validation_results();
		$parent->appendChild( $child );
		$sanitizer->remove_invalid_child( $child, [ 'foo' => 'bar' ] );
		$this->assertEquals( $child, $parent->firstChild );
		$this->assertCount( 1, AMP_Validation_Manager::$validation_results );
		$this->assertEquals(
			[
				'error'     => $expected_error,
				'sanitized' => false,
			],
			AMP_Validation_Manager::$validation_results[0]
		);

		// Make sure the validation error is not duplicated since it was not sanitized.
		$sanitizer->remove_invalid_child( $child, [ 'foo' => 'bar' ] );
		$this->assertCount( 1, AMP_Validation_Manager::$validation_results );
		AMP_Validation_Manager::$validation_results = null;
	}

	/**
	 * Tests remove_invalid_child and should_sanitize_validation_error.
	 *
	 * @covers AMP_Base_Sanitizer::remove_invalid_attribute()
	 * @covers AMP_Base_Sanitizer::should_sanitize_validation_error()
	 * @covers AMP_Base_Sanitizer::prepare_validation_error()
	 */
	public function test_remove_invalid_attribute() {
		$that = $this;

		// Test sanitizing.
		$dom       = AMP_DOM_Utils::get_dom_from_content( '<amp-video id="bar" onload="someFunc()"></amp-video>' );
		$sanitizer = new AMP_Video_Sanitizer(
			$dom,
			[
				'validation_error_callback' => static function( $error, $context ) use ( $that ) {
					$that->assertEquals(
						[
							'node_name'          => 'onload',
							'parent_name'        => 'amp-video',
							'code'               => 'invalid_attribute',
							'element_attributes' =>
								[
									'id'     => 'bar',
									'onload' => 'someFunc()',
								],
							'type'               => AMP_Validation_Error_Taxonomy::JS_ERROR_TYPE,
						],
						$error
					);
					$that->assertInstanceOf( 'DOMAttr', $context['node'] );
					$that->assertEquals( 'onload', $context['node']->nodeName );
					return true;
				},
			]
		);
		$element   = $dom->getElementsByTagName( 'amp-video' )->item( 0 );
		$this->assertTrue( $sanitizer->remove_invalid_attribute( $element, 'onload' ) );
		$this->assertFalse( $element->hasAttribute( 'onload' ) );

		// Test not sanitizing.
		$dom       = AMP_DOM_Utils::get_dom_from_content( '<amp-video id="bar" onload="someFunc()"></amp-video>' );
		$sanitizer = new AMP_Video_Sanitizer(
			$dom,
			[
				'validation_error_callback' => static function( $error, $context ) use ( $that ) {
					$that->assertEquals(
						[
							'node_name'          => 'onload',
							'parent_name'        => 'amp-video',
							'code'               => 'invalid_attribute',
							'element_attributes' =>
								[
									'id'     => 'bar',
									'onload' => 'someFunc()',
								],
							'type'               => AMP_Validation_Error_Taxonomy::JS_ERROR_TYPE,
						],
						$error
					);
					$that->assertInstanceOf( 'DOMAttr', $context['node'] );
					$that->assertEquals( 'onload', $context['node']->nodeName );
					return false;
				},
			]
		);
		$element   = $dom->getElementsByTagName( 'amp-video' )->item( 0 );
		$this->assertFalse( $sanitizer->remove_invalid_attribute( $element, 'onload' ) );
		$this->assertTrue( $element->hasAttribute( 'onload' ) );
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

		$sanitizer = new AMP_Test_Stub_Sanitizer( new DOMDocument(), [] );
		$amp_args  = $sanitizer->get_data_amp_attributes( $amp_img );

		$expected_args = [
			'layout'    => 'fixed',
			'noloading' => 'true',
		];

		$this->assertEquals( $expected_args, $amp_args );
	}

	/**
	 * Tests set_data_amp_attributes.
	 *
	 * @covers AMP_Base_Sanitizer::filter_data_amp_attributes()
	 */
	public function test_filter_data_amp_attributes() {
		$amp_data   = [
			'noloading' => true,
			'invalid'   => 123,
		];
		$attributes = [
			'width' => 100,
		];
		$sanitizer  = new AMP_Test_Stub_Sanitizer( new DOMDocument(), [] );
		$attributes = $sanitizer->filter_data_amp_attributes( $attributes, $amp_data );

		$expected = [
			'width'              => 100,
			'data-amp-noloading' => '',
		];
		$this->assertEquals( $expected, $attributes );
	}

	/**
	 * Tests set_attachment_layout_attributes.
	 *
	 * @covers AMP_Base_Sanitizer::filter_attachment_layout_attributes()
	 */
	public function test_filter_attachment_layout_attributes() {
		$sanitizer    = new AMP_Test_Stub_Sanitizer( new DOMDocument(), [] );
		$tag          = 'figure';
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$figure       = $dom_document->createElement( $tag );
		$amp_img      = $dom_document->createElement( 'amp-img' );
		$layout       = 'fixed-height';
		$figure->appendChild( $amp_img );
		$attributes = [
			'src' => '',
		];

		$attributes    = $sanitizer->filter_attachment_layout_attributes( $amp_img, $attributes, $layout );
		$expected_atts = [
			'src'    => '',
			'height' => 400,
			'width'  => 'auto',
		];
		$this->assertEquals( $expected_atts, $attributes );

		$parent_style   = $figure->getAttribute( 'style' );
		$expected_style = 'height: 400px; width: auto;';
		$this->assertEquals( $expected_style, $parent_style );
	}
}
