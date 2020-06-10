<?php
/**
 * Tests for AMP_Options_REST_Controller.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Option;

/**
 * Tests for AMP_Options_REST_Controller.
 *
 * @group amp-options
 *
 * @covers AMP_Options_REST_Controller
 */
class Test_AMP_Options_REST_Controller extends WP_UnitTestCase {

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

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );

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
			]
		);
	}

	/**
	 * Tests AMP_Options_REST_Controller::update_items.
	 *
	 * @covers AMP_Options_REST_Controller::update_items.
	 */
	public function test_update_items() {
		$this->assertEquals(
			'reader',
			$this->controller->get_items( new WP_REST_Request( 'GET', '/amp/v1/options' ) )->get_data()['theme_support']
		);

		wp_set_current_user( 1 );

		$request = new WP_REST_Request( 'POST', '/amp/v1/options' );
		$request->set_body_params( [ 'theme_support' => 'transitional' ] );
		$response = $this->controller->update_items( $request );

		$this->assertEquals( 'transitional', $response->get_data()['theme_support'] );
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
