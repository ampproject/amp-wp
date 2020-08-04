<?php
/**
 * Tests for Polyfills class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\Polyfills;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_UnitTestCase;

/**
 * Tests for Polyfills class.
 *
 * @since 2.0
 *
 * @covers Polyflls.
 */
class PolyfillsTest extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var Polyfills
	 */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->instance = new Polyfills();
	}

	/** @covers Polyfills::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( Polyfills::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/**
	 * Tests Polyfills::register
	 *
	 * @covers Polyfills::register
	 * @covers Polyfills::register_shimmed_scripts
	 * @covers Polyfills::register_shimmed_styles
	 */
	public function test_registration() {
		$this->instance->register();

		// These should pass in WP 4.9 tests.
		$this->assertTrue( wp_script_is( 'lodash', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-api-fetch', 'registered' ) );
		$this->assertTrue( wp_style_is( 'wp-components', 'registered' ) );
	}
}
