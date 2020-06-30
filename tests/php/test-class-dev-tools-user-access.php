<?php
/**
 * Tests for DevToolsUserAccess class.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\DevToolsUserAccess;

/**
 * Tests for DevToolsUserAccess class.
 *
 * @group dev-tools-user-access
 *
 * @since 1.6.0
 *
 * @covers DevToolsUserAccess
 */
class Test_DevToolsUserAccess extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var DevToolsUserAccess
	 */
	private $dev_tools_user_access;

	public function setUp() {
		parent::setUp();

		$this->dev_tools_user_access = new DevToolsUserAccess();
	}

	/**
	 * Tests DevToolsUserAccess::register
	 *
	 * @covers DevToolsUserAccess::register
	 */
	public function test_register() {
		$this->dev_tools_user_access->register();
		$this->assertEquals( 10, has_action( 'rest_api_init', [ $this->dev_tools_user_access, 'register_rest_field' ] ) );
	}

	/**
	 * Tests DevToolsUserAccess::register_rest_field
	 *
	 * @covers DevToolsUserAccess::register_rest_field
	 */
	public function test_register_rest_field() {
		global $wp_rest_additional_fields;

		$this->dev_tools_user_access->register_rest_field();

		$this->assertArrayHasKey( 'amp_dev_tools_enabled', $wp_rest_additional_fields['user'] );
	}

	/**
	 * Tests DevToolsUserAccess::rest_get_dev_tools_enabled
	 *
	 * @covers DevToolsUserAccess::rest_get_dev_tools_enabled
	 */
	public function test_rest_get_dev_tools_enabled() {
		/** @var WP_User $user */
		$user = self::factory()->user->create_and_get( [ 'role' => 'author' ] );

		$this->assertFalse( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user->ID ] ) );

		$user->set_role( 'administrator' );
		$this->assertTrue( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user->ID ] ) );

		update_user_meta( $user->ID, 'amp_dev_tools_enabled', 'false' );
		$this->assertFalse( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user->ID ] ) );

		update_user_meta( $user->ID, 'amp_dev_tools_enabled', 'true' );
		$this->assertTrue( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user->ID ] ) );
	}

	/**
	 * Tests DevToolsUserAccess::rest_update_dev_tools_enabled
	 *
	 * @covers DevToolsUserAccess::rest_update_dev_tools_enabled
	 */
	public function test_rest_update_dev_tools_enabled() {
		$author_user = self::factory()->user->create_and_get( [ 'role' => 'author' ] );

		wp_set_current_user( $author_user->ID );

		$update = $this->dev_tools_user_access->rest_update_dev_tools_enabled( true, $author_user );
		$this->assertInstanceOf( WP_Error::class, $update );

		$administrator_user = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		wp_set_current_user( $administrator_user->ID );
		$this->assertInstanceOf( WP_Error::class, $this->dev_tools_user_access->rest_update_dev_tools_enabled( true, $author_user ) );

		$this->assertTrue( $this->dev_tools_user_access->rest_update_dev_tools_enabled( true, $administrator_user ) );
		$this->assertEquals( 'true', get_user_meta( $administrator_user->ID, 'amp_dev_tools_enabled', true ) );

		$this->assertTrue( $this->dev_tools_user_access->rest_update_dev_tools_enabled( false, $administrator_user ) );
		$this->assertEquals( 'false', get_user_meta( $administrator_user->ID, 'amp_dev_tools_enabled', true ) );
	}
}
