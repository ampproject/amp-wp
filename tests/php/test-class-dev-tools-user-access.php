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
 * @group user-options
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

		$this->assertEquals( 10, has_action( 'rest_api_init', [ $this->dev_tools_user_access, 'register_user_meta' ] ) );
		$this->assertEquals( 10, has_filter( 'get_user_metadata', [ $this->dev_tools_user_access, 'get_default_enable_developer_tools_setting' ] ) );
		$this->assertEquals( 10, has_filter( 'update_user_metadata', [ $this->dev_tools_user_access, 'update_enable_developer_tools_permission_check' ] ) );
	}

	/**
	 * Tests DevToolsUserAccess::register_user_meta
	 *
	 * @covers DevToolsUserAccess::register_user_meta
	 */
	public function test_register_user_meta() {
		global $wp_meta_keys;

		$this->dev_tools_user_access->register_user_meta();

		$this->assertArrayHasKey( 'amp_dev_tools_enabled', $wp_meta_keys['user'][''] );
	}

	/**
	 * Provides test users to test initializing and updating the developer tools setting.
	 *
	 * @return array
	 */
	public function get_test_users() {
		return [
			'administrator'               => [
				true,
				'administrator',
				false,
			],
			'author_without_amp_validate' => [
				false,
				'author',
				false,
			],
			'author_with_amp_validate'    => [
				true,
				'author',
				true,
			],
		];
	}

	/**
	 * Tests DevToolsUserAccess::get_default_enable_developer_tools_setting
	 *
	 * @covers DevToolsUserAccess::get_default_enable_developer_tools_setting
	 *
	 * @dataProvider get_test_users
	 *
	 * @param string $user_can_have_dev_tools Whether the user is allowed to have dev tools enabled.
	 * @param string $role                    The user role to test.
	 * @param bool   $has_amp_validate_cap    Whether the user has the amp_validate cap added.
	 */
	public function test_get_default_enable_developer_tools_setting( $user_can_have_dev_tools, $role, $has_amp_validate_cap ) {
		$user = self::factory()->user->create( compact( 'role' ) );

		if ( $has_amp_validate_cap ) {
			$user_object = get_user_by( 'ID', $user );
			$user_object->add_cap( 'amp_validate' );
		}

		wp_set_current_user( $user );

		// Double check that the meta does not already exist.
		$meta = get_user_meta( $user );
		$this->assertFalse( array_key_exists( 'amp_dev_tools_enabled', $meta ) );

		$expected = $user_can_have_dev_tools ? '1' : '';
		$this->dev_tools_user_access->get_default_enable_developer_tools_setting( null, $user, 'amp_dev_tools_enabled' );
		$this->assertEquals( $expected, get_user_meta( $user, 'amp_dev_tools_enabled', true ) );
	}

	/**
	 * Tests DevToolsUserAccess::update_enable_developer_tools_permission_check
	 *
	 * @covers DevToolsUserAccess::update_enable_developer_tools_permission_check
	 *
	 * @dataProvider get_test_users
	 *
	 * @param string $user_can_have_dev_tools Whether the user is allowed to have dev tools enabled.
	 * @param string $role                    The user role to test.
	 * @param bool   $has_amp_validate_cap    Whether the user has the amp_validate cap added.
	 */
	public function test_update_enable_developer_tools_permission_check( $user_can_have_dev_tools, $role, $has_amp_validate_cap ) {
		$user = self::factory()->user->create( compact( 'role' ) );

		if ( $has_amp_validate_cap ) {
			$user_object = get_user_by( 'ID', $user );
			$user_object->add_cap( 'amp_validate' );
		}

		// Anyone can set it to false.
		$this->assertNull( $this->dev_tools_user_access->update_enable_developer_tools_permission_check( null, $user, 'amp_dev_tools_enabled', false ) );

		$expected = '1' === $user_can_have_dev_tools ? null : false;
		// Only users with permissions can set it to true.
		$this->assertEquals( $expected, $this->dev_tools_user_access->update_enable_developer_tools_permission_check( null, $user, 'amp_dev_tools_enabled', true ) );
	}
}
