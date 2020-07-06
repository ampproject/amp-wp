<?php
/**
 * Tests for OnboardingWizardSubmenu class.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\OnboardingWizardSubmenu;

/**
 * Tests for OnboardingWizardSubmenu  class.
 *
 * @group setup
 *
 * @since 1.6.0
 *
 * @covers OnboardingWizardSubmenu
 */
class Test_OnboardingWizardSubmenu  extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var OnboardingWizardSubmenu
	 */
	private $wizard;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->wizard = new OnboardingWizardSubmenu();
	}

	/**
	 * Tests OnboardingWizardSubmenu::register
	 *
	 * @covers OnboardingWizardSubmenu::register
	 */
	public function test_init() {
		global $submenu;

		wp_set_current_user( 1 ); 

		$this->wizard->register();

		$this->assertEquals( end( $submenu['amp-options'] )[2], 'amp-onboarding-wizard' );
	}
}
