<?php
/**
 * Tests for PairedBrowsing class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Admin\PairedBrowsing;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\HasRequirements;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\Services;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\DevMode;
use WP_Admin_Bar;

/** @coversDefaultClass \AmpProject\AmpWP\Admin\PairedBrowsing */
class PairedBrowsingTest extends DependencyInjectedTestCase {

	/** @var PairedBrowsing */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->instance = $this->injector->make( PairedBrowsing::class );
	}

	/** @covers ::is_needed() */
	public function test_is_needed() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->assertFalse( PairedBrowsing::is_needed() );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertFalse( PairedBrowsing::is_needed() );

		$admin_user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_user_id );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->assertSame( Services::get( 'dependency_support' )->has_support(), PairedBrowsing::is_needed() );
		wp_set_current_user( 0 );
		$this->assertFalse( PairedBrowsing::is_needed() );
		wp_set_current_user( $admin_user_id );
		add_filter( 'amp_dev_mode_enabled', '__return_false' );
		$this->assertFalse( PairedBrowsing::is_needed() );
		remove_filter( 'amp_dev_mode_enabled', '__return_false' );
		$this->assertSame( Services::get( 'dependency_support' )->has_support(), PairedBrowsing::is_needed() );

		// Case where Reader theme is set to be the same as the active theme.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, get_stylesheet() );
		$this->assertSame( Services::get( 'dependency_support' )->has_support(), PairedBrowsing::is_needed() );
	}

	/** @covers ::get_requirements() */
	public function test_get_requirements() {
		$this->assertSame( [ 'dependency_support' ], PairedBrowsing::get_requirements() );
	}

	/** @covers ::get_registration_action() */
	public function test_get_registration_action() {
		$this->assertSame( 'wp_loaded', PairedBrowsing::get_registration_action() );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( PairedBrowsing::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( HasRequirements::class, $this->instance );
	}

	/** @covers ::register() */
	public function test_register() {
		$this->instance->register();
		$this->assertEquals( PHP_INT_MAX, has_action( 'wp', [ $this->instance, 'init_frontend' ] ) );
		$this->assertEquals( 10, has_filter( 'amp_dev_mode_element_xpaths', [ $this->instance, 'filter_dev_mode_element_xpaths' ] ) );
		$this->assertEquals( 10, has_filter( 'amp_validated_url_status_actions', [ $this->instance, 'filter_validated_url_status_actions' ] ) );
	}

	/** @covers ::filter_dev_mode_element_xpaths() */
	public function test_filter_dev_mode_element_xpaths() {
		$xpaths = $this->instance->filter_dev_mode_element_xpaths( [ '//div' ] );
		$this->assertCount( 2, $xpaths );
	}

	/** @covers ::filter_validated_url_status_actions() */
	public function test_filter_validated_url_status_actions() {
		$post    = self::factory()->post->create_and_get();
		$actions = $this->instance->filter_validated_url_status_actions( [ 'foo' => 'bar' ], $post );
		$this->assertCount( 2, $actions );
		$this->assertArrayHasKey( 'foo', $actions );
		$this->assertArrayHasKey( 'paired_browsing', $actions );
	}

	/** @covers ::init_frontend() */
	public function test_init_frontend_short_circuited() {
		$post = self::factory()->post->create_and_get();

		$assert_short_circuited = function () {
			$this->assertFalse( has_action( 'template_redirect', [ $this->instance, 'ensure_app_location' ] ) );
			$this->assertFalse( has_action( 'admin_bar_menu', [ $this->instance, 'add_admin_bar_menu_item' ] ) );
		};

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->go_to( get_permalink( $post ) );

		// Check first short-circuit condition.
		add_filter( 'amp_skip_post', '__return_true' );
		$this->assertFalse( amp_is_available() );
		$this->instance->init_frontend();
		$assert_short_circuited();
		remove_all_filters( 'amp_skip_post' );
	}

	/**
	 * @covers ::init_frontend()
	 * @covers ::init_app()
	 */
	public function test_init_frontend_app() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$post = self::factory()->post->create_and_get();
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->go_to( add_query_arg( PairedBrowsing::APP_QUERY_VAR, '1', get_permalink( $post ) ) );

		add_filter( 'amp_skip_post', '__return_false' );
		$this->instance->init_frontend();

		// Check that init_app() was called.
		$this->assertEquals( 10, has_action( 'template_redirect', [ $this->instance, 'ensure_app_location' ] ) );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'template_include', [ $this->instance, 'filter_template_include_for_app' ] ) );

		// Check that init_client() was not called.
		$this->assertFalse( has_action( 'admin_bar_menu', [ $this->instance, 'add_admin_bar_menu_item' ] ) );
		$this->assertEquals( 0, did_action( 'amp_register_polyfills' ) );
	}

	/**
	 * @covers ::init_frontend()
	 * @covers ::init_client()
	 */
	public function test_init_frontend_client() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$post = self::factory()->post->create_and_get();
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );

		add_filter( 'amp_skip_post', '__return_false' );
		$this->go_to( $this->instance->paired_routing->add_endpoint( get_permalink( $post ) ) );
		$this->assertTrue( amp_is_request() );
		$this->instance->init_frontend();

		// Check that init_client() was called.
		$this->assertEquals( 102, has_action( 'admin_bar_menu', [ $this->instance, 'add_admin_bar_menu_item' ] ) );
		$this->assertEquals( 1, did_action( 'amp_register_polyfills' ) );
		$this->assertTrue( wp_script_is( 'amp-paired-browsing-client' ) );
		$printed_scripts = get_echo( 'wp_print_scripts' );
		$this->assertStringContainsString( DevMode::DEV_MODE_ATTRIBUTE, $printed_scripts );
		$this->assertStringContainsString( 'ampPairedBrowsingClientData', $printed_scripts );
		$this->assertStringContainsString( 'isAmpDocument', $printed_scripts );
		$this->assertStringContainsString( 'amp-paired-browsing-client.js', $printed_scripts );

		// Check that init_app() was not called.
		$this->assertFalse( has_action( 'template_redirect', [ $this->instance, 'ensure_app_location' ] ) );
	}

	/** @covers ::add_admin_bar_menu_item() */
	public function test_add_admin_bar_menu_item() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
		add_filter( 'show_admin_bar', '__return_true' );
		$wp_admin_bar = new WP_Admin_Bar();

		// Test when DevTools not enabled.
		$this->assertFalse( $this->instance->dev_tools_user_access->is_user_enabled() );
		$this->instance->add_admin_bar_menu_item( $wp_admin_bar );
		$this->assertEmpty( $wp_admin_bar->get_node( 'amp-paired-browsing' ) );

		// Test when DevTools enabled.
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertTrue( $this->instance->dev_tools_user_access->is_user_enabled() );
		$this->instance->add_admin_bar_menu_item( $wp_admin_bar );
		$this->assertNotEmpty( $wp_admin_bar->get_node( 'amp-paired-browsing' ) );
	}

	/** @covers ::get_paired_browsing_url() */
	public function test_get_paired_browsing_url() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$post_id = self::factory()->post->create();
		$this->go_to( amp_get_permalink( $post_id ) );

		$this->assertStringContainsString( PairedBrowsing::APP_QUERY_VAR . '=1', $this->instance->get_paired_browsing_url() );
		$this->assertStringNotContainsString( amp_get_slug() . '=1', $this->instance->get_paired_browsing_url() );
		$this->assertEquals(
			$this->instance->get_paired_browsing_url(),
			$this->instance->get_paired_browsing_url( amp_get_current_url() )
		);
	}

	/** @covers ::ensure_app_location() */
	public function test_ensure_app_location() {
		$redirected = false;
		add_filter(
			'wp_redirect',
			function () use ( &$redirected ) {
				$redirected = true;
				return false;
			}
		);

		// Test that redirection is not needed.
		$this->go_to( $this->instance->get_paired_browsing_url( home_url( '/' ) ) );
		$this->instance->ensure_app_location();
		$this->assertFalse( $redirected );

		// Test that redirection is needed.
		$this->go_to( add_query_arg( QueryVar::NOAMP, $this->instance->get_paired_browsing_url( home_url( '/' ) ) ) );
		$this->instance->ensure_app_location();
		$this->assertTrue( $redirected );
	}

	/** @covers ::filter_template_include_for_app() */
	public function test_filter_template_include_for_app_when_allowed() {
		$this->assertEquals( 0, did_action( 'amp_register_polyfills' ) );

		$include_path = $this->instance->filter_template_include_for_app();
		$this->assertEquals( 1, did_action( 'amp_register_polyfills' ) );
		$this->assertTrue( wp_style_is( 'amp-paired-browsing-app' ) );
		$this->assertTrue( wp_script_is( 'amp-paired-browsing-app' ) );

		ob_start();
		load_template( $include_path );
		$template = ob_get_clean();

		$this->assertStringContainsString( 'amp-paired-browsing-app.css', $template );
		$this->assertStringContainsString( 'amp-paired-browsing-app.js', $template );
		$this->assertStringContainsString( 'ampPairedBrowsingAppData', $template );
		$this->assertStringContainsString( 'ampPairedBrowsingQueryVar', $template );
	}
}
