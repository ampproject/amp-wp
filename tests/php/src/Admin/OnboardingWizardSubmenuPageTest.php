<?php
/**
 * Tests for OnboardingWizardSubmenuPage class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\GoogleFonts;
use AmpProject\AmpWP\Admin\OnboardingWizardSubmenuPage;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Admin\RESTPreloader;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use WP_UnitTestCase;

/**
 * Tests for OnboardingWizardSubmenuPage class.
 *
 * @group onboarding
 *
 * @since 2.0
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\OnboardingWizardSubmenuPage
 */
class OnboardingWizardSubmenuPageTest extends WP_UnitTestCase {

	use AssertContainsCompatibility;

	/**
	 * Test instance.
	 *
	 * @var OnboardingWizardSubmenuPage
	 */
	private $page;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->page = new OnboardingWizardSubmenuPage( new GoogleFonts(), new ReaderThemes(), new RESTPreloader() );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( OnboardingWizardSubmenuPage::class, $this->page );
		$this->assertInstanceOf( Delayed::class, $this->page );
		$this->assertInstanceOf( Service::class, $this->page );
		$this->assertInstanceOf( Registerable::class, $this->page );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::register
	 *
	 * @covers ::register
	 */
	public function test_register() {
		$this->page->register();

		$this->assertEquals( 10, has_action( 'admin_head-admin_page_amp-onboarding-wizard', [ $this->page, 'override_template' ] ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->page, 'enqueue_assets' ] ) );
		$this->assertEquals( 10, add_filter( 'admin_title', [ $this->page, 'override_title' ] ) );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::override_title
	 *
	 * @covers ::override_title
	 */
	public function test_override_title() {
		set_current_screen( 'index.php' );

		$this->assertEquals( 'Index - WordPress', $this->page->override_title( 'Index - WordPress' ) );

		set_current_screen( $this->page->screen_handle() );

		$this->assertEquals( 'AMP Onboarding Wizard - WordPress', $this->page->override_title( ' - WordPress' ) );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::render
	 *
	 * @covers ::render
	 */
	public function test_render() {
		ob_start();

		$this->page->render();

		$this->assertStringContains( '<div class="amp" id="amp-onboarding-wizard"></div>', ob_get_clean() );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::screen_handle
	 *
	 * @covers ::screen_handle
	 */
	public function test_screen_handle() {
		$this->assertEquals( $this->page->screen_handle(), 'admin_page_amp-onboarding-wizard' );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::enqueue_assets
	 *
	 * @covers ::enqueue_assets
	 */
	public function test_enqueue_assets() {
		$handle = 'amp-onboarding-wizard';

		$this->page->enqueue_assets( $this->page->screen_handle() );
		$this->assertTrue( wp_script_is( $handle ) );
		$this->assertTrue( wp_style_is( $handle ) );
	}
}
