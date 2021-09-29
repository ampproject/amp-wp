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
		$admin_user  = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		$editor_user = self::factory()->user->create_and_get( [ 'role' => 'editor' ] );

		wp_set_current_user( $editor_user->ID );
		$this->assertTrue( $this->user_rest_endpoint_extension->update_review_panel_dismissed_for_template_mode( AMP_Theme_Support::STANDARD_MODE_SLUG, $editor_user ) );
		$this->assertWPError( $this->user_rest_endpoint_extension->update_review_panel_dismissed_for_template_mode( AMP_Theme_Support::STANDARD_MODE_SLUG, $admin_user ) );

		wp_set_current_user( $admin_user->ID );
		$this->assertTrue( $this->user_rest_endpoint_extension->update_review_panel_dismissed_for_template_mode( AMP_Theme_Support::TRANSITIONAL_MODE_SLUG, $admin_user ) );
		$this->assertWPError( $this->user_rest_endpoint_extension->update_review_panel_dismissed_for_template_mode( AMP_Theme_Support::TRANSITIONAL_MODE_SLUG, $editor_user ) );

		$this->assertEquals( AMP_Theme_Support::TRANSITIONAL_MODE_SLUG, get_user_meta( $admin_user->ID, UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE, true ) );
		$this->assertTrue( $this->user_rest_endpoint_extension->update_review_panel_dismissed_for_template_mode( '', $admin_user ) );
		$this->assertEmpty( get_user_meta( $admin_user->ID, UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE, true ) );

		$this->assertWPError( $this->user_rest_endpoint_extension->update_review_panel_dismissed_for_template_mode( 'foobar', $editor_user ) );
	}

	/**
	 * Tests UserRESTEndpointExtension::get_review_panel_dismissed_for_template_mode
	 *
	 * @covers ::get_review_panel_dismissed_for_template_mode
	 */
	public function test_get_review_panel_dismissed_for_template_mode() {
		$admin_user  = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		$editor_user = self::factory()->user->create_and_get( [ 'role' => 'editor' ] );

		wp_set_current_user( $editor_user->ID );
		$this->assertEmpty( $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $editor_user->ID ] ) );
		$this->user_rest_endpoint_extension->update_review_panel_dismissed_for_template_mode( AMP_Theme_Support::STANDARD_MODE_SLUG, $editor_user );
		$this->assertEquals( AMP_Theme_Support::STANDARD_MODE_SLUG, $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $editor_user->ID ] ) );
		$this->assertWPError( $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $admin_user->ID ] ) );

		wp_set_current_user( $admin_user->ID );
		$this->assertEmpty( $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $admin_user->ID ] ) );
		$this->user_rest_endpoint_extension->update_review_panel_dismissed_for_template_mode( AMP_Theme_Support::TRANSITIONAL_MODE_SLUG, $admin_user );
		$this->assertEquals( AMP_Theme_Support::TRANSITIONAL_MODE_SLUG, $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $admin_user->ID ] ) );
		$this->assertWPError( $this->user_rest_endpoint_extension->get_review_panel_dismissed_for_template_mode( [ 'id' => $editor_user->ID ] ) );
	}
}
