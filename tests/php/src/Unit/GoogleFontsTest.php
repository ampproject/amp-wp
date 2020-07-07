<?php
/**
 * Tests for GoogleFonts class.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\GoogleFonts;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Tests for GoogleFonts class.
 *
 * @group google-fonts
 *
 * @since 1.6.0
 *
 * @covers GoogleFonts
 */
class GoogleFontsTest extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var GoogleFonts
	 */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->instance = new GoogleFonts();
	}

	/** @covers GoogleFonts::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( GoogleFonts::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/**
	 * Tests OnboardingWizardSubmenu::register
	 *
	 * @covers OnboardingWizardSubmenu::get_handle
	 * @covers OnboardingWizardSubmenu::register
	 */
	public function test_register() {
		$this->instance->register();

		$this->assertTrue( wp_style_is( 'amp-admin-google-fonts', 'registered' ) );
	}
}
