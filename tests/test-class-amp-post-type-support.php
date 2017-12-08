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

	/**
	 * Return an error code if a given post does not have AMP support.
	 *
	 * @covers AMP_Post_Type_Support::get_support_errors()
	 */
	public function test_get_support_error() {
		register_post_type( 'book', array(
			'label'  => 'Book',
			'public' => true,
		) );

		// Post type support.
		$book_id = $this->factory()->post->create( array( 'post_type' => 'book' ) );
		$this->assertEquals( array( 'post-type-support' ), AMP_Post_Type_Support::get_support_errors( $book_id ) );
		add_post_type_support( 'book', AMP_QUERY_VAR );
		$this->assertEmpty( AMP_Post_Type_Support::get_support_errors( $book_id ) );

		// Disabled.
		update_post_meta( $book_id, AMP_Post_Meta_Box::DISABLED_POST_META_KEY, true );
		$this->assertEquals( array( 'post-disabled' ), AMP_Post_Type_Support::get_support_errors( $book_id ) );
		delete_post_meta( $book_id, AMP_Post_Meta_Box::DISABLED_POST_META_KEY );
		$this->assertEmpty( AMP_Post_Type_Support::get_support_errors( $book_id ) );

		// Password-protected.
		add_filter( 'post_password_required', '__return_true' );
		$this->assertEquals( array( 'password-protected' ), AMP_Post_Type_Support::get_support_errors( $book_id ) );
		remove_filter( 'post_password_required', '__return_true' );
		$this->assertEmpty( AMP_Post_Type_Support::get_support_errors( $book_id ) );

		// Skip-post.
		add_filter( 'amp_skip_post', '__return_true' );
		$this->assertEquals( array( 'skip-post' ), AMP_Post_Type_Support::get_support_errors( $book_id ) );
		remove_filter( 'amp_skip_post', '__return_true' );
		$this->assertEmpty( AMP_Post_Type_Support::get_support_errors( $book_id ) );

		// Page for posts and show on front.
		$page_id = $this->factory()->post->create( array( 'post_type' => 'page' ) );
		$this->assertEmpty( AMP_Post_Type_Support::get_support_errors( $page_id ) );
		update_option( 'show_on_front', 'page' );
		$this->assertEmpty( AMP_Post_Type_Support::get_support_errors( $page_id ) );
		update_option( 'page_for_posts', $page_id );
		$this->assertEquals( array( 'page-for-posts' ), AMP_Post_Type_Support::get_support_errors( $page_id ) );
		update_option( 'page_for_posts', '' );
		update_option( 'page_on_front', $page_id );
		$this->assertEquals( array( 'page-on-front' ), AMP_Post_Type_Support::get_support_errors( $page_id ) );
		update_option( 'show_on_front', 'posts' );
		$this->assertEmpty( AMP_Post_Type_Support::get_support_errors( $page_id ) );
	}
}
