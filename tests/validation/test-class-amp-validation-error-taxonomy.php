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
	 * Test get_response.
	 *
	 * @covers AMP_Validation_Manager::summarize_validation_errors()
	 */
	public function test_summarize_validation_errors() {
		$this->markTestSkipped( 'Needs refactoring' );

		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$this->process_markup( $this->disallowed_tag );
		$response = AMP_Validation_Manager::summarize_validation_errors( wp_list_pluck( AMP_Validation_Manager::$validation_results, 'error' ) );
		AMP_Validation_Manager::reset_validation_results();
		$expected_response = array(
			AMP_Validation_Manager::REMOVED_ELEMENTS   => array(
				'script' => 1,
			),
			AMP_Validation_Manager::REMOVED_ATTRIBUTES => array(),
			'sources_with_invalid_output'              => array(),
		);
		$this->assertEquals( $expected_response, $response );
	}
}
