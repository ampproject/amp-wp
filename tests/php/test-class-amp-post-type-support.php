<?php
/**
 * Tests for Post Types Support.
 *
 * @package AMP
 * @since 0.6
 */

use AmpProject\AmpWP\Option;

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
		foreach ( [ 'book', 'poem', 'secret' ] as $post_type ) {
			unregister_post_type( $post_type );
		}
	}

	/**
	 * Test get_eligible_post_types.
	 *
	 * @covers AMP_Post_Type_Support::get_eligible_post_types()
	 */
	public function test_get_eligible_post_types() {
		register_post_type(
			'book',
			[
				'label'  => 'Book',
				'public' => true,
			]
		);
		register_post_type(
			'secret',
			[
				'label'  => 'Secret',
				'public' => false,
			]
		);

		$this->assertEqualSets(
			[
				'post',
				'page',
				'attachment',
				'book',
			],
			AMP_Post_Type_Support::get_eligible_post_types()
		);

		add_filter(
			'amp_supportable_post_types',
			static function ( $post_types ) {
				$post_types[] = 'secret';
				return array_diff( $post_types, [ 'attachment' ] );
			}
		);

		$this->assertEqualSets(
			[
				'post',
				'page',
				'secret',
				'book',
			],
			AMP_Post_Type_Support::get_eligible_post_types()
		);
	}

	/**
	 * Return an error code if a given post does not have AMP support.
	 *
	 * @covers AMP_Post_Type_Support::get_support_errors()
	 */
	public function test_get_support_error() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		register_post_type(
			'book',
			[
				'label'  => 'Book',
				'public' => true,
			]
		);

		// Post type support.
		$book_id = self::factory()->post->create( [ 'post_type' => 'book' ] );
		$this->assertEquals( [ 'post-type-support' ], AMP_Post_Type_Support::get_support_errors( $book_id ) );
		$supported_post_types = array_merge(
			AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES ),
			[ 'book' ]
		);
		AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, $supported_post_types );
		$this->assertEmpty( AMP_Post_Type_Support::get_support_errors( $book_id ) );

		// Skip-post.
		add_filter( 'amp_skip_post', '__return_true' );
		$this->assertEquals( [ 'skip-post' ], AMP_Post_Type_Support::get_support_errors( $book_id ) );
		remove_filter( 'amp_skip_post', '__return_true' );
		$this->assertEmpty( AMP_Post_Type_Support::get_support_errors( $book_id ) );

		// Invalid post.
		$this->assertEquals( [ 'invalid-post' ], AMP_Post_Type_Support::get_support_errors( null ) );
	}
}
