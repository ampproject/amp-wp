<?php
/**
 * Tests for RESTPreloader class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\RESTPreloader;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for RESTPreloader class.
 *
 * @since 2.0
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\RESTPreloader
 */
class RESTPreloaderTest extends TestCase {

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
	public function set_up() {
		parent::set_up();

		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Requires WordPress 5.0.' );
		}

		$this->instance = new RESTPreloader();
	}

	/**
	 * @covers ::add_preloaded_path
	 * @covers ::preload_data
	 */
	public function test_adding_preloaded_data() {
		global $wp_scripts;

		set_current_screen( 'index.php' );
		$this->instance->add_preloaded_path( '/wp/v2/posts' );
		do_action( 'admin_enqueue_scripts' );

		$result = end( $wp_scripts->registered['wp-api-fetch']->extra['after'] );

		$this->assertEquals(
			'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( {"\/wp\/v2\/posts":{"body":[],"headers":{"X-WP-Total":0,"X-WP-TotalPages":0}}} ) );',
			$result
		);

		set_current_screen( 'front' );
	}
}
