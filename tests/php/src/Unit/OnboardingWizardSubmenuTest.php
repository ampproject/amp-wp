<?php
/**
 * Tests for OnboardingWizardSubmenu class.
 *
 * @package AmpProject\AmpWP\Tests
 */

use AmpProject\AmpWP\Admin\OnboardingWizardSubmenu;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Tests for OnboardingWizardSubmenu  class.
 *
 * @group onboarding
 *
 * @since 1.6.0
 *
 * @covers OnboardingWizardSubmenu
 */
class OnboardingWizardSubmenuTest  extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var OnboardingWizardSubmenu
	 */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->instance = new OnboardingWizardSubmenu();
	}

	/** @covers OnboardingWizardSubmenu::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( OnboardingWizardSubmenu::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/**
	 * Tests OnboardingWizardSubmenu::register
	 *
	 * @covers OnboardingWizardSubmenu::register
	 */
	public function test_register() {
		global $submenu;

		wp_set_current_user( 1 );

		$this->instance->register();

		$this->assertEquals( end( $submenu['amp-options'] )[2], 'amp-onboarding-wizard' );
	}
}
