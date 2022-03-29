<?php
/**
 * Tests for Post Types Support.
 *
 * @package AMP
 * @since 0.6
 */

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for Post Type Support.
 *
 * @covers AMP_Post_Type_Support
 */
class Test_AMP_Post_Type_Support extends TestCase {

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tear_down() {
		foreach ( [ 'book', 'poem', 'secret', 'car', 'secret_book', 'non_amp_book' ] as $post_type ) {
			unregister_post_type( $post_type );
		}
		parent::tear_down();
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
				'label'              => 'Book',
				'publicly_queryable' => true,
			]
		);
		register_post_type(
			'car',
			[
				'label'              => 'Car',
				'public'             => false,
				'publicly_queryable' => true,
			]
		);
		register_post_type(
			'secret',
			[
				'label'              => 'Secret',
				'publicly_queryable' => false,
			]
		);
		register_post_type(
			'secret_book',
			[
				'label'              => 'Secret book',
				'public'             => true,
				'publicly_queryable' => false,
			]
		);

		$this->assertEqualSets(
			[
				'post',
				'page',
				'car',
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
				'car',
				'secret',
				'book',
			],
			AMP_Post_Type_Support::get_eligible_post_types()
		);

	}

	/**
	 * Test get_eligible_post_types.
	 *
	 * @covers AMP_Post_Type_Support::get_eligible_post_types()
	 */
	public function test_get_eligible_post_types_with_filter() {

		if ( version_compare( get_bloginfo( 'version' ), '5.9', '<' ) ) {
			$this->markTestSkipped( 'Requires WordPress 5.9 or greater than that.' );
		}

		register_post_type(
			'non_amp_book',
			[
				'label'  => 'Non AMP book',
				'public' => true,
			]
		);
		$is_post_type_viewable_callback = static function ( $is_viewable, $post_type ) {

			return 'non_amp_book' === $post_type->name ? false : $is_viewable;
		};

		add_filter( 'is_post_type_viewable', $is_post_type_viewable_callback, 10, 2 );

		$this->assertNotContains( 'non_amp_book', AMP_Post_Type_Support::get_eligible_post_types() );

		remove_filter( 'is_post_type_viewable', $is_post_type_viewable_callback );
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
