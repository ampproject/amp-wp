<?php
/**
 * Tests for ValidationRESTController.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Validation\ValidationProvider;
use AmpProject\AmpWP\Validation\ValidationRESTController;
use AmpProject\AmpWP\Validation\ValidationURLProvider;
use WP_REST_Request;
use WP_UnitTestCase;

/**
 * Tests for ValidationRESTController.
 *
 * @group amp-options
 *
 * @coversDefaultClass \AmpProject\AmpWP\Validation\ValidationRESTController
 */
class ValidationRESTControllerTest extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var ValidationRESTController
	 */
	private $controller;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		$this->controller = new ValidationRESTController();
		add_filter( 'pre_http_request', [ ValidationRequestMocking::class, 'get_validate_response' ] );
	}

	public function test_register() {
		do_action( 'rest_api_init' );
		$this->controller->register();

		$this->assertContains( '/amp/v1/validate', array_keys( rest_get_server()->get_routes() ) );

	}

	/**
	 * Tests ValidationRESTController::get_items_permissions_check.
	 *
	 * @covers ::get_items_permissions_check
	 */
	public function test_get_items_permissions_check() {
		$this->assertWPError( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/validate' ) ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->assertTrue( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/validate' ) ) );
	}

	/**
	 * Tests ValidationRESTController::validate_urls.
	 *
	 * @covers ::validate_urls
	 */
	public function test_validate_urls() {
		$this->factory()->post->create(
			[
				'post_content' => '<img data-src="http://my-invalid-attribute.com" />',
			]
		);

		$urls = ( new ValidationURLProvider() )->get_urls();

		$request = new WP_REST_Request( 'POST', '/amp/v1/validate' );
		$request->set_body_params( compact( 'urls' ) );

		$data = $this->controller->validate_urls( $request )->get_data();

		$this->assertEquals(
			[
				'results',
				'total_errors',
				'unaccepted_errors',
				'validity_by_type',
				'remaining_urls',
			],
			array_keys( $data )
		);

		$this->assertEquals( 1, $data['total_errors'] );
		$this->assertEquals( 0, $data['unaccepted_errors'] );
		$this->assertEquals( 1, count( $data['results'] ) );
		$this->assertEquals( [ 'home' ], array_keys( $data['validity_by_type'] ) );
		$this->assertEquals( 5, count( $data['remaining_urls'] ) );

		( new ValidationProvider() )->with_lock(
			function() use ( $request ) {

				$this->assertWPError( $this->controller->validate_urls( $request ) );
			}
		);
	}

	/**
	 * Tests ValidationRESTController::get_item_schema.
	 *
	 * @covers ::get_item_schema
	 */
	public function test_get_item_schema() {
		$schema = $this->controller->get_item_schema();

		$this->assertEquals(
			[
				'$schema',
				'title',
				'type',
				'properties',
			],
			array_keys( $schema )
		);

		$this->assertEquals(
			[
				'results',
				'total_errors',
				'unaccepted_errors',
				'validity_by_type',
				'remaining_urls',
			],
			array_keys( $schema['properties'] )
		);
	}
}
