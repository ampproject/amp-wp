<?php
/**
 * Tests for UserRESTEndpointExtension class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AMP_Theme_Support;
use AmpProject\AmpWP\Admin\UserRESTEndpointExtension;
use AmpProject\AmpWP\Tests\TestCase;
use WP_REST_Request;

/**
 * Tests for UserRESTEndpointExtension class.
 *
 * @group options-menu
 *
 * @since 2.2
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\UserRESTEndpointExtension
 */
class UserRESTEndpointExtensionTest extends TestCase {

	/**
	 * Test instance.
	 *
	 * @var UserRESTEndpointExtension
	 */
	private $user_rest_endpoint_extension;

	public function setUp() {
		parent::setUp();

		$this->user_rest_endpoint_extension = new UserRESTEndpointExtension();
	}

	/**
	 * Tests UserRESTEndpointExtension::register
	 *
	 * @covers ::get_registration_action
	 */
	public function test_get_registration_action() {
		$this->assertEquals( 'rest_api_init', UserRESTEndpointExtension::get_registration_action() );
	}

	/**
	 * Tests UserRESTEndpointExtension::register_rest_field
	 *
	 * @covers ::register
	 * @covers ::register_rest_field
	 */
	public function test_register_rest_field() {
		global $wp_rest_additional_fields;

		$this->user_rest_endpoint_extension->register();

		$this->assertArrayHasKey( 'amp_review_panel_dismissed_for_template_mode', $wp_rest_additional_fields['user'] );
	}

	/**
	 * Tests UserRESTEndpointExtension::update_review_panel_dismissed_for_template_mode
	 *
	 * @covers ::update_review_panel_dismissed_for_template_mode
	 */
	public function test_update_review_panel_dismissed_for_template_mode() {
		$server = rest_get_server();
		$this->user_rest_endpoint_extension->register_rest_field();

		$admin_user  = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		$editor_user = self::factory()->user->create_and_get( [ 'role' => 'editor' ] );

		$this->assertSame( '', $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $admin_user->ID ] ) );
		$this->assertSame( '', $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $editor_user->ID ] ) );

		// Test that an editor can edit their own field.
		wp_set_current_user( $editor_user->ID );
		$request = new WP_REST_Request( 'PUT', "/wp/v2/users/{$editor_user->ID}" );
		$request->set_body_params( [ UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE => AMP_Theme_Support::STANDARD_MODE_SLUG ] );
		$response = $server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( AMP_Theme_Support::STANDARD_MODE_SLUG, $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $editor_user->ID ] ) );

		// Test that an editor cannot edit another user's field.
		wp_set_current_user( $editor_user->ID );
		$request = new WP_REST_Request( 'PUT', "/wp/v2/users/{$admin_user->ID}" );
		$request->set_body_params( [ UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE => AMP_Theme_Support::STANDARD_MODE_SLUG ] );
		$response = $server->dispatch( $request );
		$this->assertEquals( 403, $response->get_status() );
		$this->assertSame( '', $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $admin_user->ID ] ) );

		// Test that admin user can edit another user's field.
		wp_set_current_user( $admin_user->ID );
		$request = new WP_REST_Request( 'PUT', "/wp/v2/users/{$editor_user->ID}" );
		$request->set_body_params( [ UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE => AMP_Theme_Support::TRANSITIONAL_MODE_SLUG ] );
		$response = $server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( AMP_Theme_Support::TRANSITIONAL_MODE_SLUG, $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $editor_user->ID ] ) );

		// Test that admin user can edit their own field (to clear it out).
		wp_set_current_user( $admin_user->ID );
		$request = new WP_REST_Request( 'PUT', "/wp/v2/users/{$admin_user->ID}" );
		$request->set_body_params( [ UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE => '' ] );
		$response = $server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertSame( '', $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $admin_user->ID ] ) );

		// Test that a user user cannot update the value to be bogus.
		wp_set_current_user( $admin_user->ID );
		$request = new WP_REST_Request( 'PUT', "/wp/v2/users/{$admin_user->ID}" );
		$request->set_body_params( [ UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE => 'BOGUS' ] );
		$response = $server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
		$this->assertSame( '', $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $admin_user->ID ] ) );
	}

	/**
	 * Tests UserRESTEndpointExtension::get_review_panel_dismissed_for_template_mode
	 *
	 * @covers ::get_review_panel_dismissed_for_template_mode
	 */
	public function test_get_review_panel_dismissed_for_template_mode() {
		$server = rest_get_server();
		$this->user_rest_endpoint_extension->register_rest_field();

		$admin_user = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_user->ID );

		// Check initial value.
		$request  = new WP_REST_Request( 'GET', "/wp/v2/users/{$admin_user->ID}" );
		$response = $server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE, $data );
		$this->assertSame( '', $data[ UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE ] );

		// Check updated value.
		$this->user_rest_endpoint_extension->update_review_panel_dismissed_for_template_mode( AMP_Theme_Support::TRANSITIONAL_MODE_SLUG, $admin_user );
		$request  = new WP_REST_Request( 'GET', "/wp/v2/users/{$admin_user->ID}" );
		$response = $server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE, $data );
		$this->assertSame( AMP_Theme_Support::TRANSITIONAL_MODE_SLUG, $data[ UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE ] );
	}
}
