<?php
/**
 * Tests for ScannableURLsRestController.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\Validation;

use AMP_Validation_Manager;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Validation\ScannableURLsRestController;
use WP_Post;
use WP_REST_Controller;
use WP_REST_Request;

/**
 * Tests for ScannableURLsRestController.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Validation\ScannableURLsRestController
 */
class ScannableURLsRestControllerTest extends DependencyInjectedTestCase {
	use ValidationRequestMocking;

	/**
	 * Test instance.
	 *
	 * @var ScannableURLsRestController
	 */
	private $controller;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		do_action( 'rest_api_init' );
		$this->controller = $this->injector->make( ScannableURLsRestController::class );
		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ] );
	}

	/** @covers ::get_registration_action() */
	public function test_get_registration_action() {
		$this->assertEquals( 'rest_api_init', ScannableURLsRestController::get_registration_action() );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( Delayed::class, $this->controller );
		$this->assertInstanceOf( ScannableURLsRestController::class, $this->controller );
		$this->assertInstanceOf( WP_REST_Controller::class, $this->controller );
	}

	/** @covers ::register() */
	public function test_register() {
		$this->controller->register();

		$this->assertContains( '/amp/v1/scannable-urls', array_keys( rest_get_server()->get_routes() ) );
	}

	/** @covers ::get_items_permissions_check() */
	public function test_get_items_permissions_check() {
		$this->assertWPError( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/scannable-urls/' ) ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertTrue( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/scannable-urls/' ) ) );
	}

	/**
	 * @covers ::get_items()
	 * @covers ::prepare_item_for_response()
	 * @covers ::get_item_schema()
	 */
	public function test_get_items() {
		$this->assertTrue( amp_is_legacy() );
		$post_id = self::factory()->post->create( [ 'post_type' => 'post' ] );
		$page_id = self::factory()->post->create( [ 'post_type' => 'page' ] );
		AMP_Validation_Manager::validate_url_and_store( get_permalink( $post_id ) );

		$this->controller->register();
		$item_schema = $this->controller->get_item_schema();

		$request = new WP_REST_Request( 'GET', '/amp/v1/scannable-urls' );

		wp_set_current_user( 0 );
		$response = rest_get_server()->dispatch( $request );
		$this->assertTrue( $response->is_error() );
		$error = $response->as_error();
		$this->assertEquals( 'amp_rest_cannot_validate_urls', $error->get_error_code() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$response = rest_get_server()->dispatch( $request );
		$this->assertTrue( $response->is_error() );
		$error = $response->as_error();
		$this->assertEquals( 'amp_rest_cannot_validate_urls', $error->get_error_code() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$response = rest_get_server()->dispatch( $request );
		$this->assertFalse( $response->is_error() );
		$scannable_urls = $response->get_data();
		$this->assertCount( 2, $scannable_urls, 'Expected there to be only two URLs since in legacy Reader mode.' );

		foreach ( $scannable_urls as $scannable_url_entry ) {
			$this->assertEqualSets(
				array_keys( $item_schema['properties'] ),
				array_keys( $scannable_url_entry )
			);

			$this->assertContains(
				$scannable_url_entry['url'],
				[ get_permalink( $post_id ), get_permalink( $page_id ) ]
			);
			$this->assertContains(
				$scannable_url_entry['amp_url'],
				[ amp_get_permalink( $post_id ), amp_get_permalink( $page_id ) ]
			);

			if ( get_permalink( $post_id ) === $scannable_url_entry['url'] ) {
				$this->assertIsArray( $scannable_url_entry['validated_url_post'] );

				$this->assertEqualSets(
					[ 'id', 'edit_link' ],
					array_keys( $scannable_url_entry['validated_url_post'] )
				);
				$validated_url_post = get_post( $scannable_url_entry['validated_url_post']['id'] );
				$this->assertInstanceOf( WP_Post::class, $validated_url_post );
				$this->assertEquals(
					get_edit_post_link( $validated_url_post, 'raw' ),
					$scannable_url_entry['validated_url_post']['edit_link']
				);

				$this->assertIsArray( $scannable_url_entry['validation_errors'] );
				$this->assertCount( 1, $scannable_url_entry['validation_errors'] );

				$this->assertFalse( $scannable_url_entry['stale'] );
			} else {
				$this->assertNull( $scannable_url_entry['validated_url_post'] );
				$this->assertNull( $scannable_url_entry['validation_errors'] );
				$this->assertNull( $scannable_url_entry['stale'] );
			}
		}

		// Test `force_standard_mode` query parameter.
		$request_with_forced_standard_mode = new WP_REST_Request( 'GET', '/amp/v1/scannable-urls' );
		$request_with_forced_standard_mode->set_param( ScannableURLsRestController::FORCE_STANDARD_MODE, 'true' );
		$response_with_forced_standard_mode = rest_get_server()->dispatch( $request_with_forced_standard_mode );

		$this->assertFalse( $response_with_forced_standard_mode->is_error() );
		$scannable_urls_in_standard_mode = $response_with_forced_standard_mode->get_data();
		$this->assertGreaterThan( 2, count( $scannable_urls_in_standard_mode ), 'Expected more than two URLs since in Standard mode.' );
	}

	/**
	 * Tests ScannableURLsRestController::get_item_schema.
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

		$this->assertEqualSets(
			[
				'url',
				'amp_url',
				'type',
				'label',
				'validated_url_post',
				'validation_errors',
				'stale',
			],
			array_keys( $schema['properties'] )
		);
	}
}
