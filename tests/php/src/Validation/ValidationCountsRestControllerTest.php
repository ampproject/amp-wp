<?php
/**
 * Tests for OptionsRESTController.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Validation;

use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Validation\ValidationCountsRestController;
use WP_REST_Request;

/**
 * Tests for OptionsRESTController.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Validation\ValidationCountsRestController
 */
class ValidationCountsRestControllerTest extends DependencyInjectedTestCase {

	/**
	 * Test instance.
	 *
	 * @var ValidationCountsRestController
	 */
	private $controller;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		$this->controller = $this->injector->make( ValidationCountsRestController::class );
	}

	/**
	 * Test ::get_registration_action().
	 *
	 * @covers ::get_registration_action()
	 */
	public function test_get_registration_action() {
		self::assertEquals( 'rest_api_init', ValidationCountsRestController::get_registration_action() );
	}

	/**
	 * Test ::get_items_permissions_check().
	 *
	 * @covers ::get_items_permissions_check()
	 */
	public function test_get_items_permissions_check() {
		$admin_user = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );

		$this->assertWPError( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/unreviewed-validation-counts' ) ) );

		wp_set_current_user( $admin_user->ID );
		$this->assertWPError( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/unreviewed-validation-counts' ) ) );

		update_user_meta( $admin_user->ID, UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED, wp_json_encode( true ) );
		$this->assertTrue( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/unreviewed-validation-counts' ) ) );
	}

	/**
	 * Test ::get_items().
	 *
	 * @covers ::get_items()
	 */
	public function test_get_items() {
		$admin_user = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_user->ID );
		update_user_meta( $admin_user->ID, UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED, wp_json_encode( true ) );

		$request = new WP_REST_Request( 'GET', '/amp/v1/unreviewed-validation-counts' );

		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals(
			[
				'validated_urls' => 0,
				'errors'         => 0,
			],
			$response->get_data()
		);

		AMP_Validated_URL_Post_Type::store_validation_errors(
			[
				[
					'code' => 'foobar',
				],
			],
			get_permalink( self::factory()->post->create() )
		);

		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals(
			[
				'validated_urls' => 1,
				'errors'         => 1,
			],
			$response->get_data()
		);
	}

	/**
	 * Test ::get_item_schema().
	 *
	 * @covers ::get_item_schema()
	 */
	public function test_get_item_schema() {
		self::assertEquals( [ 'validation_urls', 'errors' ], array_keys( $this->controller->get_item_schema()['properties'] ) );
	}
}
