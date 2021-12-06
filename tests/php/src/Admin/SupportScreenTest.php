<?php
/**
 * Tests for SupportMenu.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\Admin\SupportScreen;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\HomeUrlLoopbackRequestMocking;

/**
 * Tests for SupportMenu.
 *
 * @group support-menu
 * @coversDefaultClass \AmpProject\AmpWP\Admin\SupportScreen
 */
class SupportScreenTest extends DependencyInjectedTestCase {

	use HomeUrlLoopbackRequestMocking;

	/**
	 * Instance of SupportMenu
	 *
	 * @var SupportScreen
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {

		parent::setUp();

		$this->instance = $this->injector->make( SupportScreen::class );

		$this->add_home_url_loopback_request_mocking();
	}

	/** @covers ::__construct() */
	public function test__construct() {

		$this->assertInstanceOf( SupportScreen::class, $this->instance );
	}

	/**
	 * @covers ::is_needed
	 */
	public function test_is_needed() {

		// Without mocking.
		$this->assertFalse( SupportScreen::is_needed() );

		// Mock the is_admin()
		set_current_screen( $this->instance->screen_handle() );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		add_filter( 'amp_support_menu_is_enabled', '__return_true', 999 );

		$this->assertTrue( SupportScreen::is_needed() );

		// Reset data.
		unset( $GLOBALS['current_screen'] );
	}

	/**
	 * @covers ::register
	 */
	public function test_register() {

		$this->instance->register();

		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_assets' ] ) );
		$this->assertEquals( 9, has_action( 'admin_menu', [ $this->instance, 'add_menu_items' ] ) );

	}

	/**
	 * @covers ::get_menu_slug
	 */
	public function test_get_menu_slug() {

		$this->assertEquals( 'amp-support', $this->instance->get_menu_slug() );
	}

	/**
	 * @covers ::screen_handle
	 */
	public function test_screen_handle() {

		$this->assertEquals(
			sprintf( 'amp_page_%s', $this->instance->get_menu_slug() ),
			$this->instance->screen_handle()
		);
	}

	/**
	 * @covers ::add_menu_items
	 */
	public function test_add_menu_items() {

		global $submenu;

		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);

		$this->instance->add_menu_items();

		$this->assertArrayHasKey( 'amp-options', $submenu );
		$this->assertContains(
			[
				'Support',
				'manage_options',
				'amp-support',
				'Support',
			],
			$submenu['amp-options']
		);

	}

	/**
	 * @covers ::enqueue_assets
	 */
	public function test_enqueue_assets() {

		$wp_scripts = wp_scripts();
		$wp_styles  = wp_styles();

		$this->instance->enqueue_assets( '' );

		$this->assertArrayNotHasKey( SupportScreen::ASSET_HANDLE, $wp_scripts->registered );
		$this->assertArrayNotHasKey( SupportScreen::ASSET_HANDLE, $wp_styles->registered );

		$this->instance->enqueue_assets( $this->instance->screen_handle() );

		$this->assertArrayHasKey( SupportScreen::ASSET_HANDLE, $wp_scripts->registered );
		$this->assertArrayHasKey( SupportScreen::ASSET_HANDLE, $wp_styles->registered );

	}

	/**
	 * @covers ::render_screen
	 */
	public function test_render_screen() {

		ob_start();
		$this->instance->render_screen();
		$content = ob_get_clean();

		$this->assertStringContainsString( '<div id="amp-support-root"></div>', $content );
	}

	/**
	 * @covers ::get_amp_validated_post_counts()
	 */
	public function test_get_amp_validated_post_counts() {

		$validated_environment = AMP_Validated_URL_Post_Type::get_validated_environment();

		$this->factory()->post->create_and_get(
			[
				'post_title' => home_url( 'sample-page-for-amp-validation' ),
				'post_type'  => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
				'meta_input' => [
					AMP_Validated_URL_Post_Type::VALIDATED_ENVIRONMENT_POST_META_KEY => $validated_environment,
				],
			]
		);

		$stale_validated_environment                             = $validated_environment;
		$stale_validated_environment['options']['theme_support'] = 'standard';

		$this->factory()->post->create_and_get(
			[
				'post_title' => home_url( 'sample-page-for-amp-validation-stale' ),
				'post_type'  => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
				'meta_input' => [
					AMP_Validated_URL_Post_Type::VALIDATED_ENVIRONMENT_POST_META_KEY => $stale_validated_environment,
				],
			]
		);

		$this->assertEquals(
			[
				'all'   => 2,
				'fresh' => 1,
				'stale' => 1,
			],
			$this->instance->get_amp_validated_post_counts()
		);
	}
}
