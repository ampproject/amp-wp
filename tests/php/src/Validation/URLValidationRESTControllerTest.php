<?php
/**
 * Tests for URLValidationRESTController.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Validation\URLValidationProvider;
use AmpProject\AmpWP\Validation\URLValidationRESTController;
use WP_REST_Request;
use WP_UnitTestCase;

/**
 * Tests for URLValidationRESTController.
 *
 * @group amp-options
 *
 * @coversDefaultClass \AmpProject\AmpWP\Validation\URLValidationRESTController
 */
class URLValidationRESTControllerTest extends WP_UnitTestCase {
	use ValidationRequestMocking;

	/**
	 * Test instance.
	 *
	 * @var URLValidationRESTController
	 */
	private $controller;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		$this->controller = new URLValidationRESTController( new URLValidationProvider() );
		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ] );
	}

	/** @covers ::register */
	public function test_register() {
		do_action( 'rest_api_init' );
		$this->controller->register();

		$this->assertContains( '/amp/v1/validate-post-url', array_keys( rest_get_server()->get_routes() ) );
	}

	/** @covers ::update_items_permissions_check() */
	public function test_update_items_permissions_check() {
		$this->assertWPError( $this->controller->update_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/validate-post-url' ) ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->assertTrue( $this->controller->update_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/validate-post-url' ) ) );
	}

	/** @covers ::validate_post_url() */
	public function test_validate_url() {
		$id = $this->factory()->post->create(
			[
				'post_content' => '<img data-src="http://my-invalid-attribute.com" />',
			]
		);

		$request = new WP_REST_Request( 'POST', '/amp/v1/validate-post-url' );
		$request->set_body_params( compact( 'id' ) );

		$data = $this->controller->validate_post_url( $request )->get_data();

		$this->assertEquals(
			[
				'results',
				'review_link',
			],
			array_keys( $data )
		);

		$this->assertEquals( '', $data['review_link'] );
		$this->assertEquals( 1, count( $data['results'] ) );
	}

	/**
	 * Tests URLValidationRESTController::get_item_schema.
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
				'review_link',
			],
			array_keys( $schema['properties'] )
		);
	}
}
