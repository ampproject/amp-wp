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
		$user = self::factory()->user->create();

		$this->assertNull( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user ] ) );

		update_user_meta( $user, 'amp_dev_tools_enabled', false );
		$this->assertFalse( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user ] ) );

		update_user_meta( $user, 'amp_dev_tools_enabled', true );
		$this->assertTrue( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user ] ) );
	}

	/**
	 * Tests DevToolsUserAccess::rest_update_dev_tools_enabled
	 *
	 * @covers DevToolsUserAccess::rest_update_dev_tools_enabled
	 */
	public function test_rest_update_dev_tools_enabled() {
		$author = self::factory()->user->create( [ 'role' => 'author' ] );

		$author_user = get_user_by( 'ID', $author );
		wp_set_current_user( $author );

		$update = $this->dev_tools_user_access->rest_update_dev_tools_enabled( true, $author_user );
		$this->assertInstanceOf( WP_Error::class, $update );

		$administrator = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $administrator );

		$this->assertInternalType( 'int', $this->dev_tools_user_access->rest_update_dev_tools_enabled( true, $author_user ) );
		$this->assertTrue( (bool) get_user_meta( $author, 'amp_dev_tools_enabled', true ) );
	}
}
