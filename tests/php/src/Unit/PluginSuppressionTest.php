<?php

namespace AmpProject\AmpWP\Tests\Unit;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\PluginRegistry;
use AmpProject\AmpWP\PluginSuppression;
use AmpProject\AmpWP\Services;
use AmpProject\AmpWP\Tests\AssertContainsCompatibility;
use AmpProject\AmpWP\Tests\MockPluginEnvironment;
use AmpProject\AmpWP\Tests\PrivateAccess;
use AmpProject\AmpWP\Tests\ThemesApiRequestMocking;
use AMP_Options_Manager;
use AMP_Theme_Support;
use AMP_Validated_URL_Post_Type;
use AMP_Validation_Manager;
use Exception;
use WP_Block_Type_Registry;
use WP_UnitTestCase;

/** @covers PluginSuppression */
final class PluginSuppressionTest extends WP_UnitTestCase {

	use PrivateAccess;
	use AssertContainsCompatibility;
	use ThemesApiRequestMocking;

	private $attempted_validate_request_urls = [];

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		$this->reset_widgets();
		add_filter(
			'pre_http_request',
			function( $r, $args, $url ) {
				unset( $args );

				if ( false === strpos( $url, 'amp_validate=' ) ) {
					return $r;
				}

				$this->attempted_validate_request_urls[] = remove_query_arg( [ 'amp_validate', 'amp_cache_bust' ], $url );
				return [
					'body'     => '',
					'response' => [
						'code'    => 503,
						'message' => 'Service Unavailable',
					],
				];
			},
			10,
			3
		);

