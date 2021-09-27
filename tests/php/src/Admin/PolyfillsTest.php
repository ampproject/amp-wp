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
use AmpProject\AmpWP\Infrastructure\HasRequirements;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for Polyfills class.
 *
 * @since 2.0
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\Polyfills
 */
class PolyfillsTest extends TestCase {

	/**
	 * Test instance.
	 *
	 * @var Polyfills
	 */
	private $instance;

	/**
	 * Set up.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		global $wp_scripts, $wp_styles;
		$wp_scripts = null;
		$wp_styles  = null;

		$this->instance = new Polyfills();
	}

	/**
	 * Tear down.
	 *
	 * @inheritdoc
	 */
	public function tearDown() {
		parent::tearDown();
		global $wp_scripts, $wp_styles;
		$wp_scripts = null;
		$wp_styles  = null;
	}

	public function test__construct() {
		$this->assertInstanceOf( Polyfills::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( HasRequirements::class, $this->instance );
	}

	/** @covers ::get_requirements() */
	public function test_get_requirements() {
		$this->assertSame( [ 'dependency_support' ], Polyfills::get_requirements() );
	}

	/**
	 * Tests Polyfills::register
	 *
	 * @covers ::register
	 * @covers ::register_shimmed_scripts
	 * @covers ::register_shimmed_styles
	 */
	public function test_registration() {
		if ( function_exists( 'is_gutenberg_page' ) ) {
			$this->assertFalse( is_gutenberg_page() );
		}
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			$this->assertTrue( empty( $screen->is_block_editor ) );
		}

		$this->instance->register();

		// These should pass in WP < 5.6.
		$this->assertTrue( wp_script_is( 'lodash', 'registered' ) );
		$this->assertStringContainsString( '_.noConflict();', wp_scripts()->print_inline_script( 'lodash', 'after', false ) );

		$this->assertTrue( wp_script_is( 'wp-api-fetch', 'registered' ) );
		$this->assertStringContainsString( 'createRootURLMiddleware', wp_scripts()->print_inline_script( 'wp-api-fetch', 'after', false ) );
		$this->assertStringContainsString( 'createNonceMiddleware', wp_scripts()->print_inline_script( 'wp-api-fetch', 'after', false ) );

		$this->assertTrue( wp_script_is( 'wp-hooks', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-i18n', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-dom-ready', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-polyfill', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-url', 'registered' ) );

		$this->assertTrue( wp_style_is( 'wp-components', 'registered' ) );
	}
}
