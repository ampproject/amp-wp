<?php
/**
 * Tests for Post Types Support.
 *
 * @package AMP
 * @since 0.6
 */

/**
 * Tests for Post Types Support.
 */
class Test_AMP_Post_Types_Support extends WP_UnitTestCase {

	/**
	 * Test amp_core_post_types_support.
	 *
	 * @covers amp_core_post_types_support()
	 */
	public function test_init() {
		remove_post_type_support( 'post', AMP_QUERY_VAR );
		amp_core_post_types_support();
		$this->assertTrue( post_type_supports( 'post', AMP_QUERY_VAR ) );
	}

	/**
	 * Test amp_custom_post_types_support.
	 *
	 * @covers amp_custom_post_types_support()
	 */
	public function test_amp_custom_post_types_support() {
		amp_custom_post_types_support();
		$this->assertFalse( post_type_supports( 'foo', AMP_QUERY_VAR ) );
		$this->assertFalse( post_type_supports( 'bar', AMP_QUERY_VAR ) );

		update_option( AMP_Options_Manager::OPTION_NAME, array(
			'supported_post_types' => array(
				'foo' => true,
				'bar' => true,
			),
		) );
		amp_custom_post_types_support();
		$this->assertTrue( post_type_supports( 'foo', AMP_QUERY_VAR ) );
		$this->assertTrue( post_type_supports( 'bar', AMP_QUERY_VAR ) );
	}
}
