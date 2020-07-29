<?php
/**
 * Tests for RESTPreloader class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\RESTPreloader;
use WP_UnitTestCase;

/**
 * Tests for RESTPreloader class.
 *
 * @since 2.0
 *
 * @covers RESTPreloader
 */
class RESTPreloaderTest extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var RESTPreloader
	 */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Requires WordPress 5.0.' );
		}

		$this->instance = new RESTPreloader();
	}

	/** @covers RESTPreloader::is_needed */
	public function test_is_needed() {
		global $current_screen;

		$this->assertFalse( RESTPreloader::is_needed() );

		set_current_screen( 'index.php' );
		$this->assertFalse( RESTPreloader::is_needed() );

		add_filter(
			'amp_preload_rest_paths',
			function( $paths ) {
				return $paths;
			}
		);
		$this->assertTrue( RESTPreloader::is_needed() );

		$current_screen = null;
		$this->assertFalse( RESTPreloader::is_needed() );
	}

	/**
	 * @covers RESTPreloader::is_needed
	 * @covers RESTPreloader::preload_data
	 */
	public function test_register() {
		global $wp_scripts;

		add_filter(
			'amp_preload_rest_paths',
			function() {
				return [ '/wp/v2/posts' ];
			}
		);

		$this->instance->register();

		$this->assertEquals(
			'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( {"\/wp\/v2\/posts":{"body":[],"headers":{"X-WP-Total":0,"X-WP-TotalPages":0}}} ) );',
			end( $wp_scripts->registered['wp-api-fetch']->extra['after'] )
		);
	}
}
