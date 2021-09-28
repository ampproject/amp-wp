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
use WP_Scripts;
use WP_Styles;
use WP_UnitTestCase;

/**
 * Tests for Polyfills class.
 *
 * @since 2.0
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\Polyfills
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

	public function test__construct() {
		$this->assertInstanceOf( Polyfills::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/**
	 * Tests Polyfills::register
	 *
	 * @covers ::register
	 * @covers ::register_shimmed_scripts
	 * @covers ::register_shimmed_styles
	 */
	public function test_registration() {
		global $wp_scripts, $wp_styles;
		$this->instance->register();

		$wp_scripts = new WP_Scripts();
		$wp_styles  = new WP_Styles();

		/** This action is documented in includes/class-amp-theme-support.php */
		do_action( 'amp_register_polyfills' );

		// These should pass in WP < 5.6.
		$this->assertTrue( wp_script_is( 'lodash', 'registered' ) );
		$this->assertContains( '_.noConflict();', $wp_scripts->print_inline_script( 'lodash', 'after', false ) );

		$this->assertTrue( wp_script_is( 'wp-api-fetch', 'registered' ) );
		$this->assertContains( 'createRootURLMiddleware', $wp_scripts->print_inline_script( 'wp-api-fetch', 'after', false ) );
		$this->assertContains( 'createNonceMiddleware', $wp_scripts->print_inline_script( 'wp-api-fetch', 'after', false ) );

		$this->assertTrue( wp_script_is( 'wp-hooks', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-i18n', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-dom-ready', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-polyfill', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-url', 'registered' ) );

		$this->assertTrue( wp_style_is( 'wp-components', 'registered' ) );

		unset( $wp_scripts, $wp_styles );
	}
}
