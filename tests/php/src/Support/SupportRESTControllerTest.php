<?php
/**
 * Test cases for SupportRESTController
 *
 * @package AmpProject\AmpWP\Support\Tests
 */

namespace AmpProject\AmpWP\Tests\Support;

use AmpProject\AmpWP\Support\SupportCliCommand;
use AmpProject\AmpWP\Support\SupportRESTController;
use AmpProject\AmpWP\Tests\TestCase;
use WP_Error;

/**
 * Test cases for SupportRESTController
 *
 * @coversDefaultClass \AmpProject\AmpWP\Support\SupportRESTController
 */
class SupportRESTControllerTest extends TestCase {

	/**
	 * Instance of OptionsMenu
	 *
	 * @var SupportCliCommand
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {

		parent::setUp();

		$this->instance = new SupportRESTController();
	}

	/**
	 * @covers ::get_registration_action
	 */
	public function test_get_registration_action() {

		$this->assertEquals(
			'rest_api_init',
			SupportRESTController::get_registration_action()
		);
	}

	/**
	 * @covers ::permission_callback
	 */
	public function test_permission_callback() {

		$this->assertFalse( $this->instance->permission_callback() );

		// Mock User.
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);

		$this->assertTrue( $this->instance->permission_callback() );
	}

	/**
	 * Data provider for $this->test_callback()
	 *
	 * @return array[]
	 */
	public function callback_data_provider() {

		return [
			'fail'    => [
				'request_response' => [
					'status' => 'fail',
					'data'   => [
						'message' => 'Fail to generate UUID',
					],
				],
				'expected'         => new \WP_Error(
					'fail_to_send_data',
					'Failed to send support request. Please try again after some time',
					[ 'status' => 500 ]
				),
			],
			'success' => [
				'request_response' => [
					'status' => 'ok',
					'data'   => [
						'uuid' => 'ampwp-563e5de8-3129-55fb-af71-a6fbd9ef5026',
					],
				],
				'expected'         => [
					'success' => true,
					'data'    => [
						'uuid' => 'ampwp-563e5de8-3129-55fb-af71-a6fbd9ef5026',
					],
				],
			],
		];
	}

	/**
	 * @dataProvider callback_data_provider
	 *
	 * @param array          $request_response Value to mock for response for API.
	 * @param array|WP_Error $expected         Expected AJAX response.
	 *
	 * @covers ::callback
	 */
	public function test_callback( $request_response, $expected ) {

		// Mock User.
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);

		$this->perform_test_on_callback_with( $request_response, $expected );
	}

	/**
	 * To perform test on $this->ajax_callback().
	 *
	 * @param array          $request_response Value to mock for response for API.
	 * @param array|WP_Error $expected         Expected AJAX response.
	 *
	 * @return void
	 */
	private function perform_test_on_callback_with( $request_response, $expected ) {

		$callback_wp_remote = static function () use ( $request_response ) {

			return [
				'body' => wp_json_encode( $request_response ),
			];
		};

		add_filter( 'pre_http_request', $callback_wp_remote );

		$request  = new \WP_REST_Request( 'POST', $this->instance->namespace . '/send-diagnostic', [] );
		$response = $this->instance->callback( $request );

		if ( ! is_wp_error( $response ) ) {
			$response = $response->get_data();
		}

		$this->assertEquals( $expected, $response );

		remove_filter( 'pre_http_request', $callback_wp_remote );
	}
}
