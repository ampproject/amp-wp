<?php
/**
 * Tests for the reader theme REST controller.
 *
 * @package AMP
 * @since 2.0
 */

use AmpProject\AmpWP\Admin\ReaderThemes;

/**
 * Tests for AMP_Reader_Theme_REST_Controller.
 *
 * @group reader-themes
 *
 * @covers AMP_Reader_Theme_REST_Controller
 */
class Test_Reader_Theme_REST_Controller extends WP_UnitTestCase {
	/**
	 * Test instance.
	 *
	 * @var AMP_Reader_Theme_REST_Controller
	 */
	private $controller;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		if ( ! amp_should_use_new_onboarding() ) {
			$this->markTestSkipped( 'Requires WordPress 5.0.' );
		}

		do_action( 'rest_api_init' );
		$this->controller = new AMP_Reader_Theme_REST_Controller( new ReaderThemes() );
	}

	/**
	 * Tests AMP_Reader_Theme_REST_Controller::register_routes.
	 *
	 * @covers AMP_Reader_Theme_REST_Controller::register_routes
	 */
	public function test_register_routes() {
		$this->controller->register_routes();

		$this->assertContains( 'amp/v1', rest_get_server()->get_namespaces() );
		$this->assertContains( '/amp/v1/reader-themes', array_keys( rest_get_server()->get_routes( 'amp/v1' ) ) );
	}

	/**
	 * Tests AMP_Reader_Theme_REST_Controller::get_items.
	 *
	 * @covers AMP_Reader_Theme_REST_Controller::get_items
	 */
	public function test_get_items() {
		$data = $this->controller->get_items( new WP_REST_Request( 'GET', 'amp/v1' ) )->data;

		$this->assertArraySubset(
			[
				'twentytwenty',
				'twentynineteen',
				'twentyseventeen',
				'twentysixteen',
				'twentyfifteen',
				'twentyfourteen',
				'twentythirteen',
				'twentytwelve',
				'twentyeleven',
				'legacy',
			],
			wp_list_pluck( $data, 'slug' )
		);

		$filter = static function() {
			return [
				[
					'name'           => 'My theme',
					'slug'           => 'my-theme',
					'screenshot_url' => '',
				],
			];
		};

		// Test that only the filtered and AMP Legacy themes are returned.
		$this->controller = new AMP_Reader_Theme_REST_Controller( new ReaderThemes() );
		add_filter( 'amp_reader_themes', $filter );

		$data = $this->controller->get_items( new WP_REST_Request( 'GET', 'amp/v1' ) )->data;

		$this->assertEquals( [ 'my-theme', 'legacy' ], wp_list_pluck( $data, 'slug' ) );
		remove_filter( 'amp_reader_themes', $filter );
	}
}
