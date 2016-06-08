<?php
// stub class since AMP_Base_Sanitizer is abstract
class AMP_Stub_Sanitizer extends AMP_Base_Sanitizer {
	public function sanitize() {
		return $this->dom;
	}
}

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
					'class' => 'amp-wp-enforced-sizes'
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
					'class' => 'my-class amp-wp-enforced-sizes'
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
					'class' => 'amp-wp-enforced-sizes'
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
					'class' => 'amp-wp-enforced-sizes'
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
		$sanitizer = new AMP_Stub_Sanitizer( new DOMDocument, $args );
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
		$sanitizer = new AMP_Stub_Sanitizer( new DOMDocument, $args );
		$returned_attributes = $sanitizer->enforce_fixed_height( $source_attributes );

		$this->assertEquals( $expected_attributes, $returned_attributes );
	}
}
