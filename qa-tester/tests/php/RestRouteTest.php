<?php
/**
 * Class RestRouteTest.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester\Tests;

use AmpProject\AmpWP_QA_Tester\RestRoute;
use PHPUnit\Framework\TestCase;

/**
 * Tests for RestRoute.
 *
 * @package AmpProject\AmpWP_QA_Tester
 * @coversDefaultClass \AmpProject\AmpWP_QA_Tester\RestRoute
 */
class RestRouteTest extends TestCase {

	/**
	 * Instance of RestRoute class.
	 *
	 * @var RestRoute
	 */
	private $rest_route;

	/**
	 * Set up.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		$this->rest_route = new RestRoute();
	}

	/**
	 * Test register.
	 *
	 * @covers ::register()
	 */
	public function test_register() {
		$this->rest_route->register();
		$this->assertEquals( 10, has_action( 'rest_api_init', [ $this->rest_route, 'register_route' ] ) );
	}
}
