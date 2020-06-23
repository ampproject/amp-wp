<?php
/**
 * Tests for AMP_Options_REST_Controller.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\ThemesApiRequestMocking;

/**
 * Tests for AMP_Options_REST_Controller.
 *
 * @group amp-options
 *
 * @covers AMP_Options_REST_Controller
 */
class Test_AMP_Options_REST_Controller extends WP_UnitTestCase {

	use ThemesApiRequestMocking;

	/**
	 * Test instance.
	 *
	 * @var AMP_Options_REST_Controller
	 */
	private $controller;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Requires WordPress 5.0.' );
		}

		$this->add_reader_themes_request_filter();

		do_action( 'rest_api_init' );
		$this->controller = new AMP_Options_REST_Controller( new AMP_Reader_Themes() );
	}

	/**
	 * Tests AMP_Options_REST_Controller::register_routes.
	 *
	 * @covers AMP_Options_REST_Controller::register_routes
	 */
	public function test_register_routes() {
		$this->controller->register_routes();

		$this->assertContains( 'amp/v1', rest_get_server()->get_namespaces() );
		$this->assertContains( '/amp/v1/options', array_keys( rest_get_server()->get_routes( 'amp/v1' ) ) );
	}

	/**
	 * Tests AMP_Options_REST_Controller::get_items_permissions_check.
	 *
	 * @covers AMP_Options_REST_Controller::get_items_permissions_check
	 */
	public function test_get_items_permissions_check() {
		$this->assertWPError( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/options' ) ) );

		wp_set_current_user( 1 );

		$this->assertTrue( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/options' ) ) );
	}

	/**
	 * Tests AMP_Options_REST_Controller::get_items.
	 *
	 * @covers AMP_Options_REST_Controller::get_items.
	 */
	public function test_get_items() {
		$this->assertEquals(
			array_keys( $this->controller->get_items( new WP_REST_Request( 'GET', '/amp/v1/options' ) )->get_data() ),
			[
				'theme_support',
				'reader_theme',
				'mobile_redirect',
			]
		);
	}

	/**
	 * Tests AMP_Options_REST_Controller::update_items.
	 *
	 * @covers AMP_Options_REST_Controller::update_items.
	 */
	public function test_update_items() {
		wp_set_current_user( 1 );

		$request      = new WP_REST_Request( 'POST', '/amp/v1/options' );
		$valid_params = [
			'reader_theme'  => 'twentysixteen',
			'theme_support' => 'transitional',
		];
		$request->set_body_params( $valid_params );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 'transitional', $response->get_data()['theme_support'] );
		$this->assertEquals( 'twentysixteen', $response->get_data()['reader_theme'] );

		// Test that illegal theme_support is not accepted.
		$request = new WP_REST_Request( 'POST', '/amp/v1/options' );
		$request->set_body_params(
			[
				'theme_support' => 'some-unknown-value',
			]
		);
		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals( 'rest_invalid_param', $response->get_data()['code'] );

		// Test that invalid reader_theme is not accepted.
		$request = new WP_REST_Request( 'POST', '/amp/v1/options' );
		$request->set_body_params(
			[
				'reader_theme' => 'twentyninety',
			]
		);
		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals( 'rest_invalid_param', $response->get_data()['code'] );

		// Verify the invalid settings were not set.
		$this->assertArraySubset( $valid_params, AMP_Options_Manager::get_options() );
	}

	/**
	 * Tests AMP_Options_REST_Controller::get_item_schema.
	 *
	 * @covers AMP_Options_REST_Controller::get_item_schema.
	 */
	public function test_get_item_schema() {
		$schema = $this->controller->get_item_schema();

		$this->assertContains( 'theme_support', array_keys( $schema['properties'] ) );
	}
}