		$this->set_private_property(
			Services::get( 'plugin_registry' ),
			'plugin_folder',
			basename( AMP__DIR__ ) . '/' . MockPluginEnvironment::BAD_PLUGINS_DIR
		);
	}

	/**
	 * Tear down.
	 */
	public function tearDown() {
		parent::tearDown();
		$this->attempted_validate_request_urls = [];

		$GLOBALS['wp_settings_fields']     = [];
		$GLOBALS['wp_registered_settings'] = [];

		if ( class_exists( 'WP_Block_Type_Registry' ) && WP_Block_Type_Registry::get_instance()->is_registered( 'bad/bad-block' ) ) {
			WP_Block_Type_Registry::get_instance()->unregister( 'bad/bad-block' );
		}
		$this->reset_widgets();
		$this->set_private_property(
			Services::get( 'plugin_registry' ),
			'plugin_folder',
			null
		);
	}

	/**
	 * Reset widgets.
	 */
	private function reset_widgets() {
		global $wp_widget_factory, $wp_registered_sidebars, $wp_registered_widgets, $wp_registered_widget_controls, $wp_registered_widget_updates;
		$wp_registered_sidebars        = [];
		$wp_registered_widgets         = [];
		$wp_registered_widget_controls = [];
		$wp_registered_widget_updates  = [];
		$wp_widget_factory->widgets    = [];
	}

	/**
	 * Initialize plugins used for testing.
	 */
	private function init_plugins() {
		update_option( 'active_plugins', MockPluginEnvironment::BAD_PLUGIN_FILES );
		foreach ( MockPluginEnvironment::BAD_PLUGIN_FILES as $bad_plugin_file ) {
			require AMP__DIR__ . '/' . MockPluginEnvironment::BAD_PLUGINS_DIR . '/' . $bad_plugin_file;
		}

		$sidebar_id = 'sidebar-1';
		register_sidebar( [ 'id' => $sidebar_id ] );
		update_option(
			'widget_bad',
			[
				2              => [],
				'_multiwidget' => true,
			]
		);
		update_option(
			'widget_search',
			[
				2              => [],
				'_multiwidget' => true,
			]
		);
		wp_set_sidebars_widgets(
			[
				$sidebar_id => [ 'bad-2', 'bad_single', 'search-2' ],
			]
		);
		wp_widgets_init(); // For bad-widget.php
	}

	/**
	 * Get bad plugin file slugs.
	 *
	 * @return string[] Plugin file slugs.
	 */
	private function get_bad_plugin_file_slugs() {
		/** @var PluginRegistry $plugin_registry */
		$plugin_registry = Services::get( 'plugin_registry' );

		$plugin_file_slugs = array_map(
			[ $plugin_registry, 'get_plugin_slug_from_file' ],
			MockPluginEnvironment::BAD_PLUGIN_FILES
		);

		if ( ! function_exists( 'register_block_type' ) || ! class_exists( 'WP_Block_Registry' ) ) {
			$plugin_file_slugs = array_diff( $plugin_file_slugs, [ MockPluginEnvironment::BAD_BLOCK_PLUGIN_FILE ] );
		}

		return $plugin_file_slugs;
	}

	/**
	 * @param bool $register Call the register method.
	 * @return PluginSuppression
	 */
	private function get_instance( $register = false ) {
		/** @var PluginRegistry $plugin_registry */
		$plugin_registry = Services::get( 'plugin_registry' );

		$instance = new PluginSuppression( $plugin_registry );
		if ( $register ) {
			$instance->register();
		}
		return $instance;
	}

	/** @covers PluginSuppression::__construct() */
	public function test_it_can_be_initialized() {
		$instance = $this->get_instance();

		$this->assertInstanceOf( PluginSuppression::class, $instance );
		$this->assertInstanceOf( Service::class, $instance );
		$this->assertInstanceOf( Registerable::class, $instance );
	}

	/** @covers PluginSuppression::register() */
	public function test_register() {
		$instance = $this->get_instance();

		$instance->register();
		$this->assertEquals(
			defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX, // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound
			has_action( 'wp', [ $instance, 'suppress_plugins' ] )
		);
		$this->assertEquals( 10, has_action( 'amp_options_menu_items', [ $instance, 'add_settings_field' ] ) );
	}

	/** @covers PluginSuppression::suppress_plugins() */
	public function test_suppress_plugins_not_amp_endpoint() {
		$url = home_url( '/' );
		remove_theme_support( 'amp' );
		$this->init_plugins();
		$bad_plugin_file_slugs = $this->get_bad_plugin_file_slugs();
		$this->populate_validation_errors( $url, $bad_plugin_file_slugs );
		$instance = $this->get_instance( true );
		$this->go_to( $url );

		$this->assertNotEmpty( $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
		$this->assertFalse( is_amp_endpoint() );
		$this->assertFalse( $instance->suppress_plugins(), 'Expected no suppression since not an AMP endpoint.' );
		$this->assert_plugin_suppressed_state( false, $bad_plugin_file_slugs );
	}

	/** @covers PluginSuppression::suppress_plugins() */
	public function test_suppress_plugins_none_suppressible() {
		$url = home_url( '/' );
		add_theme_support( 'amp' );
		$this->init_plugins();
		update_option( 'active_plugins', [] );
		$bad_plugin_file_slugs = $this->get_bad_plugin_file_slugs();
		$this->populate_validation_errors( $url, $bad_plugin_file_slugs );
		$instance = $this->get_instance( true );
		$this->go_to( $url );

		$this->assertEmpty( $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
		$this->assertTrue( is_amp_endpoint() );
		$this->assertFalse( $instance->suppress_plugins(), 'Expected no suppression since no suppressible plugins.' );
		$this->assert_plugin_suppressed_state( false, $bad_plugin_file_slugs );
	}

	/** @covers PluginSuppression::suppress_plugins() */
	public function test_suppress_plugins_when_no_plugins_suppressed() {
		$url = home_url( '/' );
		add_theme_support( 'amp' );
		$this->init_plugins();
		$bad_plugin_file_slugs = $this->get_bad_plugin_file_slugs();
		$this->populate_validation_errors( $url, $bad_plugin_file_slugs );
		$instance = $this->get_instance( true );
		$this->go_to( $url );
		AMP_Options_Manager::update_option( Option::SUPPRESSED_PLUGINS, [] );

		$this->assertNotEmpty( $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
		$this->assertTrue( is_amp_endpoint() );
		$this->assertFalse( $instance->suppress_plugins(), 'Expected no suppression since no plugins are being suppressed.' );
		$this->assert_plugin_suppressed_state( false, $bad_plugin_file_slugs );
	}

	/**
	 * @covers PluginSuppression::suppress_plugins()
	 * @covers PluginSuppression::suppress_blocks()
	 * @covers PluginSuppression::suppress_hooks()
	 * @covers PluginSuppression::suppress_shortcodes()
	 * @covers PluginSuppression::suppress_widgets()
	 * @covers PluginSuppression::is_callback_plugin_suppressed()
	 */
	public function test_suppress_plugins_when_conditions_satisfied_for_all() {
		$url = home_url( '/' );
		add_theme_support( 'amp' );
		$this->init_plugins();

		$bad_plugin_file_slugs = $this->get_bad_plugin_file_slugs();
		$this->assertGreaterThan( 0, $bad_plugin_file_slugs );
		$this->populate_validation_errors( $url, $bad_plugin_file_slugs );
		$instance = $this->get_instance( true );
		$this->go_to( $url );
		$this->assert_plugin_suppressed_state( false, $bad_plugin_file_slugs );

		$this->update_suppressed_plugins_option( array_fill_keys( $bad_plugin_file_slugs, true ) );
		$this->assertNotEmpty( $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
		$this->assertEqualSets( $bad_plugin_file_slugs, $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
		$this->assertTrue( is_amp_endpoint() );
		$this->assertTrue( $instance->suppress_plugins() );
		$this->assert_plugin_suppressed_state( true, $bad_plugin_file_slugs );
	}

	/**
	 * @covers PluginSuppression::suppress_plugins()
	 * @covers PluginSuppression::suppress_blocks()
	 * @covers PluginSuppression::suppress_hooks()
	 * @covers PluginSuppression::suppress_shortcodes()
	 * @covers PluginSuppression::suppress_widgets()
	 * @covers PluginSuppression::is_callback_plugin_suppressed()
	 */
	public function test_suppress_plugins_when_conditions_satisfied_for_some() {
		$url = home_url( '/' );
		add_theme_support( 'amp' );
		$this->init_plugins();

		$bad_plugin_file_slugs = $this->get_bad_plugin_file_slugs();
		$this->assertGreaterThan( 0, $bad_plugin_file_slugs );
		$suppressed_slugs   = array_slice( $bad_plugin_file_slugs, 0, 2 );
		$unsuppressed_slugs = array_slice( $bad_plugin_file_slugs, 2 );

		$this->populate_validation_errors( $url, $bad_plugin_file_slugs );
		$instance = $this->get_instance( true );
		$this->go_to( $url );
		$this->assert_plugin_suppressed_state( false, $bad_plugin_file_slugs );

		$this->update_suppressed_plugins_option( array_fill_keys( $suppressed_slugs, true ) );
		$this->assertNotEmpty( $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
		$this->assertEqualSets( $bad_plugin_file_slugs, $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
		$this->assertTrue( is_amp_endpoint() );
		$this->assertTrue( $instance->suppress_plugins() );
		$this->assert_plugin_suppressed_state( true, $suppressed_slugs );
		$this->assert_plugin_suppressed_state( false, $unsuppressed_slugs );
	}

	/**
	 * Test validating suppressed plugins.
	 *
	 * @covers PluginSuppression::sanitize_options()
	 * @covers AMP_Validated_URL_Post_Type::get_recent_validation_errors_by_source()
	 */
	public function test_sanitize_options() {
		$this->add_reader_themes_request_filter();
		$instance = $this->get_instance();
		$instance->register();

		/** @var PluginRegistry $plugin_registry */
		$plugin_registry = Services::get( 'plugin_registry' );

		$this->init_plugins();
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		AMP_Options_Manager::register_settings(); // Adds validate_options as filter.
		add_theme_support( AMP_Theme_Support::SLUG );
		AMP_Validation_Manager::init();

		$bad_plugin_file_slugs = $this->get_bad_plugin_file_slugs();
		// Test initial state.
		$this->assertEquals( [], AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS ) );

		// Test updating empty array.
		AMP_Options_Manager::update_option( Option::SUPPRESSED_PLUGINS, [] );
		$this->assertEquals( [], AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS ) );

		$this->assertCount( 0, $this->attempted_validate_request_urls );
		$this->assertEmpty( AMP_Validated_URL_Post_Type::get_recent_validation_errors_by_source() );

		$this->populate_validation_errors( home_url( '/' ), $bad_plugin_file_slugs );

		$errors_by_source = AMP_Validated_URL_Post_Type::get_recent_validation_errors_by_source();
		$this->assertEqualSets( [ 'plugin' ], array_keys( $errors_by_source ) );
		$this->assertEqualSets( $bad_plugin_file_slugs, array_keys( $errors_by_source['plugin'] ) );

		// When updating plugins that don't exit or can't be suppressed, do nothing.
		$this->update_suppressed_plugins_option(
			array_fill_keys(
				[ 'bogus', 'amp' ],
				'1'
			)
		);

		$this->assertEquals( [], AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS ) );
		$this->assertCount( 0, $this->attempted_validate_request_urls );

		// When updating option but both plugins are not suppressed, then no change is made.
		$this->update_suppressed_plugins_option(
			array_fill_keys(
				$bad_plugin_file_slugs,
				'0'
			)
		);
		$this->assertEquals( [], AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS ) );
		$this->assertCount( 0, $this->attempted_validate_request_urls, 'Expected no validation request to have been made since no changes were made (as both plugins are still unsuppressed).' );

		// When updating option and both are now suppressed, then a change is made.
		$this->update_suppressed_plugins_option(
			array_fill_keys(
				$bad_plugin_file_slugs,
				'1'
			)
		);
		$this->assertCount( 1, $this->attempted_validate_request_urls, 'Expected one validation request to have been made since no changes were made (as both plugins are still unsuppressed).' );
		$suppressed_plugins = AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS );
		$this->assertEqualSets( $bad_plugin_file_slugs, array_keys( $suppressed_plugins ) );
		foreach ( $suppressed_plugins as $slug => $suppressed_plugin ) {
			$this->assertArrayHasKey( Option::SUPPRESSED_PLUGINS_LAST_VERSION, $suppressed_plugin );
			$this->assertArrayHasKey( Option::SUPPRESSED_PLUGINS_TIMESTAMP, $suppressed_plugin );
			$this->assertArrayHasKey( Option::SUPPRESSED_PLUGINS_USERNAME, $suppressed_plugin );
			$this->assertEquals( wp_get_current_user()->user_nicename, $suppressed_plugin[ Option::SUPPRESSED_PLUGINS_USERNAME ] );
			$this->assertArrayHasKey( Option::SUPPRESSED_PLUGINS_ERRORING_URLS, $suppressed_plugin );
			$this->assertEquals( [ home_url( '/' ) ], $suppressed_plugin[ Option::SUPPRESSED_PLUGINS_ERRORING_URLS ] );
			$this->assertEquals( $plugin_registry->get_plugin_from_slug( $slug )['data']['Version'], $suppressed_plugin[ Option::SUPPRESSED_PLUGINS_LAST_VERSION ] );
		}

		// Stop suppressing only some plugins.
		$unsuppressed_plugins = array_slice( $bad_plugin_file_slugs, 0, 2 );
		$suppressed_plugins   = array_slice( $bad_plugin_file_slugs, 2 );
		$this->update_suppressed_plugins_option(
			array_merge(
				array_fill_keys( $suppressed_plugins, '1' ),
				array_fill_keys( $unsuppressed_plugins, '0' )
			)
		);
		$this->assertCount( 2, $this->attempted_validate_request_urls, 'Expected one validation request to have been made since no changes were made (as both plugins are still unsuppressed).' );
		$this->assertEqualSets( $suppressed_plugins, array_keys( AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS ) ) );
	}

	/** @covers PluginSuppression::add_settings_field() */
	public function test_add_settings_field_without_any_suppressible_plugins() {
		global $wp_settings_fields;
		$this->init_plugins();
		$instance = $this->get_instance( true );
		$this->assertCount( 0, $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
		$wp_settings_fields = [];
		$instance->add_settings_field();
		$this->assertFalse( isset( $wp_settings_fields[ AMP_Options_Manager::OPTION_NAME ]['general'][ Option::SUPPRESSED_PLUGINS ] ) );
	}

	/** @covers PluginSuppression::add_settings_field() */
	public function test_add_settings_field_with_suppressible_plugins() {
		global $wp_settings_fields;
		$instance = $this->get_instance( true );
		$this->init_plugins();
		$bad_plugin_file_slugs = $this->get_bad_plugin_file_slugs();
		$this->update_suppressed_plugins_option( array_fill_keys( $bad_plugin_file_slugs, true ) );
		$this->assertEqualSets( $bad_plugin_file_slugs, $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
		$wp_settings_fields = [];
		$instance->add_settings_field();
		$this->assertTrue( isset( $wp_settings_fields[ AMP_Options_Manager::OPTION_NAME ]['general'][ Option::SUPPRESSED_PLUGINS ] ) );
	}

	/**
	 * @covers PluginSuppression::render_suppressed_plugins()
	 * @covers PluginSuppression::render_validation_error_details()
	 */
	public function test_render_suppressed_plugins() {
		$this->init_plugins();
		$bad_plugin_file_slugs   = $this->get_bad_plugin_file_slugs();
		$plugins_with_errors     = array_slice( $bad_plugin_file_slugs, 0, 1 );
		$plugins_with_suppressed = array_slice( $bad_plugin_file_slugs, 1, 1 );
		$plugins_not_suppressed  = array_slice( $bad_plugin_file_slugs, 2, 1 );

		$instance = $this->get_instance( true );
		$this->update_suppressed_plugins_option( array_fill_keys( $plugins_with_suppressed, true ) );
		$this->populate_validation_errors( home_url( '/' ), $plugins_with_errors );

		ob_start();
		$instance->render_suppressed_plugins();
		$rendered = ob_get_clean();

		$this->assertStringContains( 'suppressed-plugins-table', $rendered );
		foreach ( array_merge( $plugins_with_errors, $plugins_with_suppressed ) as $plugin_slug ) {
			$this->assertStringContains( $plugin_slug, $rendered );
		}
		foreach ( $plugins_not_suppressed as $plugin_slug ) {
			$this->assertStringNotContains( $plugin_slug, $rendered );
		}
	}

	/** @covers PluginSuppression::get_suppressible_plugins() */
	public function test_get_suppressible_plugins_none() {
		$instance = $this->get_instance( true );
		$this->assertCount( 0, $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
	}

	/** @covers PluginSuppression::get_suppressible_plugins() */
	public function test_get_suppressible_plugins_active_but_no_errors() {
		$this->init_plugins();
		$this->assertCount( 0, $this->call_private_method( $this->get_instance( true ), 'get_suppressible_plugins' ) );
	}

	/** @covers PluginSuppression::get_suppressible_plugins() */
	public function test_get_suppressible_plugins_active_but_no_errors_since_already_suppressed() {
		$instance = $this->get_instance( true );
		$this->init_plugins();
		$bad_plugin_file_slugs = $this->get_bad_plugin_file_slugs();
		$this->update_suppressed_plugins_option( array_fill_keys( $bad_plugin_file_slugs, true ) );
		$this->assertEqualSets( $bad_plugin_file_slugs, $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
	}

	/** @covers PluginSuppression::get_suppressible_plugins() */
	public function test_get_suppressible_plugins_active_with_errors_but_not_already_suppressed() {
		$instance = $this->get_instance( true );
		$this->init_plugins();
		$bad_plugin_file_slugs = $this->get_bad_plugin_file_slugs();
		$this->update_suppressed_plugins_option( array_fill_keys( $bad_plugin_file_slugs, false ) );
		$this->populate_validation_errors( home_url( '/' ), $bad_plugin_file_slugs );
		$this->assertEqualSets( $bad_plugin_file_slugs, $this->call_private_method( $instance, 'get_suppressible_plugins' ) );
	}

	/** @covers PluginSuppression::get_suppressible_plugins() */
	public function test_get_suppressible_plugins_one_active_with_errors_others_already_suppressed() {
		$instance = $this->get_instance( true );
		$this->init_plugins();
		$bad_plugin_file_slugs      = $this->get_bad_plugin_file_slugs();
		$plugins_with_errors        = array_slice( $bad_plugin_file_slugs, 0, 1 );
		$plugins_already_suppressed = array_slice( $bad_plugin_file_slugs, 1 );
		$this->update_suppressed_plugins_option( array_fill_keys( $plugins_already_suppressed, true ) );
		$this->populate_validation_errors( home_url( '/' ), $plugins_with_errors );
		$this->assertEqualSets(
			array_merge( $plugins_already_suppressed, $plugins_with_errors ),
			$this->call_private_method( $instance, 'get_suppressible_plugins' )
		);
	}

	/**
	 * Update suppressed plugins options.
	 *
	 * @param array<bool> $plugins Plugins, mapping slugs to whether suppressed.
	 */
	private function update_suppressed_plugins_option( $plugins ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		}
		if ( ! isset( $GLOBALS['wp_registered_settings'][ AMP_Options_Manager::OPTION_NAME ] ) ) {
			AMP_Options_Manager::register_settings(); // Adds validate_options as filter.
		}
		AMP_Options_Manager::update_option(
			Option::SUPPRESSED_PLUGINS,
			$plugins
		);
		remove_all_actions( 'update_option_' . AMP_Options_Manager::OPTION_NAME );
	}

	/**
	 * Assert suppressed state of given plugins.
	 *
	 * @param bool     $suppressed   Whether the supplied plugin slugs are expected to be suppressed.
	 * @param string[] $plugin_slugs Plugin slugs to check.
	 * @throws Exception When bad arguments are supplied.
	 */
	private function assert_plugin_suppressed_state( $suppressed, $plugin_slugs ) {
		if ( empty( $plugin_slugs ) ) {
			throw new Exception( 'No plugins supplied to check!' );
		}

		$checked = 0;

		// Check bad-shortcode.
		if ( in_array( 'bad-shortcode.php', $plugin_slugs, true ) ) {
			global $shortcode_tags;
			$this->assertArrayHasKey( 'bad', $shortcode_tags );

			$content = do_shortcode( '[bad] [audio src="https://example.com/audio.mp3"]' );
			$this->assertStringContains( 'audio.mp3', $content );
			if ( $suppressed ) {
				$this->assertStringNotContains( 'Bad shortcode!', $content );
			} else {
				$this->assertStringContains( 'Bad shortcode!', $content );
			}
			$checked++;
		}

		// Check bad-widget.
		if ( in_array( 'bad-widget', $plugin_slugs, true ) ) {
			global $wp_widget_factory, $wp_registered_widgets;
			$this->assertArrayHasKey( 'Bad_Widget', $wp_widget_factory->widgets );
			$this->assertArrayHasKey( 'bad_single', $wp_registered_widgets );
			$this->assertArrayHasKey( 'bad-2', $wp_registered_widgets );
			$this->assertArrayHasKey( 'search-2', $wp_registered_widgets );

			ob_start();
			dynamic_sidebar( 'sidebar-1' );
			if ( version_compare( get_bloginfo( 'version' ), '5.3', '>=' ) ) {
				// Suppressing widgets printed by the widget() is only supported since WP>=5.3 when the 'widget_display_callback'
				// filter was added to the_widget().
				the_widget( 'Bad_Widget', array_fill_keys( [ 'before_widget', 'after_widget', 'before_title', 'after_title' ], '' ), [] );
			}
			$rendered_sidebar = ob_get_clean();

			$this->assertStringContains( 'searchform', $rendered_sidebar, 'Expected search widget to be present.' );
			if ( $suppressed ) {
				$this->assertStringNotContains( 'Bad Multi Widget', $rendered_sidebar );
				$this->assertStringNotContains( 'Bad Single Widget', $rendered_sidebar );
			} else {
				$this->assertStringContains( 'Bad Multi Widget', $rendered_sidebar );
				$this->assertStringContains( 'Bad Single Widget', $rendered_sidebar );
			}
			$checked++;
		}

		// Check bad-hooks.
		if ( in_array( 'bad-hooks.php', $plugin_slugs, true ) ) {

			// Check filter.
			$content = apply_filters( 'the_content', 'This is "content".' );
			$this->assertStringContains( '<p>This is &#8220;content&#8221;.</p>', $content, 'Expected default filters to apply.' );
			if ( $suppressed ) {
				$this->assertStringNotContains( 'Bad filter!', $content );
			} else {
				$this->assertStringContains( 'Bad filter!', $content );
			}

			// Check action.
			ob_start();
			wp_footer();
			$footer = ob_get_clean();
			if ( $suppressed ) {
				$this->assertStringNotContains( 'Bad action!', $footer );
			} else {
				$this->assertStringContains( 'Bad action!', $footer );
			}

			$checked++;
		}

		// Check bad-block.
		if ( in_array( 'bad-block.php', $plugin_slugs, true ) ) {
			$blocks = do_blocks( '<!-- wp:latest-posts /--><!-- wp:bad/bad-block /-->' );
			$this->assertStringContains( 'wp-block-latest-posts', $blocks, 'Expected Latest Posts block to always be present.' );
			if ( $suppressed ) {
				$this->assertStringNotContains( 'Bad dynamic block!', $blocks );
			} else {
				$this->assertStringContains( 'Bad dynamic block!', $blocks );
			}
			$checked++;
		}

		if ( 0 === $checked ) {
			throw new Exception( 'None of the supplied plugins were checked!' );
		}
	}

	/**
	 * Populate sample validation errors.
	 *
	 * @param string   $url               URL to populate errors for. Defaults to the home URL.
	 * @param string[] $plugin_file_slugs Plugin file slugs.
	 * @return int ID for amp_validated_url post.
	 */
	private function populate_validation_errors( $url, $plugin_file_slugs ) {
		if ( ! $url ) {
			$url = home_url( '/' );
		}

		$errors = array_map(
			static function ( $plugin_file_slug ) {
				return [
					'code'    => 'bad',
					'sources' => [
						[
							'type' => 'plugin',
							'name' => $plugin_file_slug,
						],
					],
				];
			},
			$plugin_file_slugs
		);

		$r = AMP_Validated_URL_Post_Type::store_validation_errors( $errors, $url );
		if ( is_wp_error( $r ) ) {
			throw new Exception( $r->get_error_message() );
		}
		return $r;
	}
}
