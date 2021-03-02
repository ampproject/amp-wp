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
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;

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

	use AssertContainsCompatibility;

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
		set_current_screen( 'admin_page_amp-onboarding-wizard' );

		ob_start();

		$this->onboarding_wizard_submenu_page->render();

		$this->assertStringContains( '<div class="amp" id="amp-onboarding-wizard"></div>', ob_get_clean() );
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
	 */
	public function test_enqueue_assets() {
		$handle = 'amp-onboarding-wizard';

		$this->onboarding_wizard_submenu_page->enqueue_assets( $this->onboarding_wizard_submenu_page->screen_handle() );
		$this->assertTrue( wp_script_is( $handle ) );
		$this->assertTrue( wp_style_is( $handle ) );
	}

	/** @return array */
	public function get_referrer_links() {
		return [
			'tools_page'        => [
				admin_url( 'tools.php' ),
				admin_url( 'tools.php' ),
			],
			'amp_settings_page' => [
				admin_url( 'admin.php?page=amp-options' ),
				admin_url( 'admin.php?page=amp-options' ),
			],
			'login_page'        => [
				wp_login_url(),
				admin_url( 'admin.php?page=amp-options' ),
			],
		];
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::get_close_link()
	 *
	 * @covers ::enqueue_assets()
	 * @dataProvider get_referrer_links()
	 *
	 * @param string $referrer_link Referrer link.
	 * @param string $expected_link Expected link.
	 */
	public function test_get_close_link( $referrer_link, $expected_link ) {
		$this->options_menu->add_menu_items();

		$_SERVER['HTTP_REFERER'] = $referrer_link;
		$this->assertEquals( $expected_link, $this->onboarding_wizard_submenu_page->get_close_link() );
	}
}
