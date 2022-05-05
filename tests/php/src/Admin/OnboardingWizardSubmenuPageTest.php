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
use AMP_Validation_Manager;

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
	public function set_up() {
		parent::set_up();

		$this->onboarding_wizard_submenu_page = $this->injector->make( OnboardingWizardSubmenuPage::class );

		$this->options_menu = $this->injector->make( OptionsMenu::class );

		$this->add_reader_themes_request_filter();
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

	/** @return array */
	public function get_can_validate_data() {
		return [
			'can_validate'    => [ true ],
			'cannot_validate' => [ false ],
		];
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::enqueue_assets
	 *
	 * @dataProvider get_can_validate_data
	 * @covers ::enqueue_assets()
	 * @covers ::add_preload_rest_paths()
	 *
	 * @param bool $can_validate
	 */
	public function test_enqueue_assets( $can_validate ) {
		$handle = 'amp-onboarding-wizard';
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

		$rest_preloader = $this->get_private_property( $this->onboarding_wizard_submenu_page, 'rest_preloader' );
		$this->assertCount( 0, $this->get_private_property( $rest_preloader, 'paths' ) );

		$this->onboarding_wizard_submenu_page->enqueue_assets( $this->onboarding_wizard_submenu_page->screen_handle() );
		$this->assertTrue( wp_script_is( $handle ) );
		$this->assertTrue( wp_style_is( $handle ) );

		$script_before = implode( '', wp_scripts()->get_data( $handle, 'before' ) );
		$this->assertStringContainsString( 'var ampSettings', $script_before );
		$this->assertStringContainsString( 'AMP_OPTIONS_KEY', $script_before );
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
					'/amp/v1/reader-themes',
					'/amp/v1/scannable-urls?_fields%5B0%5D=url&_fields%5B1%5D=amp_url&_fields%5B2%5D=type&_fields%5B3%5D=label&force_standard_mode=1',
					'/wp/v2/plugins?_fields%5B0%5D=author&_fields%5B1%5D=name&_fields%5B2%5D=plugin&_fields%5B3%5D=status&_fields%5B4%5D=version',
					'/wp/v2/settings',
					'/wp/v2/themes?_fields%5B0%5D=author&_fields%5B1%5D=name&_fields%5B2%5D=status&_fields%5B3%5D=stylesheet&_fields%5B4%5D=template&_fields%5B5%5D=version',
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
