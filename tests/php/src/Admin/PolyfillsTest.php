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
	public function set_up() {
		parent::set_up();

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
	public function tear_down() {
		global $wp_scripts, $wp_styles;
		$wp_scripts = null;
		$wp_styles  = null;
		parent::tear_down();
	}

	public function test__construct() {
		$this->assertInstanceOf( Polyfills::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
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
		$this->assertStringContainsString( '_.noConflict();', self::get_inline_script( 'lodash', 'after' ) );

		$this->assertTrue( wp_script_is( 'wp-api-fetch', 'registered' ) );
		$this->assertStringContainsString( 'createRootURLMiddleware', self::get_inline_script( 'wp-api-fetch', 'after' ) );
		$this->assertStringContainsString( 'createNonceMiddleware', self::get_inline_script( 'wp-api-fetch', 'after' ) );

		$this->assertTrue( wp_script_is( 'wp-hooks', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-i18n', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-dom-ready', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-polyfill', 'registered' ) );
		$this->assertTrue( wp_script_is( 'wp-url', 'registered' ) );

		$this->assertTrue( wp_style_is( 'wp-components', 'registered' ) );
	}

	/**
	 * Get inline script.
	 *
	 * @param string $handle Script handle.
	 * @param string $position Script position.
	 * @param bool   $display Whether to display the script.
	 *
	 * @return string
	 */
	public static function get_inline_script( $handle, $position = 'after', $display = false ) {
		if ( method_exists( wp_scripts(), 'get_inline_script_tag' ) ) {
			return wp_scripts()->get_inline_script_tag( $handle, $position );
		} else {
			return wp_scripts()->print_inline_script( $handle, $position, $display );
		}
	}
}
