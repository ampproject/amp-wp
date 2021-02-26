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
use WP_REST_Controller;
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

	/** @covers ::get_registration_action() */
	public function test_get_registration_action() {
		$this->assertEquals( 'rest_api_init', URLValidationRESTController::get_registration_action() );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( URLValidationRESTController::class, $this->controller );
		$this->assertInstanceOf( WP_REST_Controller::class, $this->controller );
	}

	/** @covers ::register() */
	public function test_register() {
		$this->controller->register();

		$this->assertContains( '/amp/v1/validate-post-url/(?P<id>[\d]+)', array_keys( rest_get_server()->get_routes() ) );
	}

	/** @covers ::create_item_permissions_check() */
	public function test_create_item_permissions_check() {
		$post = self::factory()->post->create();

		$this->assertWPError( $this->controller->create_item_permissions_check( new WP_REST_Request( 'POST', 'amp/v1/validate-post-url/' . $post ) ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );
		$this->assertWPError( $this->controller->create_item_permissions_check( new WP_REST_Request( 'POST', 'amp/v1/validate-post-url/' . $post ) ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->user_access->set_user_enabled( wp_get_current_user(), true );
		$this->assertTrue( $this->controller->create_item_permissions_check( new WP_REST_Request( 'POST', 'amp/v1/validate-post-url/' . $post ) ) );
	}

	/** @covers ::is_valid_preview_nonce() */
	public function test_is_valid_preview_nonce() {
		$user_id = self::factory()->user->create( [ 'role' => 'author' ] );
		$post_id = self::factory()->post->create( [ 'post_author' => $user_id ] );

		wp_set_current_user( $user_id );

		$this->assertFalse( $this->controller->is_valid_preview_nonce( 'bad', 1 ) );
		$this->assertFalse( $this->controller->is_valid_preview_nonce( 'bad', $post_id ) );
		$this->assertFalse( $this->controller->is_valid_preview_nonce( wp_create_nonce( 'post_preview_' . ( $post_id + 1 ) ), $post_id ) );
		$this->assertTrue( $this->controller->is_valid_preview_nonce( wp_create_nonce( 'post_preview_' . $post_id ), $post_id ) );
	}

	/** @covers ::validate_post_url() */
	public function test_validate_post_url_published() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$post_id = self::factory()->post->create();
		wp_set_current_user( $user_id );

		$request = new WP_REST_Request(
			'POST',
			'amp/v1/validate-post-url/' . $post_id
		);
		$request->set_param( 'id', $post_id );

		$response = $this->controller->validate_post_url( $request );
		$data     = $response->get_data();

		$this->assertEquals(
			[
				'results',
				'review_link',
			],
			array_keys( $data )
		);

		$this->assertNotEmpty( $data['review_link'] );
		$this->assertEquals( 1, count( $data['results'] ) );
	}

	/** @covers ::validate_post_url() */
	public function test_validate_post_url_preview() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$post_id = self::factory()->post->create();
		wp_set_current_user( $user_id );

		// Ensure that a bad preview_nonce fails.
		$request = new WP_REST_Request(
			'POST',
			'amp/v1/validate-post-url/' . $post_id
		);
		$request->set_param( 'preview_nonce', wp_create_nonce( 'bad' ) );
		$response = $this->controller->validate_post_url( $request );
		$this->assertTrue( is_wp_error( $response ) );

		// Check valid post_preview.
		$request = new WP_REST_Request(
			'POST',
			'amp/v1/validate-post-url/' . $post_id
		);
		$request->set_param( 'id', $post_id );
		$preview_nonce = wp_create_nonce( 'post_preview_' . $post_id );
		$this->assertTrue( $this->controller->is_valid_preview_nonce( $preview_nonce, $post_id ) );
		$request->set_param( 'preview_nonce', $preview_nonce );
		$response = $this->controller->validate_post_url( $request );
		$this->assertFalse( is_wp_error( $response ) );
		$data = $response->get_data();

		$this->assertEquals(
			[
				'results',
				'review_link',
			],
			array_keys( $data )
		);

		$this->assertNotEmpty( $data['review_link'] );
		$this->assertEquals( 1, count( $data['results'] ) );
		foreach ( $data['results'] as $result ) {
			$this->assertArrayHasKey( 'error', $result );
			$this->assertArrayHasKey( 'sources', $result['error'] );
			$this->assertArrayHasKey( 'sanitized', $result );
		}
	}

	/**
	 * Tests URLValidationRESTController::get_item_schema.
	 *
	 * @covers ::get_item_schema()
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
