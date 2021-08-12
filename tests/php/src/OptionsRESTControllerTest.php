<?php
/**
 * Tests for OptionsRESTController.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests;

use AMP_Options_Manager;
use AmpProject\AmpWP\OptionsRESTController;
use AmpProject\AmpWP\PluginRegistry;
use AmpProject\AmpWP\Tests\Helpers\ThemesApiRequestMocking;
use WP_REST_Request;

/**
 * Tests for OptionsRESTController.
 *
 * @coversDefaultClass \AmpProject\AmpWP\OptionsRESTController
 */
class OptionsRESTControllerTest extends DependencyInjectedTestCase {

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

		$this->controller = $this->injector->make( OptionsRESTController::class );
	}

	/**
	 * Tests OptionsRESTController::get_items_permissions_check.
	 *
	 * @covers ::get_items_permissions_check
	 */
	public function test_get_items_permissions_check() {
		$this->assertWPError( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/options' ) ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->assertTrue( $this->controller->get_items_permissions_check( new WP_REST_Request( 'GET', '/amp/v1/options' ) ) );
	}

	/**
	 * Tests OptionsRESTController::get_items.
	 *
	 * @covers ::get_items
	 */
	public function test_get_items() {
		$data = $this->controller->get_items( new WP_REST_Request( 'GET', '/amp/v1/options' ) )->get_data();
		$this->assertEqualSets(
			[
				'theme_support',
				'reader_theme',
				'mobile_redirect',
				'plugin_configured',
				'all_templates_supported',
				'suppressed_plugins',
				'supported_templates',
				'supported_post_types',
				'analytics',
				'preview_permalink',
				'suppressible_plugins',
				'supportable_post_types',
				'supportable_templates',
				'onboarding_wizard_link',
				'customizer_link',
				'paired_url_structure',
				'paired_url_examples',
				'amp_slug',
				'custom_paired_endpoint_sources',
				'endpoint_path_slug_conflicts',
				'rewrite_using_permalinks',
			],
			array_keys( $data )
		);

		$plugin_registry = $this->injector->make( PluginRegistry::class );

		$this->assertEqualSets( array_keys( $plugin_registry->get_plugins( true ) ), array_keys( $data['suppressible_plugins'] ) );
		$this->assertEquals( null, $data['preview_permalink'] );
		$this->assertEquals( [], $data['suppressed_plugins'] );
		$this->assertArraySubset( [ 'post', 'page', 'attachment' ], wp_list_pluck( $data['supportable_post_types'], 'name' ) );
		$this->assertEquals( [ 'post', 'page' ], $data['supported_post_types'] );
		$this->assertStringContainsString( 'is_singular', wp_list_pluck( $data['supportable_templates'], 'id' ) );
		$this->assertEquals( [ 'is_singular' ], $data['supported_templates'] );
	}

	/**
	 * Tests OptionsRESTController::update_items.
	 *
	 * @covers ::update_items
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
	 * @covers ::get_item_schema
	 */
	public function test_get_item_schema() {
		$schema = $this->controller->get_item_schema();

		$this->assertStringContainsString( 'theme_support', array_keys( $schema['properties'] ) );
	}
}
