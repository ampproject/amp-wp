<?php
/**
 * Tests for AMP_Options_Manager.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\Helpers\LoadsCoreThemes;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for AMP_Options_Manager.
 *
 * @covers AMP_Options_Manager
 */
class Test_AMP_Options_Manager extends TestCase {

	use LoadsCoreThemes;

	/**
	 * Whether the external object cache was enabled.
	 *
	 * @var bool
	 */
	private $was_wp_using_ext_object_cache;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		$this->was_wp_using_ext_object_cache = $GLOBALS['_wp_using_ext_object_cache'];
		delete_option( AMP_Options_Manager::OPTION_NAME ); // Make sure default reader mode option does not override theme support being added.
		remove_theme_support( 'amp' );
		$GLOBALS['wp_settings_errors'] = [];

		$this->register_core_themes();
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		parent::tearDown();
		$GLOBALS['_wp_using_ext_object_cache'] = $this->was_wp_using_ext_object_cache;
		unregister_post_type( 'foo' );
		unregister_post_type( 'book' );

		global $current_screen;
		$current_screen = null;

		foreach ( get_post_types() as $post_type ) {
			remove_post_type_support( $post_type, 'amp' );
		}

		$this->restore_theme_directories();
		$GLOBALS['wp_the_query'] = $GLOBALS['wp_query']; // This is missing in core.
	}

	/**
	 * Test constants.
	 */
	public function test_constants() {
		$this->assertEquals( 'amp-options', AMP_Options_Manager::OPTION_NAME );
	}

	/**
	 * Tests the init method.
	 *
	 * @covers AMP_Options_Manager::init()
	 */
	public function test_init() {
		AMP_Options_Manager::init();
		$this->assertEquals( 10, has_action( 'admin_notices', [ AMP_Options_Manager::class, 'render_php_css_parser_conflict_notice' ] ) );
		$this->assertEquals( 10, has_action( 'admin_notices', [ AMP_Options_Manager::class, 'insecure_connection_notice' ] ) );
		$this->assertEquals( 10, has_action( 'admin_notices', [ AMP_Options_Manager::class, 'reader_theme_fallback_notice' ] ) );
	}

	/**
	 * Test register_settings.
	 *
	 * @covers AMP_Options_Manager::register_settings()
	 */
	public function test_register_settings() {
		AMP_Options_Manager::register_settings();
		AMP_Options_Manager::init();
		$registered_settings = get_registered_settings();
		$this->assertArrayHasKey( AMP_Options_Manager::OPTION_NAME, $registered_settings );
		$this->assertEquals( 'array', $registered_settings[ AMP_Options_Manager::OPTION_NAME ]['type'] );
	}

	/**
	 * Test get_options.
	 *
	 * @covers AMP_Options_Manager::get_options()
	 * @covers AMP_Options_Manager::get_option()
	 * @covers AMP_Options_Manager::update_option()
	 * @covers AMP_Options_Manager::validate_options()
	 */
	public function test_get_and_set_options() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		wp_using_ext_object_cache( true ); // turn on external object cache flag.
		AMP_Options_Manager::register_settings(); // Adds validate_options as filter.
		delete_option( AMP_Options_Manager::OPTION_NAME );
		$this->assertEquals(
			[
				Option::THEME_SUPPORT           => AMP_Theme_Support::READER_MODE_SLUG,
				Option::SUPPORTED_POST_TYPES    => [ 'post', 'page' ],
				Option::ANALYTICS               => [],
				Option::ALL_TEMPLATES_SUPPORTED => true,
				Option::SUPPORTED_TEMPLATES     => [ 'is_singular' ],
				Option::SUPPRESSED_PLUGINS      => [],
				Option::VERSION                 => AMP__VERSION,
				Option::MOBILE_REDIRECT         => true,
				Option::READER_THEME            => 'legacy',
				Option::PLUGIN_CONFIGURED       => false,
				Option::PAIRED_URL_STRUCTURE    => Option::PAIRED_URL_STRUCTURE_QUERY_VAR,
				Option::LATE_DEFINED_SLUG       => null,
			],
			AMP_Options_Manager::get_options()
		);
		$this->assertSame( false, AMP_Options_Manager::get_option( 'foo' ) );
		$this->assertSame( 'default', AMP_Options_Manager::get_option( 'foo', 'default' ) );

		// Test supported_post_types validation.
		AMP_Options_Manager::update_option(
			Option::SUPPORTED_POST_TYPES,
			[ 'post' ]
		);
		$this->assertSame(
			[ 'post' ],
			AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES )
		);

		// Test supported_templates validation.
		AMP_Options_Manager::update_option(
			Option::SUPPORTED_TEMPLATES,
			[
				'is_search',
				'is_category',
			]
		);
		$this->assertSame(
			[
				'is_search',
				'is_category',
			],
			AMP_Options_Manager::get_option( Option::SUPPORTED_TEMPLATES )
		);

		// Test analytics validation with missing fields.
		AMP_Options_Manager::update_option(
			Option::ANALYTICS,
			[
				'bad' => [],
			]
		);
		$this->assertEmpty( AMP_Options_Manager::get_option( Option::ANALYTICS ) );

		// Test bad analytics JSON entries are skipped.
		AMP_Options_Manager::update_option(
			Option::ANALYTICS,
			[
				'abcdefghijkl' => [
					'type'   => 'foo',
					'config' => '{"good":true}',
				],
				'mnopqrstuvwx' => [
					'type'   => 'bar',
					'config' => '{"bad":true',
				],
				'mshvad9sdasa' => [
					'type' => 'baz',
				],
			]
		);
		$updated_entries = AMP_Options_Manager::get_option( Option::ANALYTICS );
		$this->assertEquals(
			[
				'abcdefghijkl' => [
					'type'   => 'foo',
					'config' => '{"good":true}',
				],
			],
			$updated_entries
		);

		// Confirm format of entry ID.
		$entries = AMP_Options_Manager::get_option( Option::ANALYTICS );
		$id      = current( array_keys( $entries ) );
		$this->assertArrayHasKey( $id, $entries );
		$this->assertEquals( 'foo', $entries[ $id ]['type'] );
		$this->assertEquals( '{"good":true}', $entries[ $id ]['config'] );

		// Confirm adding another entry works.
		AMP_Options_Manager::update_option(
			Option::ANALYTICS,
			[
				'abcdefghijkl' => [
					'type'   => 'foo',
					'config' => '{"good":true}',
				],
				'mnopqrstuvwx' => [
					'type'   => 'bar',
					'config' => '{"good":true}',
				],
			]
		);
		$entries = AMP_Options_Manager::get_option( Option::ANALYTICS );
		$this->assertCount( 2, AMP_Options_Manager::get_option( Option::ANALYTICS ) );
		$this->assertArrayHasKey( $id, $entries );

		// Confirm updating an entry works.
		AMP_Options_Manager::update_option(
			Option::ANALYTICS,
			[
				$id => [
					'id'     => $id,
					'type'   => 'foo',
					'config' => '{"very_good":true}',
				],
			]
		);
		$entries = AMP_Options_Manager::get_option( Option::ANALYTICS );
		$this->assertEquals( 'foo', $entries[ $id ]['type'] );
		$this->assertEquals( '{"very_good":true}', $entries[ $id ]['config'] );

		// Confirm deleting an entry works.
		AMP_Options_Manager::update_option(
			Option::ANALYTICS,
			[
				'new-entry' => [
					'type'   => 'bar',
					'config' => '{"good":true}',
				],
			]
		);
		$entries = AMP_Options_Manager::get_option( Option::ANALYTICS );
		$this->assertCount( 1, $entries );
		$this->assertArrayNotHasKey( $id, $entries );
	}

	/**
	 * Test get_options for toggling the default value of plugin_configured.
	 *
	 * @covers AMP_Options_Manager::get_option()
	 * @covers AMP_Options_Manager::get_options()
	 */
	public function test_get_options_changing_plugin_configured_default() {
		// Ensure plugin_configured is false when existing option is absent.
		delete_option( AMP_Options_Manager::OPTION_NAME );
		$this->assertFalse( AMP_Options_Manager::get_option( Option::PLUGIN_CONFIGURED ) );

		// Ensure plugin_configured is true when existing option is absent from an old version.
		update_option( AMP_Options_Manager::OPTION_NAME, [ Option::VERSION => '1.5.2' ] );
		$this->assertTrue( AMP_Options_Manager::get_option( Option::PLUGIN_CONFIGURED ) );

		// Ensure plugin_configured is true when explicitly set as such in the DB.
		update_option(
			AMP_Options_Manager::OPTION_NAME,
			[
				Option::VERSION           => AMP__VERSION,
				Option::PLUGIN_CONFIGURED => false,
			]
		);
		$this->assertFalse( AMP_Options_Manager::get_option( Option::PLUGIN_CONFIGURED ) );

		// Ensure plugin_configured is false when explicitly set as such in the DB.
		update_option(
			AMP_Options_Manager::OPTION_NAME,
			[
				Option::VERSION           => AMP__VERSION,
				Option::PLUGIN_CONFIGURED => true,
			]
		);
		$this->assertTrue( AMP_Options_Manager::get_option( Option::PLUGIN_CONFIGURED ) );
	}

	/** @return array */
	public function get_data_for_testing_get_options_default_template_mode() {
		return [
			'core_theme'    => [
				'twentytwenty',
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				null,
			],
			'child_of_core' => [
				'child-of-core',
				AMP_Theme_Support::READER_MODE_SLUG,
				null,
			],
			'custom_theme'  => [
				'twentytwenty',
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				[],
			],
		];
	}

	/**
	 * Test the expected default mode when various themes are active.
	 *
	 * @dataProvider get_data_for_testing_get_options_default_template_mode
	 *
	 * @covers AMP_Options_Manager::get_options()
	 * @param string     $theme               Theme.
	 * @param string     $expected_mode       Expected mode.
	 * @param null|array $added_theme_support Added theme support (or not if null).
	 */
	public function test_get_options_default_template_mode( $theme, $expected_mode, $added_theme_support ) {
		$theme_dir = basename( dirname( AMP__DIR__ ) ) . '/' . basename( AMP__DIR__ ) . '/tests/php/data/themes';
		register_theme_directory( $theme_dir );

		delete_option( AMP_Options_Manager::OPTION_NAME );
		remove_theme_support( 'amp' );
		switch_theme( $theme );
		if ( is_array( $added_theme_support ) ) {
			add_theme_support( 'amp', $added_theme_support );
		}
		AMP_Core_Theme_Sanitizer::extend_theme_support();
		$this->assertEquals( $expected_mode, AMP_Options_Manager::get_option( Option::THEME_SUPPORT ) );
	}

	/**
	 * Test get_options when supported_post_types option is list of post types and when post type support is added for default values.
	 *
	 * @covers AMP_Options_Manager::get_options()
	 */
	public function test_get_options_migration_supported_post_types_defaults() {
		foreach ( get_post_types() as $post_type ) {
			remove_post_type_support( $post_type, 'amp' );
		}

		register_post_type(
			'book',
			[
				'public'   => true,
				'supports' => [ 'amp' ],
			]
		);

		// Make sure the post type support get migrated.
		delete_option( AMP_Options_Manager::OPTION_NAME );
		$this->assertEquals(
			[
				'post', // Enabled by default.
				'page', // Enabled by default.
				'book',
			],
			AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES )
		);
	}

	/** @return array */
	public function get_data_for_testing_supported_post_types_options_migration() {
		return [
			'reader_with_all_templates'          => [
				[
					'theme_support'           => 'reader',
					'all_templates_supported' => true,
					'supported_post_types'    => [ 'post' ],
				],
				[ 'post', 'book' ],
			],
			'reader_without_all_templates'       => [
				[
					'theme_support'           => 'reader',
					'all_templates_supported' => false,
					'supported_post_types'    => [ 'post' ],
				],
				[ 'post', 'book' ],
			],
			'transitional__with_all_templates'   => [
				[
					'theme_support'           => 'transitional',
					'all_templates_supported' => true,
					'supported_post_types'    => [ 'post' ],
				],
				[ 'post', 'page', 'book', 'attachment' ],
			],
			'transitional_without_all_templates' => [
				[
					'theme_support'           => 'transitional',
					'all_templates_supported' => false,
					'supported_post_types'    => [ 'post' ],
				],
				[ 'post', 'book' ],
			],
		];
	}

	/**
	 * Test get_options when supported_post_types option is list of post types when upgrading from an old version.
	 *
	 * @dataProvider get_data_for_testing_supported_post_types_options_migration
	 *
	 * @param array $existing_options              Existing options.
	 * @param array $expected_supported_post_types Expected supported post types.
	 * @covers AMP_Options_Manager::get_options()
	 */
	public function test_get_options_migration_supported_post_types_from_upgrade( $existing_options, $expected_supported_post_types ) {
		global $wpdb;
		foreach ( get_post_types() as $post_type ) {
			remove_post_type_support( $post_type, 'amp' );
		}

		register_post_type(
			'book',
			[
				'public'   => true,
				'supports' => [ 'amp' ],
			]
		);

		delete_option( AMP_Options_Manager::OPTION_NAME );
		$wpdb->insert(
			$wpdb->options,
			[
				'option_name'  => AMP_Options_Manager::OPTION_NAME,
				'option_value' => serialize( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					array_merge(
						[
							'supported_templates' => [ 'is_singular' ],
							'version'             => '1.5.5',
						],
						$existing_options
					)
				),
			]
		);
		wp_cache_flush();

		$this->assertEqualSets(
			$expected_supported_post_types,
			array_unique( AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES ) )
		);
	}

	/**
	 * Test get_options when all_templates_supported theme support is used.
	 *
	 * @covers AMP_Options_Manager::get_options()
	 */
	public function test_get_options_migration_all_templates_supported_defaults() {
		delete_option( AMP_Options_Manager::OPTION_NAME );
		add_theme_support( 'amp', [ 'templates_supported' => 'all' ] );
		$this->assertTrue( AMP_Options_Manager::get_option( Option::ALL_TEMPLATES_SUPPORTED ) );

		delete_option( AMP_Options_Manager::OPTION_NAME );
		add_theme_support(
			'amp',
			[
				'templates_supported' => [
					'is_search'  => true,
					'is_archive' => false,
				],
			]
		);
		$this->assertFalse( AMP_Options_Manager::get_option( Option::ALL_TEMPLATES_SUPPORTED ) );
		$this->assertEquals(
			[
				'is_singular',
				'is_search',
			],
			AMP_Options_Manager::get_option( Option::SUPPORTED_TEMPLATES )
		);
	}

	/**
	 * Test that get_options() will migrate options properly when there is theme support and post type support flags.
	 *
	 * @covers AMP_Options_Manager::get_options()
	 */
	public function test_get_options_migration_from_old_version_selective_templates_forced() {
		$options = [
			'theme_support'           => 'transitional',
			'supported_post_types'    => [
				'post',
			],
			'analytics'               => [],
			'all_templates_supported' => false,
			'supported_templates'     => [
				'is_singular',
				'is_404',
				'is_category',
			],
			'version'                 => '1.5.5',
		];
		update_option( AMP_Options_Manager::OPTION_NAME, $options );

		$this->assertEquals( $options, get_option( AMP_Options_Manager::OPTION_NAME ) );

		add_post_type_support( 'page', 'amp' );
		add_theme_support(
			'amp',
			[
				'templates_supported' => [
					'is_singular' => true,
					'is_404'      => false,
					'is_date'     => true,
				],
			]
		);
		$migrated_options = AMP_Options_Manager::get_options();

		$this->assertFalse( $migrated_options[ Option::ALL_TEMPLATES_SUPPORTED ] );
		$this->assertEqualSets(
			[
				'is_singular',
				'is_date',
				'is_category',
			],
			array_unique( $migrated_options[ Option::SUPPORTED_TEMPLATES ] )
		);
		$this->assertEquals(
			[
				'post',
				'page',
			],
			$migrated_options[ Option::SUPPORTED_POST_TYPES ]
		);

		// Now verify that the templates_supported=>all theme support flag is also migrated.
		update_option( AMP_Options_Manager::OPTION_NAME, $options );
		add_theme_support(
			'amp',
			[ 'templates_supported' => 'all' ]
		);
		$migrated_options = AMP_Options_Manager::get_options();
		$this->assertTrue( $migrated_options[ Option::ALL_TEMPLATES_SUPPORTED ] );
		$this->assertEqualSets(
			[
				'post',
				'page',
				'attachment',
			],
			array_unique( $migrated_options[ Option::SUPPORTED_POST_TYPES ] )
		);
	}

	/**
	 * Test get_options when supported_templates option is list of templates and when theme support is used.
	 *
	 * @covers AMP_Options_Manager::get_options()
	 */
	public function test_get_options_migration_supported_templates() {
		// Make sure the theme support get migrated to DB option.
		delete_option( AMP_Options_Manager::OPTION_NAME );
		add_theme_support(
			'amp',
			[
				'templates_supported' => [
					'is_archive'  => true,
					'is_search'   => false,
					'is_404'      => false,
					'is_singular' => true,
				],
			]
		);
		$this->assertEqualSets(
			[
				'is_archive',
				'is_singular',
			],
			array_unique( AMP_Options_Manager::get_option( Option::SUPPORTED_TEMPLATES ) )
		);
	}

	/**
	 * Tests the update_options method.
	 *
	 * @covers AMP_Options_Manager::update_options
	 */
	public function test_update_options() {
		// Confirm updating multiple entries at once works.
		AMP_Options_Manager::update_options(
			[
				Option::THEME_SUPPORT => 'reader',
				Option::READER_THEME  => 'twentysixteen',
			]
		);

		$this->assertEquals( 'reader', AMP_Options_Manager::get_option( Option::THEME_SUPPORT ) );
		$this->assertEquals( 'twentysixteen', AMP_Options_Manager::get_option( Option::READER_THEME ) );
	}

	public function get_test_get_options_defaults_data() {
		return [
			'reader'                               => [
				null,
				AMP_Theme_Support::READER_MODE_SLUG,
			],
			'transitional_without_template_dir'    => [
				[
					'paired' => true,
				],
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
			],
			'transitional_implied_by_template_dir' => [
				[
					'template_dir' => 'amp',
				],
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
			],
			'standard_paired_false'                => [
				[
					'paired' => false,
				],
				AMP_Theme_Support::STANDARD_MODE_SLUG,
			],
			'transitional_no_args'                 => [
				[],
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
			],
			'standard_via_native'                  => [
				null,
				AMP_Theme_Support::STANDARD_MODE_SLUG,
				[
					Option::THEME_SUPPORT => 'native',
				],
			],
			'standard_via_paired'                  => [
				null,
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				[
					Option::THEME_SUPPORT => 'paired',
				],
			],
			'reader_mode_persists_non_paired'      => [
				[
					'paired' => false,
				],
				AMP_Theme_Support::READER_MODE_SLUG,
				[
					Option::THEME_SUPPORT => 'disabled',
				],
			],
			'reader_mode_persists_paired'          => [
				[
					'paired' => true,
				],
				AMP_Theme_Support::READER_MODE_SLUG,
				[
					Option::THEME_SUPPORT => 'disabled',
				],
			],
		];
	}

	/**
	 * Test get_options defaults.
	 *
	 * @dataProvider get_test_get_options_defaults_data
	 * @covers AMP_Options_Manager::get_options()
	 * @covers AMP_Options_Manager::get_option()
	 *
	 * @param array|null $args           Theme support args.
	 * @param string     $expected_mode  Expected mode.
	 * @param array      $initial_option Initial option in DB.
	 */
	public function test_get_options_theme_support_defaults( $args, $expected_mode, $initial_option = [] ) {
		update_option( AMP_Options_Manager::OPTION_NAME, $initial_option );
		if ( isset( $args ) ) {
			add_theme_support( 'amp', $args );
		}
		$this->assertEquals( $expected_mode, AMP_Options_Manager::get_option( Option::THEME_SUPPORT ) );
	}

	/** @covers AMP_Options_Manager::render_php_css_parser_conflict_notice() */
	public function test_render_php_css_parser_conflict_notice() {
		$this->assertEmpty( get_echo( [ 'AMP_Options_Manager', 'render_php_css_parser_conflict_notice' ] ) );

		set_current_screen( 'themes' );
		$this->assertEmpty( get_echo( [ 'AMP_Options_Manager', 'render_php_css_parser_conflict_notice' ] ) );

		set_current_screen( 'toplevel_page_' . AMP_Options_Manager::OPTION_NAME );
		$this->assertEmpty( get_echo( [ 'AMP_Options_Manager', 'render_php_css_parser_conflict_notice' ] ) );
	}

	/** @covers AMP_Options_Manager::insecure_connection_notice() */
	public function test_insecure_connection_notice() {
		$_SERVER['HTTPS'] = false;
		$this->assertEmpty( get_echo( [ 'AMP_Options_Manager', 'insecure_connection_notice' ] ) );

		set_current_screen( 'themes' );
		$this->assertEmpty( get_echo( [ 'AMP_Options_Manager', 'insecure_connection_notice' ] ) );

		set_current_screen( 'toplevel_page_' . AMP_Options_Manager::OPTION_NAME );
		$this->assertStringContainsString( 'notice-warning', get_echo( [ 'AMP_Options_Manager', 'insecure_connection_notice' ] ) );

		$_SERVER['HTTPS'] = 'on';
		$set_https_url    = static function ( $url ) {
			return set_url_scheme( $url, 'https' );
		};
		add_filter( 'home_url', $set_https_url );
		add_filter( 'site_url', $set_https_url );
		set_current_screen( 'toplevel_page_' . AMP_Options_Manager::OPTION_NAME );
		$this->assertEmpty( get_echo( [ 'AMP_Options_Manager', 'insecure_connection_notice' ] ) );
	}

	/** @covers AMP_Options_Manager::reader_theme_fallback_notice() */
	public function test_reader_theme_fallback_notice() {
		$admin_user = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_user );
		AMP_Options_Manager::update_options(
			[
				Option::THEME_SUPPORT => 'reader',
				Option::READER_THEME  => 'foobar',
			]
		);

		$this->assertEmpty( get_echo( [ 'AMP_Options_Manager', 'reader_theme_fallback_notice' ] ) );

		set_current_screen( 'index' );
		$this->assertEmpty( get_echo( [ 'AMP_Options_Manager', 'reader_theme_fallback_notice' ] ) );

		set_current_screen( 'themes' );
		$this->assertStringContainsString( 'notice-warning', get_echo( [ 'AMP_Options_Manager', 'reader_theme_fallback_notice' ] ) );

		set_current_screen( 'toplevel_page_' . AMP_Options_Manager::OPTION_NAME );
		$this->assertStringContainsString( 'notice-warning', get_echo( [ 'AMP_Options_Manager', 'reader_theme_fallback_notice' ] ) );

		AMP_Options_Manager::update_option( Option::READER_THEME, ReaderThemes::DEFAULT_READER_THEME );
		$this->assertEmpty( get_echo( [ 'AMP_Options_Manager', 'reader_theme_fallback_notice' ] ) );

		AMP_Options_Manager::update_option( Option::READER_THEME, 'foobar' );
		wp_set_current_user( 0 );
		$this->assertEmpty( get_echo( [ 'AMP_Options_Manager', 'reader_theme_fallback_notice' ] ) );
	}
}
