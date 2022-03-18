<?php
/**
 * Tests for AnalysisResultsRestController.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\PageExperience;

use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\PageExperience\Authorization;
use AmpProject\AmpWP\PageExperience\Engine;
use AmpProject\AmpWP\PageExperience\AnalysisResultsRestController;
use AmpProject\AmpWP\Services;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\RemoteRequest\StubbedRemoteGetRequest;
use WP_REST_Controller;
use WP_REST_Request;

/**
 * Tests for AnalysisResultsRestController.
 *
 * @coversDefaultClass \AmpProject\AmpWP\PageExperience\AnalysisResultsRestController
 */
class AnalysisResultsRestControllerTest extends DependencyInjectedTestCase {

	/**
	 * Test instance.
	 *
	 * @var AnalysisResultsRestController
	 */
	private $controller;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		$stubbed_requests = new StubbedRemoteGetRequest( [ 'https://example.com' => 'bla' ] );
		$this->controller = Services::get( 'rest.px.')
	}

	/** @covers ::get_registration_action() */
	public function test_get_registration_action() {
		$this->assertEquals( 'rest_api_init', AnalysisResultsRestController::get_registration_action() );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( Delayed::class, $this->controller );
		$this->assertInstanceOf( AnalysisResultsRestController::class, $this->controller );
		$this->assertInstanceOf( WP_REST_Controller::class, $this->controller );
	}

	/** @covers ::register() */
	public function test_register() {
		$this->controller->register();

		$this->assertContains( '/amp/v1/analysis-results', array_keys( rest_get_server()->get_routes() ) );
	}

	/** @covers ::create_item_permissions_check() */
	public function test_create_items_permissions_check() {
		$this->assertWPError(
			$this->controller->create_item_permissions_check( new WP_REST_Request( 'POST', '/amp/v1/analysis-results/' ) ),
			'Sorry, you do not have access to run a page experience analysis with the AMP plugin for WordPress.'
		);

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertTrue( $this->controller->create_item_permissions_check( new WP_REST_Request( 'POST', '/amp/v1/analysis-results/' ) ) );
	}

	/**
	 * @covers ::create_items()
	 * @covers ::prepare_item_for_response()
	 * @covers ::get_item_schema()
	 */
	public function test_create_items() {
		$this->controller->register();

		$empty_request = new WP_REST_Request( 'POST', '/amp/v1/analysis-results' );
		$valid_request = new WP_REST_Request( 'POST', '/amp/v1/analysis-results' );
		$valid_request->set_body_params( [ 'url' => 'https://example.com' ] );

		wp_set_current_user( 0 );
		$response = rest_get_server()->dispatch( $empty_request );
		$this->assertTrue( $response->is_error() );
		$error = $response->as_error();
		$this->assertEquals( 'rest_missing_callback_param', $error->get_error_code() );

		wp_set_current_user( 0 );
		$response = rest_get_server()->dispatch( $valid_request );
		$this->assertTrue( $response->is_error() );
		$error = $response->as_error();
		$this->assertEquals( 'amp_rest_no_page_experience_analysis', $error->get_error_code() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$response = rest_get_server()->dispatch( $empty_request );
		$this->assertTrue( $response->is_error() );
		$error = $response->as_error();
		$this->assertEquals( 'rest_missing_callback_param', $error->get_error_code() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$response = rest_get_server()->dispatch( $valid_request );
		$this->assertFalse( $response->is_error() );
		$analysis = $response->get_data();
	}

	/**
	 * Tests AnalysisResultsRestController::get_item_schema.
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
				'status',
				'timestamp',
				'scope',
				'ruleset',
				'results',
			],
			array_keys( $schema['properties'] )
		);
	}
}
