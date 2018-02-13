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
}
