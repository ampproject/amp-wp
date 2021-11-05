<?php
/**
 * Tests for OnboardingWizardSubmenuPage class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\OnboardingWizardSubmenuPage;
use AmpProject\AmpWP\Admin\OptionsMenu;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\ThemesApiRequestMocking;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AMP_Options_Manager;

/**
 * Tests for OnboardingWizardSubmenuPage class.
 *
 * @group onboarding
 *
 * @since 2.0
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\OnboardingWizardSubmenuPage
 */
class OnboardingWizardSubmenuPageTest extends DependencyInjectedTestCase {

	use PrivateAccess;
	use ThemesApiRequestMocking;

	/**
	 * Test instance.
	 *
	 * @var OnboardingWizardSubmenuPage
	 */
	private $onboarding_wizard_submenu_page;

	/**
	 * @var OptionsMenu
	 */
	private $options_menu;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->onboarding_wizard_submenu_page = $this->injector->make( OnboardingWizardSubmenuPage::class );

		$this->options_menu = $this->injector->make( OptionsMenu::class );

		$this->add_reader_themes_request_filter();
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( OnboardingWizardSubmenuPage::class, $this->onboarding_wizard_submenu_page );
		$this->assertInstanceOf( Delayed::class, $this->onboarding_wizard_submenu_page );
		$this->assertInstanceOf( Service::class, $this->onboarding_wizard_submenu_page );
		$this->assertInstanceOf( Registerable::class, $this->onboarding_wizard_submenu_page );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::register
	 *
	 * @covers ::register()
	 */
	public function test_register() {
		$this->onboarding_wizard_submenu_page->register();

		$this->assertEquals( 10, has_action( 'admin_head-admin_page_amp-onboarding-wizard', [ $this->onboarding_wizard_submenu_page, 'override_template' ] ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->onboarding_wizard_submenu_page, 'enqueue_assets' ] ) );
		$this->assertEquals( 10, add_filter( 'admin_title', [ $this->onboarding_wizard_submenu_page, 'override_title' ] ) );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::override_title()
	 *
	 * @covers ::override_title()
	 */
	public function test_override_title() {
		set_current_screen( 'index.php' );

		$this->assertEquals( 'Index - WordPress', $this->onboarding_wizard_submenu_page->override_title( 'Index - WordPress' ) );

		set_current_screen( $this->onboarding_wizard_submenu_page->screen_handle() );

		$this->assertEquals( 'AMP Onboarding Wizard - WordPress', $this->onboarding_wizard_submenu_page->override_title( ' - WordPress' ) );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::render()
	 *
	 * @covers ::render()
	 */
	public function test_render() {
		wp_scripts(); // Make sure $wp_scripts global is defined for wp_check_widget_editor_deps().
		set_current_screen( 'admin_page_amp-onboarding-wizard' );

		ob_start();

		$this->onboarding_wizard_submenu_page->render();

		$this->assertStringContainsString( '<div class="amp" id="amp-onboarding-wizard">', ob_get_clean() );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::screen_handle()
	 *
	 * @covers ::screen_handle()
	 */
	public function test_screen_handle() {
		$this->assertEquals( $this->onboarding_wizard_submenu_page->screen_handle(), 'admin_page_amp-onboarding-wizard' );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::enqueue_assets
	 *
	 * @covers ::enqueue_assets()
	 * @covers ::add_preload_rest_paths()
	 */
	public function test_enqueue_assets() {
		$handle = 'amp-onboarding-wizard';

		$rest_preloader = $this->get_private_property( $this->onboarding_wizard_submenu_page, 'rest_preloader' );
		$this->assertCount( 0, $this->get_private_property( $rest_preloader, 'paths' ) );

		$this->onboarding_wizard_submenu_page->enqueue_assets( $this->onboarding_wizard_submenu_page->screen_handle() );
		$this->assertTrue( wp_script_is( $handle ) );
		$this->assertTrue( wp_style_is( $handle ) );

		if ( function_exists( 'rest_preload_api_request' ) ) {
			$this->assertEqualSets(
				[
					'/amp/v1/options',
					'/amp/v1/reader-themes',
					'/amp/v1/scannable-urls?_fields%5B0%5D=url&_fields%5B1%5D=amp_url&_fields%5B2%5D=type&_fields%5B3%5D=label&force_standard_mode=1',
					'/wp/v2/plugins',
					'/wp/v2/settings',
					'/wp/v2/themes',
					'/wp/v2/users/me',
				],
				$this->get_private_property( $rest_preloader, 'paths' )
			);
		}
	}

	/** @return array */
	public function get_referrer_links() {
		return [
			'tools_page'        => [
				static function () {
					return admin_url( 'tools.php' );
				},
				true,
			],
			'amp_settings_page' => [
				static function () {
					return admin_url( 'admin.php?page=amp-options' );
				},
				true,
			],
			'login_page'        => [
				'wp_login_url',
				false,
			],
		];
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::get_close_link()
	 *
	 * @covers ::get_close_link()
	 * @dataProvider get_referrer_links()
	 *
	 * @param callable $referrer_link_callback  Referrer link callback.
	 * @param bool     $expected_referrer_close Whether the close link is expected to be the referrer.
	 */
	public function test_get_close_link( $referrer_link_callback, $expected_referrer_close ) {
		$this->options_menu->add_menu_items();

		$referrer                = $referrer_link_callback();
		$_SERVER['HTTP_REFERER'] = $referrer;

		$this->assertEquals(
			$expected_referrer_close ? $referrer : menu_page_url( AMP_Options_Manager::OPTION_NAME, false ),
			$this->onboarding_wizard_submenu_page->get_close_link()
		);
	}
}
