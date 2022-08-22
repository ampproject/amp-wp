<?php
/**
 * Tests for OptionsMenu.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\GoogleFonts;
use AmpProject\AmpWP\Admin\OptionsMenu;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Admin\RESTPreloader;
use AmpProject\AmpWP\Admin\SiteHealth;
use AmpProject\AmpWP\DependencySupport;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\LoadingError;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\HomeUrlLoopbackRequestMocking;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\ThemesApiRequestMocking;
use AMP_Options_Manager;
use AMP_Validation_Manager;

/**
 * Tests for OptionsMenu.
 *
 * @group options-menu
 * @coversDefaultClass \AmpProject\AmpWP\Admin\OptionsMenu
 */
class OptionsMenuTest extends DependencyInjectedTestCase {

	use HomeUrlLoopbackRequestMocking;
	use ThemesApiRequestMocking;
	use PrivateAccess;

	/**
	 * Instance of OptionsMenu
	 *
	 * @var OptionsMenu
	 */
	public $instance;

	/**
	 * Set up.
	 *
	 * @inheritdoc
	 */
	public function set_up() {
		parent::set_up();

		$site_health = $this->injector->make( SiteHealth::class );

		$this->instance = new OptionsMenu( new GoogleFonts(), new ReaderThemes(), new RESTPreloader(), new DependencySupport(), new LoadingError(), $site_health );

		$this->add_reader_themes_request_filter();
		$this->add_home_url_loopback_request_mocking();
	}

	/**
	 * Tear down.
	 *
	 * @inheritdoc
	 */
	public function tear_down() {
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;
		parent::tear_down();
	}

	/** @covers ::is_needed() */
	public function test_is_needed() {
		$this->assertFalse( is_admin() );
		set_current_screen( 'index.php' );
		$this->assertTrue( OptionsMenu::is_needed() );

		add_filter( 'amp_options_menu_is_enabled', '__return_false' );
		$this->assertFalse( OptionsMenu::is_needed() );

		add_filter( 'amp_options_menu_is_enabled', '__return_true', 20 );
		$this->assertTrue( OptionsMenu::is_needed() );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( OptionsMenu::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
	}

	/**
	 * Test constants.
	 *
	 * @see OptionsMenu::ICON_BASE64_SVG
	 */
	public function test_constants() {
		$this->assertStringStartsWith( 'data:image/svg+xml;base64,', OptionsMenu::ICON_BASE64_SVG );
	}

	/**
	 * Test add_hooks.
	 *
	 * @see OptionsMenu::add_hooks()
	 * @cogers ::register()
	 */
	public function test_register() {
		$this->instance->register();
		$this->assertEquals( 9, has_action( 'admin_menu', [ $this->instance, 'add_menu_items' ] ) );

		$this->assertEquals( 10, has_filter( 'plugin_action_links_amp/amp.php', [ $this->instance, 'add_plugin_action_links' ] ) );

		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_assets' ] ) );
	}

	/** @covers ::add_plugin_action_links() */
	public function test_add_plugin_action_links() {
		$links = [
			'example' => '<a href="https://example.com">example!</a>',
		];

		$filtered_links = $this->instance->add_plugin_action_links( $links );

		$this->assertArrayHasKey( 'example', $filtered_links );
		$this->assertArrayHasKey( 'settings', $filtered_links );
	}

	/** @covers ::get_menu_slug() */
	public function test_get_menu_slug() {
		$this->assertSame(
			AMP_Options_Manager::OPTION_NAME,
			$this->instance->get_menu_slug()
		);
	}

	/**
	 * Test admin_menu.
	 *
	 * @covers ::add_menu_items()
	 */
	public function test_add_menu_items() {
		global $_parent_pages, $submenu;

		$original_submenu      = $submenu;
		$original_parent_pages = $_parent_pages;

		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);

		$this->instance->add_menu_items();
		$this->assertArrayHasKey( 'amp-options', $_parent_pages );
		$this->assertEquals( 'amp-options', $_parent_pages['amp-options'] );

		$this->assertArrayHasKey( 'amp-options', $submenu );
		$this->assertCount( 1, $submenu['amp-options'] );
		$this->assertEquals( 'amp-options', $submenu['amp-options'][0][2] );

		$submenu       = $original_submenu;
		$_parent_pages = $original_parent_pages;
	}

