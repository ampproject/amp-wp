<?php
/**
 * Tests for AMP_Service_Worker.
 *
 * @package AMP
 * @since 1.1
 */

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for AMP_Service_Worker.
 *
 * @covers AMP_Service_Worker
 */
class Test_AMP_Service_Worker extends TestCase {

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		unset( $GLOBALS['current_screen'] );
		if ( ! function_exists( 'wp_service_workers' ) ) {
			$this->markTestSkipped( 'PWA plugin not active.' );
		}
	}

	/**
	 * Test default hooks in init.
	 *
	 * @covers \AMP_Service_Worker::init()
	 */
	public function test_default_init_hooks() {
		remove_all_filters( 'query_vars' );
		remove_all_actions( 'parse_request' );
		remove_all_actions( 'wp' );
		remove_all_actions( 'wp_front_service_worker' );

		AMP_Service_Worker::init();
		$this->assertSame( 10, has_filter( 'query_vars', [ 'AMP_Service_Worker', 'add_query_var' ] ) );
		$this->assertSame( 10, has_action( 'parse_request', [ 'AMP_Service_Worker', 'handle_service_worker_iframe_install' ] ) );
		$this->assertSame( 10, has_action( 'wp', [ 'AMP_Service_Worker', 'add_install_hooks' ] ) );

		$this->assertSame( 10, has_action( 'wp_front_service_worker', [ 'AMP_Service_Worker', 'add_cdn_script_caching' ] ) );
		$this->assertFalse( has_action( 'wp_front_service_worker', [ 'AMP_Service_Worker', 'add_image_caching' ] ) );
		$this->assertFalse( has_action( 'wp_front_service_worker', [ 'AMP_Service_Worker', 'add_google_fonts_caching' ] ) );
	}

	/**
	 * Test unconditional hooks in init.
	 *
	 * @covers \AMP_Service_Worker::init()
	 */
	public function test_theme_support_hooks() {
		remove_all_filters( 'query_vars' );
		remove_all_actions( 'parse_request' );
		remove_all_actions( 'wp' );
		remove_all_actions( 'wp_front_service_worker' );

		add_theme_support(
			'amp',
			[
				'service_worker' => [
					'cdn_script_caching'   => false,
					'image_caching'        => true,
					'google_fonts_caching' => true,
				],
			]
		);

		AMP_Service_Worker::init();
		$this->assertFalse( has_action( 'wp_front_service_worker', [ 'AMP_Service_Worker', 'add_cdn_script_caching' ] ) );
		$this->assertSame( 10, has_action( 'wp_front_service_worker', [ 'AMP_Service_Worker', 'add_image_caching' ] ) );
		$this->assertSame( 10, has_action( 'wp_front_service_worker', [ 'AMP_Service_Worker', 'add_google_fonts_caching' ] ) );
	}

	/**
	 * Test add_query_var().
	 *
	 * @covers \AMP_Service_Worker::add_query_var()
	 */
	public function test_add_query_var() {
		$query_vars = AMP_Service_Worker::add_query_var( [ 'foo' ] );
		$this->assertSame( 'foo', $query_vars[0] );
		$this->assertCount( 2, $query_vars );
		$this->assertIsString( $query_vars[1] );
	}

	/**
	 * Test add_cdn_script_caching().
	 *
	 * @covers \AMP_Service_Worker::add_cdn_script_caching()
	 */
	public function test_add_cdn_script_caching() {
		AMP_Service_Worker::add_cdn_script_caching( wp_service_workers()->get_registry() );
		$this->assertArrayHasKey( 'amp-cdn-runtime-caching', wp_service_workers()->get_registry()->registered );
	}

	/**
	 * Test add_image_caching().
	 *
	 * @covers \AMP_Service_Worker::add_image_caching()
	 */
	public function test_add_image_caching() {
		$before = wp_service_workers()->get_registry()->caching_routes()->get_all();
		AMP_Service_Worker::add_image_caching( wp_service_workers()->get_registry() );
		$after = wp_service_workers()->get_registry()->caching_routes()->get_all();

		$this->assertCount( count( $before ) + 1, $after );
	}

	/**
	 * Test add_google_fonts_caching().
	 *
	 * @covers \AMP_Service_Worker::add_google_fonts_caching()
	 */
	public function test_add_google_fonts_caching() {
		if ( class_exists( 'WP_Service_Worker_Fonts_Integration' ) ) {
			$this->markTestSkipped( 'WP_Service_Worker_Fonts_Integration is present.' );
		}
		$before = wp_service_workers()->get_registry()->caching_routes()->get_all();
		AMP_Service_Worker::add_google_fonts_caching( wp_service_workers()->get_registry() );
		$after = wp_service_workers()->get_registry()->caching_routes()->get_all();
		$this->assertCount( count( $before ) + 2, $after );
	}

	/**
	 * Test get_precached_script_cdn_urls().
	 *
	 * @covers \AMP_Service_Worker::get_precached_script_cdn_urls()
	 */
	public function test_get_precached_script_cdn_urls() {
		$urls = AMP_Service_Worker::get_precached_script_cdn_urls();

		$this->assertArraySubset(
			[
				wp_scripts()->registered['amp-runtime']->src,
				wp_scripts()->registered['amp-bind']->src,
				wp_scripts()->registered['amp-form']->src,
				wp_scripts()->registered['amp-install-serviceworker']->src,
			],
			$urls
		);

		// Comments.
		$this->assertStringNotContainsString(
			wp_scripts()->registered['amp-live-list']->src,
			$urls
		);
		add_theme_support(
			'amp',
			[
				'comments_live_list' => true,
			]
		);
		$this->assertStringContainsString(
			wp_scripts()->registered['amp-live-list']->src,
			AMP_Service_Worker::get_precached_script_cdn_urls()
		);

		// Analytics.
		$this->assertStringNotContainsString(
			wp_scripts()->registered['amp-analytics']->src,
			$urls
		);
		add_filter(
			'amp_analytics_entries',
			static function () {
				return [
					[
						'type'   => 'foo',
						'config' => '{}',
					],
				];
			}
		);
		$this->assertStringContainsString(
			wp_scripts()->registered['amp-analytics']->src,
			AMP_Service_Worker::get_precached_script_cdn_urls()
		);
	}

	/**
	 * Test add_install_hooks().
	 *
	 * @covers \AMP_Service_Worker::add_install_hooks()
	 */
	public function test_add_install_hooks() {
		remove_all_actions( 'amp_post_template_footer' );
		remove_all_actions( 'wp_footer' );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );

		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$this->assertFalse( amp_is_request() );
		AMP_Service_Worker::add_install_hooks();
		$this->assertFalse( has_action( 'amp_post_template_footer', [ 'AMP_Service_Worker', 'install_service_worker' ] ) );
		$this->assertFalse( has_action( 'wp_footer', [ 'AMP_Service_Worker', 'install_service_worker' ] ) );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->assertTrue( amp_is_request() );
		AMP_Service_Worker::add_install_hooks();
		$this->assertSame( 10, has_action( 'wp_footer', [ 'AMP_Service_Worker', 'install_service_worker' ] ) );
		$this->assertFalse( has_action( 'wp_print_scripts', [ 'AMP_Service_Worker', 'wp_print_service_workers' ] ) );
	}

	/**
	 * Test install_service_worker().
	 *
	 * @covers \AMP_Service_Worker::install_service_worker()
	 */
	public function test_install_service_worker() {
		$output = get_echo( [ 'AMP_Service_Worker', 'install_service_worker' ] );

		$this->assertStringContainsString( '<amp-install-serviceworker', $output );
	}

	/**
	 * Test handle_service_worker_iframe_install().
	 *
	 * @covers \AMP_Service_Worker::handle_service_worker_iframe_install()
	 */
	public function test_handle_service_worker_iframe_install() {
		add_filter(
			'wp_die_handler',
			static function () {
				return static function() {
					throw new Exception( 'exited' );
				};
			}
		);

		// Nothing should happen here.
		$this->go_to( home_url() );

		// Now try to go to the iframe endpoint.
		ob_start();
		$exception = null;
		try {
			$this->go_to( add_query_arg( AMP_Service_Worker::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR, '1', home_url() ) );
		} catch ( Exception $e ) {
			$exception = $e;
		}
		$this->assertInstanceOf( 'Exception', $exception );
		$this->assertEquals( 'exited', $exception->getMessage() );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<script>navigator.serviceWorker.register', $output );

		// Go back home to clean up 🤷!
		$this->go_to( home_url() );
	}
}
