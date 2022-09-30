<?php
/**
 * Tests for OnboardingWizardSubmenu class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\OnboardingWizardSubmenu;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for OnboardingWizardSubmenu  class.
 *
 * @group onboarding
 *
 * @since 2.0
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\OnboardingWizardSubmenu
 */
class OnboardingWizardSubmenuTest  extends TestCase {

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
	public function set_up() {
		parent::set_up();

		$this->instance = new OnboardingWizardSubmenu();
	}

	public function test__construct() {
		$this->assertInstanceOf( OnboardingWizardSubmenu::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/**
	 * Tests OnboardingWizardSubmenu::register
	 *
	 * @covers ::register
	 */
	public function test_register() {
		global $submenu;

		wp_set_current_user( 1 );

		$this->instance->register();

		$this->assertEquals( end( $submenu[''] )[2], 'amp-onboarding-wizard' );
	}
}