	/** @covers ::screen_handle() */
	public function test_screen_handle() {
		$this->assertSame(
			'toplevel_page_' . $this->instance->get_menu_slug(),
			$this->instance->screen_handle()
		);
	}

	/** @covers ::get_analytics_vendors() */
	public function test_get_analytics_vendors() {
		$vendors = $this->instance->get_analytics_vendors();
		$this->assertIsArray( $vendors );
		$this->assertNotEmpty( $vendors );
		$pairs = [];
		foreach ( $vendors as $vendor ) {
			$this->assertIsArray( $vendor );
			$this->assertArrayHasKey( 'value', $vendor );
			$this->assertArrayHasKey( 'label', $vendor );
			$pairs[] = $vendor['value'] . ':' . $vendor['label'];
		}
		$this->assertIndexedArrayContains(
			[
				'adobeanalytics:Adobe Analytics',
				'googleanalytics:Google Analytics',
				'gtag:gtag',
			],
			$pairs
		);
	}

	/** @covers ::enqueue_assets() */
	public function test_enqueue_assets_wrong_hook_suffix() {
		$this->instance->enqueue_assets( 'nope' );
		$this->assertEquals( 0, did_action( 'amp_register_polyfills' ) );
		$this->assertFalse( wp_script_is( OptionsMenu::ASSET_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_style_is( OptionsMenu::ASSET_HANDLE, 'enqueued' ) );
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
	 * @covers ::enqueue_assets()
	 * @covers ::add_preload_rest_paths()
	 *
	 * @param bool $can_validate
	 */
	public function test_enqueue_assets_right_hook_suffix( $can_validate ) {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		set_current_screen( $this->instance->screen_handle() );
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

		$rest_preloader = $this->get_private_property( $this->instance, 'rest_preloader' );
		$this->assertCount( 0, $this->get_private_property( $rest_preloader, 'paths' ) );

		$this->assertFalse( wp_script_is( OptionsMenu::ASSET_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_style_is( OptionsMenu::ASSET_HANDLE, 'enqueued' ) );

		add_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_assets' ] );
		do_action( 'admin_enqueue_scripts', $this->instance->screen_handle() );
		$this->assertEquals( 1, did_action( 'amp_register_polyfills' ) );

		$this->assertTrue( wp_script_is( OptionsMenu::ASSET_HANDLE, 'enqueued' ) );
		$this->assertTrue( wp_style_is( OptionsMenu::ASSET_HANDLE, 'enqueued' ) );

		$script_before = implode( "\n", wp_scripts()->get_data( OptionsMenu::ASSET_HANDLE, 'before' ) );
		$this->assertStringContainsString( 'var ampSettings', $script_before );
		$this->assertStringContainsString( 'USER_FIELD_DEVELOPER_TOOLS_ENABLED', $script_before );
		$this->assertStringContainsString( 'USERS_RESOURCE_REST_PATH', $script_before );
		if ( $can_validate ) {
			$this->assertStringContainsString( AMP_Validation_Manager::get_amp_validate_nonce(), $script_before );
		} else {
			$this->assertStringNotContainsString( AMP_Validation_Manager::get_amp_validate_nonce(), $script_before );
		}

		if ( function_exists( 'rest_preload_api_request' ) ) {
			$this->assertEqualSets(
				[
					'/amp/v1/options',
					'/amp/v1/reader-themes',
					'/amp/v1/scannable-urls?_fields%5B0%5D=url&_fields%5B1%5D=amp_url&_fields%5B2%5D=type&_fields%5B3%5D=label&_fields%5B4%5D=validation_errors&_fields%5B5%5D=stale',
					'/wp/v2/plugins?_fields%5B0%5D=author&_fields%5B1%5D=name&_fields%5B2%5D=plugin&_fields%5B3%5D=status&_fields%5B4%5D=version',
					'/wp/v2/settings',
					'/wp/v2/themes?_fields%5B0%5D=author&_fields%5B1%5D=name&_fields%5B2%5D=status&_fields%5B3%5D=stylesheet&_fields%5B4%5D=template&_fields%5B5%5D=version',
					'/wp/v2/users/me',
				],
				$this->get_private_property( $rest_preloader, 'paths' )
			);
		}
	}

	/**
	 * Test render_screen for admin users.
	 *
	 * @covers ::render_screen()
	 */
	public function test_render_screen_for_admin_user() {
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);

		ob_start();
		$this->instance->render_screen();
		$this->assertStringContainsString( '<div class="wrap">', ob_get_clean() );
	}
}
