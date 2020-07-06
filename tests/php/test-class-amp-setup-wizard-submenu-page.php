<?php
/**
 * Tests for OnboardingWizardSubmenuPage class.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\OnboardingWizardSubmenuPage;
use AmpProject\AmpWP\Tests\AssertContainsCompatibility;

/**
 * Tests for OnboardingWizardSubmenuPage class.
 *
 * @group setup
 *
 * @since 1.6.0
 *
 * @covers OnboardingWizardSubmenu
 */
class Test_OnboardingWizardSubmenuPage extends WP_UnitTestCase {

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

		$this->page = new OnboardingWizardSubmenuPage();
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::init
	 *
	 * @covers OnboardingWizardSubmenuPage::init
	 */
	public function test_init() {
		$this->page->init();

		$this->assertEquals( 10, has_action( 'admin_head-amp_page_amp-setup', [ $this->page, 'override_template' ] ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->page, 'enqueue_assets' ] ) );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::render
	 *
	 * @covers OnboardingWizardSubmenuPage::render
	 */
	public function test_render() {
		ob_start();

		$this->page->render();

		$this->assertStringContains( '<div id="amp-settings"></div>', ob_get_clean() );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::screen_handle
	 *
	 * @covers OnboardingWizardSubmenuPage::screen_handle
	 */
	public function test_screen_handle() {
		$this->assertEquals( $this->page->screen_handle(), 'amp_page_amp-setup' );
	}

	/**
	 * Tests OnboardingWizardSubmenuPage::enqueue_assets
	 *
	 * @covers OnboardingWizardSubmenuPage::enqueue_assets
	 */
	public function test_enqueue_assets() {
		$handle = 'amp-settings';

		$this->page->enqueue_assets( $this->page->screen_handle() );
		$this->assertTrue( wp_script_is( $handle ) );
	}
}
