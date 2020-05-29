<?php
/**
 * Tests for the reader theme REST controller.
 *
 * @package AMP
 * @since 1.6
 */

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

		do_action( 'rest_api_init' );
		$this->controller = new AMP_Reader_Theme_REST_Controller( new AMP_Reader_Themes() );
	}

	/**
	 * Tests AMP_Reader_Theme_REST_Controller::init
	 *
	 * @covers AMP_Reader_Theme_REST_Controller::init
	 */
	public function test_init() {
		$this->controller->init();

		$this->assertEquals( 10, has_action( 'amp_reader_themes', [ $this->controller, 'prepare_default_reader_themes_for_rest' ] ) );
	}

	/**
	 * Tests AMP_Reader_Theme_REST_Controller::register_routes.
	 *
	 * @covers AMP_Reader_Theme_REST_Controller::register_routes
	 */
	public function test_register_routes() {
		$this->controller->register_routes();

		$this->assertContains( 'amp-wp/v1', rest_get_server()->get_namespaces() );
		$this->assertContains( '/amp-wp/v1/reader-themes', array_keys( rest_get_server()->get_routes( 'amp-wp/v1' ) ) );
	}
}
