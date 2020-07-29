<?php
/**
 * Tests for GoogleFonts class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\GoogleFonts;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_UnitTestCase;

/**
 * Tests for GoogleFonts class.
 *
 * @since 2.0
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
	 * @covers GoogleFonts::get_handle
	 * @covers GoogleFonts::register
	 */
	public function test_register_style() {
		$this->instance->register_style( wp_styles() );

		$this->assertTrue( wp_style_is( 'amp-admin-google-fonts', 'registered' ) );
	}
}
