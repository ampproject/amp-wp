<?php
/**
 * Tests for AMP_Validation_Error_Taxonomy class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Validation_Error_Taxonomy class.
 *
 * @covers AMP_Validation_Error_Taxonomy
 */
class Test_AMP_Validation_Error_Taxonomy extends \WP_UnitTestCase {

	/**
	 * Test register.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::register()
	 */
	public function test_register() {
		$this->markTestIncomplete();
	}


	/**
	 * Test summarize_validation_errors.
	 *
	 * @covers AMP_Validation_Manager::summarize_validation_errors()
	 */
	public function test_summarize_validation_errors() {
		$attribute_node_name = 'button';
		$element_node_name   = 'nonexistent-element';
		$validation_errors   = array(
			array(
				'code'      => 'invalid_attribute',
				'node_name' => $attribute_node_name,
				'sources'   => array(
					array(
						'type' => 'plugin',
						'name' => 'foo',
					),
				),
			),
			array(
				'code'      => 'invalid_element',
				'node_name' => $element_node_name,
				'sources'   => array(
					array(
						'type' => 'theme',
						'name' => 'bar',
					),
				),
			),
		);

		$results          = AMP_Validation_Error_Taxonomy::summarize_validation_errors( $validation_errors );
		$expected_results = array(
			AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES => array(
				$attribute_node_name => 1,
			),
			AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS => array(
				$element_node_name => 1,
			),
			'sources_with_invalid_output' => array(
				'plugin' => array( 'foo' ),
				'theme'  => array( 'bar' ),
			),
		);
		$this->assertEquals( $expected_results, $results );
	}
}
