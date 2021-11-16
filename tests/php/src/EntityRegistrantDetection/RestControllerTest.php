<?php
/**
 * Test case for RestController
 *
 * @package AmpProject\AmpWP\EntityRegistrantDetection\Tests
 */

namespace AmpProject\AmpWP\EntityRegistrantDetection\Tests;

use AmpProject\AmpWP\EntityRegistrantDetection\EntityRegistrantDetectionManager;
use AmpProject\AmpWP\EntityRegistrantDetection\RestController;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @coversDefaultClass \AmpProject\AmpWP\EntityRegistrantDetection\RestController
 */
class RestControllerTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/**
	 * Instance of RestController
	 *
	 * @var RestController
	 */
	public $instance;

	/**
	 * Set up.
	 *
	 * @inheritdoc
	 */
	public function setUp() {

		parent::setUp();

		$this->instance = $this->injector->make( RestController::class );
	}

	/**
	 * @covers ::__construct()
	 */
	public function test_construct() {

		$this->assertEquals(
			'amp/v1',
			$this->get_private_property( $this->instance, 'namespace' )
		);

		$this->assertEquals(
			'entity-registrants',
			$this->get_private_property( $this->instance, 'rest_base' )
		);
	}

	/**
	 * @covers ::get_registration_action()
	 */
	public function test_get_registration_action() {

		$this->assertEquals(
			'rest_api_init',
			RestController::get_registration_action()
		);
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {

		do_action( 'rest_api_init' );

		$rest_server = rest_get_server();

		$namespaces = $this->get_private_property( $rest_server, 'namespaces' );

		$this->assertContains(
			'amp/v1',
			$rest_server->get_namespaces()
		);

		$this->assertContains(
			'/amp/v1/entity-registrants',
			array_keys( $namespaces['amp/v1'] )
		);
	}

	/**
	 * @covers ::get_items_permissions_check()
	 * @covers ::get_items()
	 */
	public function test_get_items() {

		// Test 1: Missing parameter.
		$request       = new WP_REST_Request( WP_REST_Server::READABLE, '/amp/v1/entity-registrants' );
		$response      = rest_get_server()->dispatch( $request );
		$response_data = $response->get_data();

		$this->assertTrue( $response->is_error() );
		$this->assertEquals( 'rest_missing_callback_param', $response_data['code'] );

		// Test 2: Valid nonce without user access.
		$request->set_query_params(
			[
				EntityRegistrantDetectionManager::NONCE_QUERY_VAR => EntityRegistrantDetectionManager::get_nonce(),
			]
		);

		$response      = rest_get_server()->dispatch( $request );
		$response_data = $response->get_data();
		$this->assertTrue( $response->is_error() );
		$this->assertEquals( 'rest_forbidden', $response_data['code'] );

		// Test 3: Invalid nonce with user access.
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);

		$request->set_query_params(
			[
				EntityRegistrantDetectionManager::NONCE_QUERY_VAR => 'invalid_nonce',
			]
		);

		$response      = rest_get_server()->dispatch( $request );
		$response_data = $response->get_data();

		$this->assertTrue( $response->is_error() );
		$this->assertEquals( 'http_request_failed', $response_data['code'] );

		// Test 4: Valid nonce with user access.
		$request->set_query_params(
			[
				EntityRegistrantDetectionManager::NONCE_QUERY_VAR => EntityRegistrantDetectionManager::get_nonce(),
			]
		);

		$response      = rest_get_server()->dispatch( $request );
		$response_data = $response->get_data();

		$this->assertFalse( $response->is_error() );

		foreach ( [ 'post_types', 'taxonomies', 'blocks', 'shortcodes' ] as $entity_type ) {
			$this->assertArrayHasKey( $entity_type, $response_data );
		}
	}
}
