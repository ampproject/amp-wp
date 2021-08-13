<?php
/**
 * Tests for the reader theme REST controller.
 *
 * @package AMP
 * @since 2.0
 */

use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Tests\Helpers\ThemesApiRequestMocking;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for AMP_Reader_Theme_REST_Controller.
 *
 * @group reader-themes
 *
 * @coversDefaultClass AMP_Reader_Theme_REST_Controller
 */
class Test_Reader_Theme_REST_Controller extends TestCase {
	use ThemesApiRequestMocking;

	/**
	 * Test instance.
	 *
	 * @var AMP_Reader_Theme_REST_Controller
	 */
	private $controller;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		delete_transient( 'amp_themes_wporg' );
		do_action( 'rest_api_init' );
		$this->controller = new AMP_Reader_Theme_REST_Controller( new ReaderThemes() );
	}

	/**
	 * Tests AMP_Reader_Theme_REST_Controller::register_routes.
	 *
	 * @covers ::register_routes
	 */
	public function test_register_routes() {
		$this->controller->register_routes();

		$this->assertStringContainsString( 'amp/v1', rest_get_server()->get_namespaces() );
		$this->assertStringContainsString( '/amp/v1/reader-themes', array_keys( rest_get_server()->get_routes( 'amp/v1' ) ) );
	}

	/**
	 * Tests AMP_Reader_Theme_REST_Controller::get_items.
	 *
	 * @covers ::get_items
	 */
	public function test_get_items() {
		$data = $this->controller->get_items( new WP_REST_Request( 'GET', 'amp/v1' ) )->data;

		$actual_reader_themes   = wp_list_pluck( $data, 'slug' );
		$expected_reader_themes = [
			'twentytwentyone',
			'twentytwenty',
			'twentynineteen',
			'twentyseventeen',
			'twentysixteen',
			'twentyfifteen',
			'twentyfourteen',
			'twentythirteen',
			'twentytwelve',
			'twentyeleven',
			'legacy',
		];

		foreach ( $expected_reader_themes as $expected_reader_theme ) {
			$this->assertStringContainsString( $expected_reader_theme, $actual_reader_themes );
		}

		$filter = static function() {
			return [
				[
					'name'           => 'My theme',
					'slug'           => 'my-theme',
					'screenshot_url' => '',
				],
			];
		};

		// Test that only the filtered and AMP Legacy themes are returned.
		$this->controller = new AMP_Reader_Theme_REST_Controller( new ReaderThemes() );
		add_filter( 'amp_reader_themes', $filter );

		$data = $this->controller->get_items( new WP_REST_Request( 'GET', 'amp/v1' ) )->data;

		$this->assertEquals( [ 'my-theme', 'legacy' ], wp_list_pluck( $data, 'slug' ) );
		remove_filter( 'amp_reader_themes', $filter );
	}

	/**
	 * Test the REST Response headers when themes_api was successful.
	 *
	 * @covers ::get_items
	 */
	public function test_get_items_header_with_themes_api_success() {
		$response = $this->controller->get_items( new WP_REST_Request( 'GET', 'amp/v1' ) );
		$this->assertEquals( [], $response->get_headers() );
	}

	/**
	 * Tests the REST response headers when themes_api fails.
	 *
	 * @covers ::get_items
	 */
	public function test_get_items_header_with_themes_api_failure() {
		add_filter( 'themes_api_result', '__return_null' );

		$response = $this->controller->get_items( new WP_REST_Request( 'GET', 'amp/v1' ) );
		$this->assertEquals(
			[ 'X-AMP-Theme-API-Error' => 'The request for reader themes from WordPress.org resulted in an invalid response. Check your Site Health to confirm that your site can communicate with WordPress.org. Otherwise, please try again later or contact your host.' ],
			$response->get_headers()
		);
	}

	/**
	 * Tests the REST response headers when themes_api contains an empty themes array.
	 *
	 * @covers ::get_items
	 */
	public function test_get_items_header_with_themes_api_empty_array() {
		$filter_cb = static function() {
			return (object) [ 'themes' => [] ];
		};
		add_filter( 'themes_api_result', $filter_cb );

		$response = $this->controller->get_items( new WP_REST_Request( 'GET', 'amp/v1' ) );
		$this->assertEquals(
			[ 'X-AMP-Theme-API-Error' => 'The default reader themes cannot be displayed because a plugin appears to be overriding the themes response from WordPress.org.' ],
			$response->get_headers()
		);

	}

	/**
	 * Test that an error is stored in state when themes_api returns an error.
	 *
	 * @covers ::get_items
	 */
	public function test_themes_api_remote_wp_error() {
		$filter_cb = static function() {
			return new WP_Error(
				'amp_test_error',
				'Test message'
			);
		};
		add_filter( 'themes_api_result', $filter_cb );

		$this->add_reader_themes_request_filter();
		$response = $this->controller->get_items( new WP_REST_Request( 'GET', 'amp/v1' ) );
		$headers  = $response->get_headers();
		$this->assertArrayHasKey( 'X-AMP-Theme-API-Error', $headers );
		$this->assertStringStartsWith( 'The request for reader themes from WordPress.org resulted in an invalid response. Check your Site Health to confirm that your site can communicate with WordPress.org. Otherwise, please try again later or contact your host.', $headers['X-AMP-Theme-API-Error'] );

		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$this->assertStringContainsString( 'Test message', $headers['X-AMP-Theme-API-Error'] );
			$this->assertStringContainsString( 'amp_test_error', $headers['X-AMP-Theme-API-Error'] );
		}
	}
}
