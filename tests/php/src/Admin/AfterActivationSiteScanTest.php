<?php
/**
 * Tests for AfterActivationSiteScan class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\AfterActivationSiteScan;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Services;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AMP_Validation_Manager;

/**
 * Tests for AfterActivationSiteScan class.
 *
 * @group plugin-activation-site-scan
 *
 * @since 2.2
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\AfterActivationSiteScan
 */
class AfterActivationSiteScanTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/**
	 * Test instance.
	 *
	 * @var AfterActivationSiteScan
	 */
	private $after_activation_site_scan;

	public function set_up() {
		parent::set_up();

		$this->after_activation_site_scan = $this->injector->make( AfterActivationSiteScan::class );
		delete_option( 'amp-options' );
	}

	/**
	 * Tear down.
	 *
	 * @inheritdoc
	 */
	public function tear_down() {
		parent::tear_down();
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( AfterActivationSiteScan::class, $this->after_activation_site_scan );
		$this->assertInstanceOf( Conditional::class, $this->after_activation_site_scan );
		$this->assertInstanceOf( Delayed::class, $this->after_activation_site_scan );
		$this->assertInstanceOf( Service::class, $this->after_activation_site_scan );
		$this->assertInstanceOf( Registerable::class, $this->after_activation_site_scan );
	}

	/** @return array */
	public function get_data_to_test_is_needed() {
		return [
			'not_admin_screen'                           => [
				'screen_hook'  => '',
				'query_params' => [],
				'expected'     => false,
				'role'         => 'administrator',
			],
			'not_admin_screen_with_get_activate'         => [
				'screen_hook'  => '',
				'query_params' => [ 'activate' ],
				'expected'     => false,
				'role'         => 'administrator',
			],
			'admin_index_no_get_vars'                    => [
				'screen_hook'  => 'index.php',
				'query_params' => [],
				'expected'     => false,
				'role'         => 'administrator',
			],
			'plugins_screen_no_get_vars'                 => [
				'screen_hook'  => 'plugins.php',
				'query_params' => [],
				'expected'     => false,
				'role'         => 'administrator',
			],
			'admin_index_with_get_activate'              => [
				'screen_hook'  => 'index.php',
				'query_params' => [ 'activate' ],
				'expected'     => false,
				'role'         => 'administrator',
			],
			'plugins_screen_with_get_activate'           => [
				'screen_hook'  => 'plugins.php',
				'query_params' => [ 'activate' ],
				'expected'     => true,
				'role'         => 'administrator',
			],
			'plugins_screen_with_get_activate_not_admin' => [
				'screen_hook'  => 'plugins.php',
				'query_params' => [ 'activate' ],
				'expected'     => false,
				'role'         => 'editor',
			],
			'admin_index_with_get_activate_multi'        => [
				'screen_hook'  => 'index.php',
				'query_params' => [ 'activate-multi' ],
				'expected'     => false,
				'role'         => 'administrator',
			],
			'plugins_screen_with_get_activate_multi'     => [
				'screen_hook'  => 'plugins.php',
				'query_params' => [ 'activate-multi' ],
				'expected'     => true,
				'role'         => 'administrator',
			],
			'plugins_screen_with_get_activated'          => [
				'screen_hook'  => 'plugins.php',
				'query_params' => [ 'activated' ],
				'expected'     => false,
				'role'         => 'administrator',
			],
			'themes_screen_no_get_vars'                  => [
				'screen_hook'  => 'themes.php',
				'query_params' => [],
				'expected'     => false,
				'role'         => 'administrator',
			],
			'admin_index_with_get_activated'             => [
				'screen_hook'  => 'index.php',
				'query_params' => [ 'activated' ],
				'expected'     => false,
				'role'         => 'administrator',
			],
			'themes_screen_with_get_activated'           => [
				'screen_hook'  => 'themes.php',
				'query_params' => [ 'activated' ],
				'expected'     => true,
				'role'         => 'administrator',
			],
			'themes_screen_with_get_activated_not_admin' => [
				'screen_hook'  => 'themes.php',
				'query_params' => [ 'activated' ],
				'expected'     => false,
				'role'         => 'editor',
			],
			'themes_screen_with_get_activate'            => [
				'screen_hook'  => 'themes.php',
				'query_params' => [ 'activate' ],
				'expected'     => false,
				'role'         => 'administrator',
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
	 * @param string $role         User role.
	 */
	public function test_is_needed( $screen_hook, $query_params, $expected, $role ) {
		global $pagenow;

		// If dependency support is absent, then abort because is_needed will never be true.
		if ( ! Services::get( 'dependency_support' )->has_support() ) {
			$this->markTestSkipped( 'WP install lacks dependency support.' );
		}

		$_GET = array_fill_keys( $query_params, true );

		if ( ! empty( $screen_hook ) ) {
			$pagenow = $screen_hook;
			set_current_screen( $screen_hook );
		}

		wp_set_current_user( self::factory()->user->create( compact( 'role' ) ) );
		$this->assertEquals( $expected, AfterActivationSiteScan::is_needed() );
	}

	/**
	 * Tests AfterActivationSiteScan::register
	 *
	 * @covers ::register
	 */
	public function test_register_with_cap() {
		$this->after_activation_site_scan->register();
		$this->assertEquals( 10, has_action( 'pre_current_active_plugins', [ $this->after_activation_site_scan, 'render_notice' ] ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->after_activation_site_scan, 'enqueue_assets' ] ) );
	}

	/**
	 * @covers ::render_notice
	 */
	public function test_render_notice() {
		$this->assertStringContainsString( 'id="amp-site-scan-notice"', get_echo( [ $this->after_activation_site_scan, 'render_notice' ] ) );
	}

	/** @return array */
	public function get_can_validate_data() {
		return [
			'can_validate'    => [ true ],
			'cannot_validate' => [ false ],
		];
	}

	/**
	 * @dataProvider get_can_validate_data
	 * @covers ::enqueue_assets
	 * @covers ::add_preload_rest_paths
	 *
	 * @param bool $can_validate
	 */
	public function test_enqueue_assets( $can_validate ) {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		if ( ! $can_validate ) {
			add_filter(
				'map_meta_cap',
				function ( $caps, $cap ) {
					if ( AMP_Validation_Manager::VALIDATE_CAPABILITY === $cap ) {
						$caps[] = 'do_not_allow';
					}
					return $caps;
				},
				10,
				3
			);
		}
		$this->assertEquals( $can_validate, AMP_Validation_Manager::has_cap() );

		$handle = 'amp-site-scan-notice';

		$rest_preloader = $this->get_private_property( $this->after_activation_site_scan, 'rest_preloader' );
		$this->assertCount( 0, $this->get_private_property( $rest_preloader, 'paths' ) );

		$this->after_activation_site_scan->enqueue_assets();
		$this->assertTrue( wp_script_is( $handle ) );
		$this->assertTrue( wp_style_is( $handle ) );

		$script_before = implode( '', wp_scripts()->get_data( $handle, 'before' ) );
		$this->assertStringContainsString( 'var ampSiteScanNotice', $script_before );
		$this->assertStringContainsString( 'AMP_COMPATIBLE_PLUGINS_URL', $script_before );
		$this->assertStringContainsString( 'AMP_COMPATIBLE_THEMES_URL', $script_before );
		$this->assertStringContainsString( 'VALIDATE_NONCE', $script_before );
		if ( $can_validate ) {
			$this->assertStringContainsString( AMP_Validation_Manager::get_amp_validate_nonce(), $script_before );
		} else {
			$this->assertStringNotContainsString( AMP_Validation_Manager::get_amp_validate_nonce(), $script_before );
		}

		if ( function_exists( 'rest_preload_api_request' ) ) {
			$this->assertEqualSets(
				[
					'/amp/v1/options',
					'/amp/v1/scannable-urls?_fields%5B0%5D=url&_fields%5B1%5D=amp_url&_fields%5B2%5D=type&_fields%5B3%5D=label',
					'/wp/v2/plugins?_fields%5B0%5D=author&_fields%5B1%5D=name&_fields%5B2%5D=plugin&_fields%5B3%5D=status&_fields%5B4%5D=version',
					'/wp/v2/themes?_fields%5B0%5D=author&_fields%5B1%5D=name&_fields%5B2%5D=status&_fields%5B3%5D=stylesheet&_fields%5B4%5D=template&_fields%5B5%5D=version',
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
		$this->assertStringContainsString( '/plugin-install.php?tab=amp-compatible', $this->call_private_method( $this->after_activation_site_scan, 'get_amp_compatible_plugins_url' ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );
		$this->assertSame( 'https://amp-wp.org/ecosystem/plugins/', $this->call_private_method( $this->after_activation_site_scan, 'get_amp_compatible_plugins_url' ) );
	}

	/**
	 * @covers ::get_amp_compatible_themes_url
	 */
	public function test_get_amp_compatible_themes_url() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertStringContainsString( '/theme-install.php?browse=amp-compatible', $this->call_private_method( $this->after_activation_site_scan, 'get_amp_compatible_themes_url' ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );
		$this->assertSame( 'https://amp-wp.org/ecosystem/themes/', $this->call_private_method( $this->after_activation_site_scan, 'get_amp_compatible_themes_url' ) );
	}
}
