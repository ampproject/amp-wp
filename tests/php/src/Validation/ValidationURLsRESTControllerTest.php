<?php
/**
 * Tests for ValidationURLsRESTController.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Validation\ValidationURLsRESTController;
use WP_REST_Request;
use WP_UnitTestCase;

/**
 * Tests for ValidationURLsRESTController.
 *
 * @group amp-options
 *
 * @coversDefaultClass \AmpProject\AmpWP\ValidationURLsRESTController
 */
class ValidationURLsRESTControllerTest extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var ValidationURLsRESTController
	 */
	private $controller;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		$this->controller = new ValidationURLsRESTController();
	}

	/**
	 * Tests ValidationURLsRESTController::get_items_permissions_check.
	 *
	 * @covers ::get_items_permissions_check
	 */
	public function test_get_items_permissions_check() {
		$this->assertWPError( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/validation-urls' ) ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->assertTrue( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/validation-urls' ) ) );
	}

	/**
	 * Tests ValidationURLsRESTController::get_urls.
	 *
	 * @covers ::get_urls
	 */
	public function test_get_urls() {
		$this->factory()->post->create_many( 20 );

		$data = $this->controller->get_urls(
			[
				'offset'  => 0,
				'limit'   => 2,
				'include' => [],
			]
		)->get_data();

		$this->assertEquals( 7, count( $data['urls'] ) );

		foreach ( $data['urls'] as $item ) {
			$this->assertEquals( [ 'url', 'type' ], array_keys( $item ) );
		}
	}

	/**
	 * Tests ValidationURLsRESTController::get_item_schema.
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
				'url',
				'type',
			],
			array_keys( $schema['properties']['urls']['items']['properties'] )
		);
	}
}
