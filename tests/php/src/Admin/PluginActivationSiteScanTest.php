<?php
/**
 * Tests for PluginActivationSiteScan class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\PluginActivationSiteScan;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Services;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

/**
 * Tests for PluginActivationSiteScan class.
 *
 * @group plugin-activation-site-scan
 *
 * @since 2.2
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\PluginActivationSiteScan
 */
class PluginActivationSiteScanTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/**
	 * Test instance.
	 *
	 * @var PluginActivationSiteScan
	 */
	private $plugin_activation_site_scan;

	public function setUp() {
		parent::setUp();

		$this->plugin_activation_site_scan = $this->injector->make( PluginActivationSiteScan::class );
		delete_option( 'amp-options' );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( PluginActivationSiteScan::class, $this->plugin_activation_site_scan );
		$this->assertInstanceOf( Conditional::class, $this->plugin_activation_site_scan );
		$this->assertInstanceOf( Delayed::class, $this->plugin_activation_site_scan );
		$this->assertInstanceOf( Service::class, $this->plugin_activation_site_scan );
		$this->assertInstanceOf( Registerable::class, $this->plugin_activation_site_scan );
	}

	/** @return array */
	public function get_data_to_test_is_needed() {
		return [
			'not_admin_screen'                       => [
				'screen_hook'  => '',
				'query_params' => [],
				'expected'     => false,
			],
			'not_admin_screen_with_get_activate'     => [
				'screen_hook'  => '',
				'query_params' => [ 'activate' ],
				'expected'     => false,
			],
			'admin_index_no_get_vars'                => [
				'screen_hook'  => 'index.php',
				'query_params' => [],
				'expected'     => false,
			],
			'plugins_screen_no_get_vars'             => [
				'screen_hook'  => 'plugins.php',
				'query_params' => [],
				'expected'     => false,
			],
			'admin_index_with_get_activate'          => [
				'screen_hook'  => 'index.php',
				'query_params' => [ 'activate' ],
				'expected'     => false,
			],
			'plugins_screen_with_get_activate'       => [
				'screen_hook'  => 'plugins.php',
				'query_params' => [ 'activate' ],
				'expected'     => true,
			],
			'admin_index_with_get_activate_multi'    => [
				'screen_hook'  => 'index.php',
				'query_params' => [ 'activate-multi' ],
				'expected'     => false,
			],
			'plugins_screen_with_get_activate_multi' => [
				'screen_hook'  => 'plugins.php',
				'query_params' => [ 'activate-multi' ],
				'expected'     => true,
			],
		];
	}

	/**
	 * @covers ::is_needed()
	 * @dataProvider get_data_to_test_is_needed
	 *
	 * @param string $screen_hook  Current screen hook.
	 * @param array  $query_params GET query parameters.
	 * @param bool   $expected     Expected value.
	 */
	public function test_is_needed( $screen_hook, $query_params, $expected ) {
		global $pagenow;

		// If dependency support is absent, then abort because is_needed will never be true.
		if ( ! Services::get( 'dependency_support' )->has_support() ) {
			return;
		}

		$_GET = array_fill_keys( $query_params, true );

		if ( ! empty( $screen_hook ) ) {
			$pagenow = $screen_hook;
			set_current_screen( $screen_hook );
		}

		$this->assertEquals( $expected, PluginActivationSiteScan::is_needed() );
	}

	/**
	 * Tests PluginActivationSiteScan::register
	 *
	 * @covers ::register
	 */
	public function test_register() {
		$this->plugin_activation_site_scan->register();
		$this->assertEquals( 10, has_action( 'pre_current_active_plugins', [ $this->plugin_activation_site_scan, 'render_notice' ] ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->plugin_activation_site_scan, 'enqueue_assets' ] ) );
	}

	/**
	 * @covers ::render_notice
	 */
	public function test_render_notice() {
		$this->assertStringContainsString( 'id="amp-site-scan-notice"', get_echo( [ $this->plugin_activation_site_scan, 'render_notice' ] ) );
	}

	/**
	 * @covers ::enqueue_assets
	 * @covers ::add_preload_rest_paths
	 */
	public function test_enqueue_assets() {
		$handle = 'amp-site-scan-notice';

		$rest_preloader = $this->get_private_property( $this->plugin_activation_site_scan, 'rest_preloader' );
		$this->assertCount( 0, $this->get_private_property( $rest_preloader, 'paths' ) );

		$this->plugin_activation_site_scan->enqueue_assets();
		$this->assertTrue( wp_script_is( $handle ) );
		$this->assertTrue( wp_style_is( $handle ) );

		if ( function_exists( 'rest_preload_api_request' ) ) {
			$this->assertEqualSets(
				[
					'/amp/v1/options',
					'/amp/v1/scannable-urls?_fields%5B0%5D=url&_fields%5B1%5D=amp_url&_fields%5B2%5D=type&_fields%5B3%5D=label',
					'/wp/v2/users/me',
				],
				$this->get_private_property( $rest_preloader, 'paths' )
			);
		}
	}

	/**
	 * @covers ::get_amp_compatible_plugins_url
	 */
	public function test_get_amp_compatible_plugins_url() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertStringContainsString( '/plugin-install.php?tab=amp-compatible', $this->call_private_method( $this->plugin_activation_site_scan, 'get_amp_compatible_plugins_url' ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );
		$this->assertSame( 'https://amp-wp.org/ecosystem/plugins/', $this->call_private_method( $this->plugin_activation_site_scan, 'get_amp_compatible_plugins_url' ) );
	}
}
