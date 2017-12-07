<?php
/**
 * Tests for Post Types Support.
 *
 * @package AMP
 * @since 0.6
 */

/**
 * Tests for Post Type Support.
 *
 * @covers AMP_Post_Type_Support
 */
class Test_AMP_Post_Type_Support extends WP_UnitTestCase {

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		parent::tearDown();
		unregister_post_type( 'book' );
		unregister_post_type( 'secret' );
	}

	/**
	 * Test add_hooks().
	 *
	 * @covers AMP_Post_Type_Support::init()
	 */
	public function test_init() {
		AMP_Post_Type_Support::init();
		$this->assertEquals( 5, has_action( 'after_setup_theme', array( 'AMP_Post_Type_Support', 'add_post_type_support' ) ) );
	}

	/**
	 * Test get_builtin_supported_post_types.
	 *
	 * @covers AMP_Post_Type_Support::get_builtin_supported_post_types()
	 */
	public function test_get_builtin_supported_post_types() {
		$this->assertEquals( array( 'post', 'page' ), AMP_Post_Type_Support::get_builtin_supported_post_types() );
	}

	/**
	 * Test get_eligible_post_types.
	 *
	 * @covers AMP_Post_Type_Support::get_eligible_post_types()
	 */
	public function test_get_eligible_post_types() {
		register_post_type( 'book', array(
			'label'  => 'Book',
			'public' => true,
		) );
		register_post_type( 'secret', array(
			'label'  => 'Secret',
			'public' => false,
		) );

		$this->assertEquals(
			array(
				'post',
				'page',
				'book',
			),
			AMP_Post_Type_Support::get_eligible_post_types()
		);
	}

	/**
	 * Test add_post_type_support.
	 *
	 * @covers AMP_Post_Type_Support::add_post_type_support()
	 */
	public function test_add_post_type_support() {
		register_post_type( 'book', array(
			'label'  => 'Book',
			'public' => true,
		) );
		register_post_type( 'poem', array(
			'label'  => 'Poem',
			'public' => true,
		) );
		AMP_Options_Manager::update_option( 'supported_post_types', array( 'poem' ) );

		AMP_Post_Type_Support::add_post_type_support();
		$this->assertTrue( post_type_supports( 'post', AMP_QUERY_VAR ) );
		$this->assertTrue( post_type_supports( 'poem', AMP_QUERY_VAR ) );
		$this->assertFalse( post_type_supports( 'book', AMP_QUERY_VAR ) );
	}
}
