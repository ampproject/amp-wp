<?php
/**
 * Tests for OptionsRESTController.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\Unit;

use AMP_Options_Manager;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\OptionsRESTController;
use AmpProject\AmpWP\PluginRegistry;
use AmpProject\AmpWP\PluginSuppression;
use AmpProject\AmpWP\Tests\Helpers\ThemesApiRequestMocking;
use WP_REST_Request;
use WP_UnitTestCase;

/**
 * Tests for OptionsRESTController.
 *
 * @group amp-options
 *
 * @covers OptionsRESTController
 */
class OptionsRESTControllerTest extends WP_UnitTestCase {

	use ThemesApiRequestMocking;

	/**
	 * Test instance.
	 *
	 * @var OptionsRESTController
	 */
	private $controller;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Requires WordPress 5.0.' );
		}

		$this->add_reader_themes_request_filter();

		$this->controller = new OptionsRESTController( new ReaderThemes(), new PluginSuppression( new PluginRegistry() ) );
	}

	/**
	 * Tests OptionsRESTController::get_items_permissions_check.
	 *
	 * @covers OptionsRESTController::get_items_permissions_check
	 */
	public function test_get_items_permissions_check() {
		$this->assertWPError( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/options' ) ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->assertTrue( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/options' ) ) );
	}

	/**
	 * Tests OptionsRESTController::get_items.
	 *
	 * @covers OptionsRESTController::get_items.
	 */
	public function test_get_items() {
		$data = $this->controller->get_items( new WP_REST_Request( 'GET', '/amp/v1/options' ) )->get_data();
		$this->assertEquals(
			array_keys( $data ),
			[
				'theme_support',
				'reader_theme',
				'mobile_redirect',
				'plugin_configured',
				'all_templates_supported',
				'suppressed_plugins',
				'supported_templates',
				'supported_post_types',
				'preview_permalink',
				'suppressible_plugins',
				'supportable_post_types',
				'supportable_templates',
				'onboarding_wizard_link',
				'customizer_link',
			]
		);

		$this->assertEquals( $data['suppressible_plugins'], [] );
		$this->assertEquals( $data['preview_permalink'], null );
		$this->assertEquals( $data['suppressed_plugins'], [] );
		$this->assertEquals( $data['suppressible_plugins'], [] );
		$this->assertContains( 'post', wp_list_pluck( $data['supportable_post_types'], 'name' ) );
		$this->assertEquals( $data['supported_post_types'], [ 'post' ] );
		$this->assertContains( 'is_singular', wp_list_pluck( $data['supportable_templates'], 'id' ) );
		$this->assertEquals( $data['supported_templates'], [ 'is_singular' ] );
	}

	/**
	 * Tests OptionsRESTController::update_items.
	 *
	 * @covers OptionsRESTController::update_items.
	 */
	public function test_update_items() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$request      = new WP_REST_Request( 'POST', '/amp/v1/options' );
		$valid_params = [
			'reader_theme'  => 'twentysixteen',
			'theme_support' => 'transitional',
		];
		$request->set_body_params( $valid_params );
		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 'transitional', $response->get_data()['theme_support'] );
		$this->assertEquals( 'twentysixteen', $response->get_data()['reader_theme'] );

		// Test that illegal theme_support is not accepted.
		$request = new WP_REST_Request( 'POST', '/amp/v1/options' );
		$request->set_body_params(
			[
				'theme_support' => 'some-unknown-value',
			]
		);
		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals( 'rest_invalid_param', $response->get_data()['code'] );

		// Test that invalid reader_theme is not accepted.
		$request = new WP_REST_Request( 'POST', '/amp/v1/options' );
		$request->set_body_params(
			[
				'reader_theme' => 'twentyninety',
			]
		);
		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals( 'rest_invalid_param', $response->get_data()['code'] );

		// Verify the invalid settings were not set.
		$this->assertArraySubset( $valid_params, AMP_Options_Manager::get_options() );
	}

	/**
	 * Tests OptionsRESTController::get_item_schema.
	 *
	 * @covers OptionsRESTController::get_item_schema.
	 */
	public function test_get_item_schema() {
		$schema = $this->controller->get_item_schema();

		$this->assertContains( 'theme_support', array_keys( $schema['properties'] ) );
	}
}
