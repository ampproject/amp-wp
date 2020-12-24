<?php
/**
 * Tests for URLValidationRESTController.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\DevTools\UserAccess;
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
	 * Test UserAccess instance.
	 *
	 * @var UserAccess
	 */
	private $user_access;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		do_action( 'rest_api_init' );
		$this->user_access = new UserAccess();
		$this->controller  = new URLValidationRESTController( new URLValidationProvider(), $this->user_access );
		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ] );
	}

	/** @covers ::register */
	public function test_register() {
		$this->controller->register();

		$this->assertContains( '/amp/v1/validate-post-url/(?P<id>[\d]+)', array_keys( rest_get_server()->get_routes() ) );
	}

	/** @covers ::get_item_permissions_check() */
	public function test_get_item_permissions_check() {
		$post = $this->factory()->post->create();

		$this->assertWPError( $this->controller->get_item_permissions_check( new WP_REST_Request( 'GET', 'amp/v1/validate-post-url/' . $post ) ) );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'author' ] ) );
		$this->assertWPError( $this->controller->get_item_permissions_check( new WP_REST_Request( 'GET', 'amp/v1/validate-post-url/' . $post ) ) );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->user_access->set_user_enabled( wp_get_current_user(), true );
		$this->assertTrue( $this->controller->get_item_permissions_check( new WP_REST_Request( 'GET', 'amp/v1/validate-post-url/' . $post ) ) );
	}

	/** @covers ::validate_post_url() */
	public function test_validate_post_url() {
		$id = $this->factory()->post->create(
			[
				'post_content' => '<img data-src="http://my-invalid-attribute.com" />',
			]
		);

		$request = new WP_REST_Request( 'GET', 'amp/v1/validate-post-url/' . $id );
		$request->set_url_params( [ 'context' => URLValidationRESTController::CONTEXT_EDITOR ] );

		$response = $this->controller->validate_post_url( $request );
		$data     = $response->get_data();

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
