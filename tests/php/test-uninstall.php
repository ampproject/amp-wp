<?php
/**
 * Test cases for uninstall.php
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\UserRESTEndpointExtension;
use AmpProject\AmpWP\BackgroundTask\MonitorCssTransientCaching;
use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\AmpWP\Validation\URLValidationCron;
use AmpProject\AmpWP\DevTools\BlockSources;

use function \AmpProject\AmpWP\delete_options;
use function \AmpProject\AmpWP\delete_user_metadata;
use function \AmpProject\AmpWP\delete_posts;
use function \AmpProject\AmpWP\delete_terms;
use function \AmpProject\AmpWP\delete_transients;

/**
 * @runInSeparateProcess
 * @group uninstall
 */
class Test_Uninstall extends TestCase {

	/** @var bool */
	private $was_using_ext_object_cache;

	public function set_up() {
		parent::set_up();
		$this->was_using_ext_object_cache = wp_using_ext_object_cache();
		require_once AMP__DIR__ . '/includes/uninstall-functions.php';
	}

	public function tear_down() {
		wp_using_ext_object_cache( $this->was_using_ext_object_cache );
		parent::tear_down();
	}

	/**
	 * @covers \AmpProject\AmpWP\delete_options()
	 */
	public function test_delete_options() {
		global $wpdb;

		$reader_theme = 'foo';

		// Non-AMP options.
		$blog_name = 'Sample Blog Name';
		update_option( 'blogname', $blog_name );
		set_theme_mod( 'color', 'blue' );

		// AMP options.
		AMP_Options_Manager::update_options(
			[
				Option::THEME_SUPPORT => AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				Option::READER_THEME  => $reader_theme,
			]
		);
		update_option( MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY, [] );
		update_option( URLValidationCron::OPTION_KEY, [] );
		update_option(
			"theme_mods_{$reader_theme}",
			[
				'color' => 'red',
				AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY => [
					'color' => time(),
				],
			]
		);

		$options_before_delete = $wpdb->get_col( "SELECT option_name FROM $wpdb->options" );

		delete_options();
		$this->flush_cache();

		$this->assertEquals( $blog_name, get_option( 'blogname' ) );
		$this->assertEquals( 'blue', get_theme_mod( 'color' ) );

		$this->assertFalse( get_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ) );
		$foo_theme_mods = get_option( 'theme_mods_foo' );
		$this->assertEquals( 'red', $foo_theme_mods['color'] );
		$this->assertArrayNotHasKey( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY, $foo_theme_mods );

