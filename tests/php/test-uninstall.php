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

	public function setUp() {
		parent::setUp();
		$this->was_using_ext_object_cache = wp_using_ext_object_cache();
		require_once AMP__DIR__ . '/includes/uninstall-functions.php';
	}

	public function tearDown() {
		parent::tearDown();
		wp_using_ext_object_cache( $this->was_using_ext_object_cache );
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

	/**
	 * @covers \AmpProject\AmpWP\delete_posts()
	 */
	public function test_delete_posts() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers \AmpProject\AmpWP\delete_terms()
	 */
	public function test_delete_terms() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers \AmpProject\AmpWP\delete_transients()
	 */
	public function test_delete_transients() {
		$this->markTestIncomplete();
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

		require AMP__DIR__ . '/uninstall.php';

		$this->flush_cache();

		$this->assertNotEmpty( get_option( AMP_Options_Manager::OPTION_NAME, false ) );

		// Test 2: With option to keep AMP data OFF.
		AMP_Options_Manager::update_option( Option::DELETE_DATA_AT_UNINSTALL, true );

		require AMP__DIR__ . '/uninstall.php';

		$this->flush_cache();

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
