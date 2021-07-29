<?php
/**
 * Tests for ValidatedUrlRESTController.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Validation\ValidatedUrlDataProvider;
use AmpProject\AmpWP\Validation\ValidatedUrlRESTController;
use WP_REST_Controller;
use WP_REST_Request;
use WP_UnitTestCase;

/**
 * Tests for URLValidationRESTController.
 *
 * @group amp-options
 *
 * @coversDefaultClass \AmpProject\AmpWP\Validation\ValidatedUrlRESTControllerTest
 */
class ValidatedUrlRESTControllerTest extends WP_UnitTestCase {
	use ValidationRequestMocking;

	/**
	 * Test instance.
	 *
	 * @var ValidatedUrlRESTController
	 */
	private $controller;

	/**
	 * Test UserAccess instance.
	 *
	 * @var UserAccess
	 */
	private $user_access;

	/**
	 * Test ValidatedUrlDataProvider instance.
	 *
	 * @var ValidatedUrlDataProvider
	 */
	private $validated_url_data_provider;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		do_action( 'rest_api_init' );
		$this->user_access                 = new UserAccess();
		$this->validated_url_data_provider = new ValidatedUrlDataProvider();
		$this->controller                  = new ValidatedUrlRESTController( $this->user_access, $this->validated_url_data_provider );
		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ] );
	}

	/** @covers ::get_registration_action() */
	public function test_get_registration_action() {
		$this->assertEquals( 'rest_api_init', ValidatedUrlRESTController::get_registration_action() );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( ValidatedUrlRESTController::class, $this->controller );
		$this->assertInstanceOf( WP_REST_Controller::class, $this->controller );
	}

	/** @covers ::register() */
	public function test_register() {
		$this->controller->register();

		$this->assertContains( '/amp/v1/validated-urls/(?P<id>[\d]+)', array_keys( rest_get_server()->get_routes() ) );
	}

	/** @covers ::get_item_permissions_check() */
	public function test_get_item_permissions_check() {
		$this->assertWPError( $this->controller->get_item_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/validated-urls/' ) ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );
		$this->assertWPError( $this->controller->get_item_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/validated-urls/' ) ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->user_access->set_user_enabled( wp_get_current_user(), true );
		$this->assertTrue( $this->controller->get_item_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/validated-urls/' ) ) );
	}

	/** @return array */
	public function get_data_for_test_get_item() {
		return [
			'not_int'              => [
				'foo',
				null,
				'administrator',
				'rest_no_route',
			],
			'too_small'            => [
				- 1,
				null,
				'administrator',
				'rest_no_route',
			],
			'empty_post'           => [
				0,
				null,
				'administrator',
				'rest_invalid_param',
			],
			'revision_id'          => [
				'{{id}}',
				'revision',
				'administrator',
				'rest_invalid_param',
			],
			'post_id'              => [
				'{{id}}',
				'post',
				'administrator',
				'rest_invalid_param',
			],
			'as_author'            => [
				'{{id}}',
				\AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
				'author',
				'amp_rest_no_dev_tools',
			],
			'amp_validated_url_id' => [
				'{{id}}',
				\AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
				'administrator',
				false,
			],
		];
	}

	/**
	 * @dataProvider get_data_for_test_get_item()
	 * @covers ::test_get_item()
	 * @covers ::validate_amp_validated_url_post_id_param()
	 *
	 * @param string|int   $post_id        Post ID.
	 * @param string|null  $post_type      Post type.
	 * @param string       $user_role      User role.
	 * @param false|string $expected_error Expected error.
	 */
	public function test_get_item( $post_id, $post_type, $user_role, $expected_error ) {
		add_filter( 'amp_dev_tools_user_default_enabled', '__return_true' );
		$user_id = self::factory()->user->create( [ 'role' => $user_role ] );

		wp_set_current_user( $user_id );

		if ( isset( $post_id ) && '{{id}}' === $post_id && $post_type ) {
			$post_id = self::factory()->post->create( compact( 'post_type' ) );
		}

		$this->controller->register();
		$request  = new WP_REST_Request( 'GET', '/amp/v1/validated-urls/' . $post_id );
		$response = rest_get_server()->dispatch( $request );

		if ( false === $expected_error ) {
			$this->assertFalse( $response->is_error() );
			$data = $response->get_data();
			$this->assertEquals( $data['id'], $post_id );
			$this->assertArrayHasKey( 'url', $data );
			$this->assertArrayHasKey( 'date', $data );
			$this->assertArrayHasKey( 'author', $data );
			$this->assertArrayHasKey( 'stylesheets', $data );
		} else {
			$this->assertTrue( $response->is_error() );
			$error = $response->as_error();
			$this->assertEquals( $expected_error, $error->get_error_code() );
		}
	}

	/**
	 * Tests ValidatedUrlRESTController::get_item_schema.
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
				'id',
				'url',
				'date',
				'author',
				'stylesheets',
				'environment',
			],
			array_keys( $schema['properties'] )
		);
	}
}