		$options_after_delete = $wpdb->get_col( "SELECT option_name FROM $wpdb->options" );
		$this->assertEqualSets(
			[
				AMP_Options_Manager::OPTION_NAME,
				URLValidationCron::OPTION_KEY,
				MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY,
			],
			array_diff( $options_before_delete, $options_after_delete ),
			'Expected only 3 options to have been deleted.'
		);
	}

	/**
	 * @covers \AmpProject\AmpWP\delete_user_metadata()
	 */
	public function test_delete_user_metadata() {
		global $wpdb;

		$user_ids = self::factory()->user->create_many( 3 );
		foreach ( $user_ids as $user_id ) {
			update_user_meta( $user_id, UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED, '...' );
			update_user_meta( $user_id, UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE, '...' );
			update_user_meta( $user_id, 'additional_user_meta', '...' );
		}

		$user_meta_keys_before_delete = $wpdb->get_col( "SELECT meta_key FROM $wpdb->usermeta" );

		delete_user_metadata();
		$this->flush_cache();

		foreach ( $user_ids as $user_id ) {
			$this->assertEmpty( get_user_meta( $user_id, UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED, true ) );
			$this->assertEmpty( get_user_meta( $user_id, UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE, true ) );
			$this->assertEquals( '...', get_user_meta( $user_id, 'additional_user_meta', true ) );
		}

		$user_meta_keys_after_delete = $wpdb->get_col( "SELECT meta_key FROM $wpdb->usermeta" );
		$this->assertEqualSets(
			[
				UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED,
				UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE,
			],
			array_unique( array_diff( $user_meta_keys_before_delete, $user_meta_keys_after_delete ) )
		);
	}

	/** @return array */
	private function create_data() {

		$validated_url_post_ids = [];

		$meta_input = [
			'public_key'   => 'public value',
			'_private_key' => 'private value',
		];

		$post_ids = self::factory()->post->create_many(
			3,
			[
				'post_type'  => 'post',
				'meta_input' => $meta_input,
			]
		);

		$post_tag_terms = self::factory()->term->create_many(
			5,
			[
				'taxonomy' => 'post_tag',
			]
		);
		$category_terms = self::factory()->term->create_many(
			5,
			[
				'taxonomy' => 'category',
			]
		);
		foreach ( array_merge( $post_tag_terms, $category_terms ) as $term_id ) {
			foreach ( $meta_input as $key => $value ) {
				update_term_meta( $term_id, $key, $value );
			}
		}

		foreach ( $post_ids as $post_id ) {
			wp_set_object_terms( $post_id, $post_tag_terms, 'post_tag' );
			wp_set_object_terms( $post_id, $category_terms, 'category' );

			$validated_url_post_ids[] = AMP_Validated_URL_Post_Type::store_validation_errors(
				[
					[ 'code' => 'foo' ],
					[ 'code' => 'bar' ],
					[ 'code' => 'baz' ],
				],
				get_permalink( $post_id ),
				[
					'queried_object'  => [
						'type' => 'post',
						'id'   => $post_id,
					],
					'php_fatal_error' => [
						'message' => 'Bad',
						'file'    => __FILE__,
						'line'    => __LINE__,
					],
					'stylesheets'     => [
						[ '...' ],
					],
				]
			);
		}

		$page_ids = self::factory()->post->create_many(
			3,
			[
				'post_type'  => 'page',
				'meta_input' => $meta_input,
			]
		);
		foreach ( $page_ids as $page_id ) {
			$validated_url_post_ids[] = AMP_Validated_URL_Post_Type::store_validation_errors(
				[
					[ 'code' => 'a' ],
					[ 'code' => 'b' ],
					[ 'code' => 'c' ],
				],
				get_permalink( $page_id ),
				[
					'queried_object' => [
						'type' => 'post',
						'id'   => $page_id,
					],
					'stylesheets'    => [
						[ '...' ],
					],
				]
			);
		}

		return compact(
			'post_ids',
			'page_ids',
			'validated_url_post_ids',
			'post_tag_terms',
			'category_terms',
			'meta_input'
		);
	}

	/**
	 * @covers \AmpProject\AmpWP\delete_posts()
	 */
	public function test_delete_posts() {
		global $wpdb;

		$data = $this->create_data();

		$post_types_before_delete     = $wpdb->get_col( "SELECT post_type FROM $wpdb->posts" );
		$post_meta_keys_before_delete = $wpdb->get_col( "SELECT meta_key FROM $wpdb->postmeta" );

		delete_posts();
		$this->flush_cache();

		foreach ( $data['post_ids'] as $post_id ) {
			$this->assertInstanceOf( WP_Post::class, get_post( $post_id ) );
			foreach ( $data['meta_input'] as $key => $value ) {
				$this->assertEquals( $value, get_post_meta( $post_id, $key, true ) );
			}
		}
		foreach ( $data['page_ids'] as $page_id ) {
			$this->assertInstanceOf( WP_Post::class, get_post( $page_id ) );
			foreach ( $data['meta_input'] as $key => $value ) {
				$this->assertEquals( $value, get_post_meta( $page_id, $key, true ) );
			}
		}
		foreach ( $data['validated_url_post_ids'] as $id ) {
			$this->assertEmpty( get_post( $id ) );
		}

		$post_types_after_delete     = $wpdb->get_col( "SELECT post_type FROM $wpdb->posts" );
		$post_meta_keys_after_delete = $wpdb->get_col( "SELECT meta_key FROM $wpdb->postmeta" );

		$this->assertEqualSets(
			[
				AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
			],
			array_unique( array_diff( $post_types_before_delete, $post_types_after_delete ) )
		);
		$this->assertContains( 'post', $post_types_after_delete );
		$this->assertContains( 'page', $post_types_after_delete );

		$this->assertEqualSets(
			[
				AMP_Validated_URL_Post_Type::PHP_FATAL_ERROR_POST_META_KEY,
				AMP_Validated_URL_Post_Type::QUERIED_OBJECT_POST_META_KEY,
				AMP_Validated_URL_Post_Type::STYLESHEETS_POST_META_KEY,
				AMP_Validated_URL_Post_Type::VALIDATED_ENVIRONMENT_POST_META_KEY,
			],
			array_unique( array_diff( $post_meta_keys_before_delete, $post_meta_keys_after_delete ) )
		);
		foreach ( array_keys( $data['meta_input'] ) as $meta_key ) {
			$this->assertContains( $meta_key, $post_meta_keys_after_delete );
		}
	}

	/**
	 * @covers \AmpProject\AmpWP\delete_terms()
	 */
	public function test_delete_terms() {
		global $wpdb;

		$data = $this->create_data();

		$term_meta_keys_before_delete  = $wpdb->get_col( "SELECT meta_key FROM $wpdb->termmeta" );
		$term_taxonomies_before_delete = $wpdb->get_col( "SELECT taxonomy FROM $wpdb->term_taxonomy" );
		$term_slugs_before_delete      = $wpdb->get_col( "SELECT slug FROM $wpdb->terms" );

		$validation_error_terms = get_terms(
			[
				'taxonomy'   => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
				'hide_empty' => false,
			]
		);
		$this->assertGreaterThan( 0, count( $validation_error_terms ) );

		delete_terms();
		$this->flush_cache();

		foreach ( $data['post_ids'] as $post_id ) {
			$this->assertInstanceOf( WP_Post::class, get_post( $post_id ) );
			$this->assertEqualSets(
				$data['post_tag_terms'],
				wp_list_pluck( wp_get_post_terms( $post_id, 'post_tag' ), 'term_id' )
			);
			$this->assertEqualSets(
				$data['category_terms'],
				wp_list_pluck( wp_get_post_terms( $post_id, 'category' ), 'term_id' )
			);
		}

		foreach ( $validation_error_terms as $term ) {
			$this->assertEmpty( get_term( $term->term_id ) );
		}
		foreach ( $data['post_tag_terms'] as $term_id ) {
			$this->assertInstanceOf( WP_Term::class, get_term( $term_id ) );
			foreach ( $data['meta_input'] as $key => $value ) {
				$this->assertEquals( $value, get_term_meta( $term_id, $key, true ) );
			}
		}
		foreach ( $data['category_terms'] as $term_id ) {
			$this->assertInstanceOf( WP_Term::class, get_term( $term_id ) );
			foreach ( $data['meta_input'] as $key => $value ) {
				$this->assertEquals( $value, get_term_meta( $term_id, $key, true ) );
			}
		}

		$term_meta_keys_after_delete = $wpdb->get_col( "SELECT meta_key FROM $wpdb->termmeta" );
		$this->assertEqualSets(
			[
				'created_date_gmt',
			],
			array_unique( array_diff( $term_meta_keys_before_delete, $term_meta_keys_after_delete ) )
		);
		foreach ( array_keys( $data['meta_input'] ) as $key ) {
			$this->assertContains( $key, $term_meta_keys_after_delete );
		}

		$term_taxonomies_after_delete = $wpdb->get_col( "SELECT taxonomy FROM $wpdb->term_taxonomy" );
		$this->assertEqualSets(
			[
				AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
			],
			array_unique( array_diff( $term_taxonomies_before_delete, $term_taxonomies_after_delete ) )
		);
		$this->assertContains( 'post_tag', $term_taxonomies_after_delete );
		$this->assertContains( 'category', $term_taxonomies_after_delete );

		$term_slugs_after_delete = $wpdb->get_col( "SELECT slug FROM $wpdb->terms" );
		foreach ( array_diff( $term_slugs_before_delete, $term_slugs_after_delete ) as $deleted_slug ) {
			$this->assertMatchesRegularExpression( '/^[0-9a-f]{32}$/', $deleted_slug );
		}
		$this->assertContains( 'uncategorized', $term_slugs_after_delete );

		$this->assertCount(
			0,
			get_terms(
				[
					'taxonomy'   => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
					'hide_empty' => false,
				]
			)
		);

		$term_ids_after_delete = $wpdb->get_col( "SELECT term_id FROM $wpdb->terms" );
		foreach ( $validation_error_terms as $validation_error_term ) {
			$this->assertNotContains( $validation_error_term->term_id, $term_ids_after_delete );
		}
		foreach ( $term_ids_after_delete as $term_id ) {
			$term = get_term( $term_id );
			$this->assertInstanceOf( WP_Term::class, $term );
			$this->assertNotEquals( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, $term->taxonomy );
			$this->assertTrue( taxonomy_exists( $term->taxonomy ) );
			if ( 'uncategorized' !== $term->slug ) {
				$objects = get_objects_in_term( $term_id, [ 'post_tag', 'category' ] );
				$this->assertNotEmpty( $objects );
			}
		}
	}

	/** @return array */
	public function get_data_to_test_delete_transients() {
		return [
			'using_ext_object_cache'     => [ true ],
			'not_using_ext_object_cache' => [ false ],
		];
	}

	/**
	 * @dataProvider get_data_to_test_delete_transients
	 * @covers \AmpProject\AmpWP\delete_transients()
	 */
	public function test_delete_transients( $using_ext_object_cache ) {
		global $wpdb;

		wp_using_ext_object_cache( $using_ext_object_cache );

		// Non-AMP transients.
		set_transient( 'foo', 1 );
		set_transient( 'bar', 2, MINUTE_IN_SECONDS );
		set_transient( 'baz', 3, HOUR_IN_SECONDS );

		// AMP transients.
		set_transient( BlockSources::class . BlockSources::CACHE_KEY, '...', BlockSources::CACHE_TIMEOUT );
		set_transient( 'amp-parsed-stylesheet-v10-1', [ '...' ], MONTH_IN_SECONDS );
		set_transient( 'amp-parsed-stylesheet-v10-2', [ '...' ], MONTH_IN_SECONDS );
		set_transient( 'amp-parsed-stylesheet-v10-3', [ '...' ], MONTH_IN_SECONDS );
		set_transient( 'amp-parsed-stylesheet-v10-3', [ '...' ], MONTH_IN_SECONDS );
		set_transient( 'amp_error_index_counts', '...' );
		set_transient( 'amp_has_page_caching', '...' );
		set_transient( 'amp_img_123abc', '...' );
		set_transient( 'amp_lock_123abc', '...' );
		set_transient( 'amp_new_validation_error_urls_count', '...' );
		set_transient( 'amp_plugin_activation_validation_errors', '...' );
		set_transient( 'amp_remote_request_101623f47561580a914e5d56e153cf6c', '...' );
		set_transient( 'amp_themes_wporg', '...', DAY_IN_SECONDS );

		$transient_keys_before_delete = $wpdb->get_col( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_%'" );
		$num_queries_before           = $wpdb->num_queries;
		delete_transients();
		$num_queries_after           = $wpdb->num_queries;
		$transient_keys_after_delete = $wpdb->get_col( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_%'" );

		if ( $using_ext_object_cache ) {
			$this->assertEquals( $num_queries_before, $num_queries_after );
			$this->assertEquals( $transient_keys_before_delete, $transient_keys_after_delete );
		} else {
			$this->assertEquals( $num_queries_before + 1, $num_queries_after );
			$this->assertContains(
				'_transient_amp_themes_wporg',
				$transient_keys_before_delete
			);
			$this->assertContains(
				'_transient_timeout_amp_themes_wporg',
				$transient_keys_before_delete
			);
			$this->assertEqualSets(
				[
					'_transient_bar',
					'_transient_baz',
					'_transient_foo',
					'_transient_timeout_bar',
					'_transient_timeout_baz',
				],
				$transient_keys_after_delete
			);
		}
	}

	/**
	 * @covers \AmpProject\AmpWP\delete_options()
	 * @covers \AmpProject\AmpWP\delete_user_metadata()
	 * @covers \AmpProject\AmpWP\delete_posts()
	 * @covers \AmpProject\AmpWP\delete_terms()
	 * @covers \AmpProject\AmpWP\delete_transients()
	 * @covers \AmpProject\AmpWP\remove_plugin_data()
	 */
	public function test_remove_plugin_data() {
		global $wpdb;
		wp_using_ext_object_cache( false );

		// Create dummy data.
		$blog_name  = 'Sample Blog Name';
		$meta_key   = 'amp_meta_key';
		$meta_value = 'amp_meta_value';

		update_option( 'blogname', $blog_name );
		update_option(
			'amp-options',
			[ 'reader_theme' => 'foo' ]
		);

		$users = $this->factory()->user->create_many( 2, [ 'role' => 'administrator' ] );

		foreach ( $users as $user ) {
			update_user_meta( $user, 'amp_dev_tools_enabled', 'Yes' );
			update_user_meta( $user, 'amp_review_panel_dismissed_for_template_mode', 'Yes' );
			update_user_meta( $user, 'additional_user_meta', 'Yes' );
		}

		$amp_validated_post = $this->factory()->post->create_and_get(
			[
				'post_type'  => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
				'meta_input' => [
					$meta_key => $meta_value,
				],
			]
		);

		$page_post = $this->factory()->post->create_and_get(
			[
				'post_type'  => 'page',
				'meta_input' => [
					$meta_key => $meta_value,
				],
			]
		);

		$amp_error_term = $this->factory()->term->create_and_get(
			[
				'taxonomy' => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
			]
		);

		update_term_meta( $amp_error_term->term_id, $meta_key, $meta_value );
		wp_add_object_terms( $amp_validated_post->ID, $amp_error_term->term_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );

		$post_tag_term = $this->factory()->term->create_and_get(
			[
				'taxonomy' => 'post_tag',
			]
		);
		update_term_meta( $post_tag_term->term_id, $meta_key, $meta_value );
		wp_add_object_terms( $page_post->ID, $post_tag_term->term_id, 'post_tag' );
		$post_tag_term_taxonomy_row_count = $wpdb->query( "SELECT * FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'post_tag';" );
		$this->assertGreaterThan( 0, $post_tag_term_taxonomy_row_count );
		$post_tag_term_relationships_row_count = $wpdb->query( $wpdb->prepare( "SELECT * FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d", $post_tag_term->term_id ) );
		$this->assertGreaterThan( 0, $post_tag_term_relationships_row_count );

		set_theme_mod( 'color', 'blue' );
		set_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY, [ 'color' => time() ] );
		update_option(
			'theme_mods_foo',
			[
				'color' => 'red',
				AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY => [
					'color' => time(),
				],
			]
		);

		$transient_groups_to_remove = [
			'amp-parsed-stylesheet-v...',
			'amp_img_...',
			'amp_new_validation_error_urls_count',
			'amp_error_index_counts',
			'amp_plugin_activation_validation_errors',
			'amp_themes_wporg',
			'amp_lock_...',
		];

		foreach ( $transient_groups_to_remove as $transient_group ) {
			set_transient( $transient_group, 'Sample value', 10000 );
		}

		set_transient( 'amp_sample_group', 'AMP Sample value', 10000 );

		// Mock uninstall const.
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'Yes' );
		}

		// Test 1: With option to keep AMP data ON.
		AMP_Options_Manager::update_option( Option::DELETE_DATA_AT_UNINSTALL, false );

		$num_queries_before = $wpdb->num_queries;
		require AMP__DIR__ . '/uninstall.php';
		$this->flush_cache();
		$this->assertEquals( $num_queries_before, $wpdb->num_queries );

		$this->assertNotEmpty( get_option( AMP_Options_Manager::OPTION_NAME, false ) );

		// Test 2: With option to keep AMP data OFF.
		AMP_Options_Manager::update_option( Option::DELETE_DATA_AT_UNINSTALL, true );

		$num_queries_before = $wpdb->num_queries;
		require AMP__DIR__ . '/uninstall.php';
		$this->flush_cache();
		$this->assertGreaterThan( $num_queries_before, $wpdb->num_queries );

		// Assert that AMP related data does get deleted.
		$this->assertEmpty( get_option( AMP_Options_Manager::OPTION_NAME, false ) );

		$this->assertEmpty( get_post( $amp_validated_post->ID ) );
		$this->assertEmpty( get_post_meta( $amp_validated_post->ID, $meta_key, true ) );

		$this->assertEmpty( get_term( $amp_error_term->term_id ) );
		$this->assertEmpty( get_term_meta( $amp_error_term->term_id, $meta_key, true ) );

		// Assert that there is no data left for `amp_validation_error` taxonomy.
		$this->assertEmpty(
			$wpdb->query(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s;",
					AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG
				)
			)
		);
		$this->assertEmpty(
			$wpdb->query(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d",
					$amp_error_term->term_id
				)
			)
		);
		$this->assertEmpty(
			$wpdb->query(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->terms} WHERE term_id = %d",
					$amp_error_term->term_id
				)
			)
		);

		// Assert that other than AMP related data does not get deleted.
		$this->assertInstanceOf( WP_Post::class, get_post( $page_post->ID ) );
		$this->assertEquals( $meta_value, get_post_meta( $page_post->ID, $meta_key, true ) );
		$this->assertInstanceOf( WP_Term::class, get_term( $post_tag_term->term_id ) );
		$this->assertEquals( $meta_value, get_term_meta( $post_tag_term->term_id, $meta_key, true ) );

		// Assert that there is no deleted other than `amp_validation_error` taxonomy.
		$this->assertEquals(
			$post_tag_term_taxonomy_row_count,
			$wpdb->query( "SELECT * FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'post_tag';" )
		);
		$this->assertEquals(
			$post_tag_term_relationships_row_count,
			$wpdb->query(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d",
					$post_tag_term->term_id
				)
			)
		);
		$this->assertEquals(
			1,
			$wpdb->query(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->terms} WHERE term_id = %d",
					$post_tag_term->term_id
				)
			)
		);

		$this->assertEquals( $blog_name, get_option( 'blogname', false ) );

		$this->assertEquals( 'blue', get_theme_mod( 'color' ) );
		$this->assertFalse( get_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ) );
		$foo_theme_mods = get_option( 'theme_mods_foo' );
		$this->assertEquals( 'red', $foo_theme_mods['color'] );
		$this->assertArrayNotHasKey( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY, $foo_theme_mods );

		foreach ( $transient_groups_to_remove as $transient_group ) {
			$this->assertEmpty( get_transient( $transient_group ) );
		}

		$this->assertEquals( 'AMP Sample value', get_transient( 'amp_sample_group' ) );

		foreach ( $users as $user ) {
			$this->assertEmpty( get_user_meta( $user, 'amp_dev_tools_enabled', true ) );
			$this->assertEmpty( get_user_meta( $user, 'amp_review_panel_dismissed_for_template_mode', true ) );
			$this->assertEquals( 'Yes', get_user_meta( $user, 'additional_user_meta', true ) );
		}
	}
}
