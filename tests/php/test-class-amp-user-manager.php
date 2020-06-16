<?php
/**
 * Tests for UserManager class.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\UserManager;

/**
 * Tests for UserManager class.
 *
 * @group user-options
 *
 * @since 1.6.0
 *
 * @covers UserManager
 */
class Test_UserManager extends WP_UnitTestCase {

	/**
	 * Tests UserManager::register
	 *
	 * @covers UserManager::register
	 */
	public function test_register() {
		( new UserManager() )->register();

		$this->assertEquals( 10, has_filter( 'amp_setup_wizard_data', [ UserManager::class, 'inject_setup_wizard_data' ] ) );
		$this->assertEquals( 10, has_action( 'rest_api_init', [ UserManager::class, 'register_user_meta' ] ) );
		$this->assertEquals( 10, has_filter( 'get_user_metadata', [ UserManager::class, 'get_default_enable_developer_tools_setting' ] ) );
		$this->assertEquals( 10, has_filter( 'update_user_metadata', [ UserManager::class, 'update_enable_developer_tools_permission_check' ] ) );
	}

	/**
	 * Tests UserManager::register_user_meta
	 *
	 * @covers UserManager::register_user_meta
	 */
	public function test_register_user_meta() {
		global $wp_meta_keys;

		UserManager::register_user_meta();

		$this->assertArrayHasKey( 'amp_dev_tools_enabled', $wp_meta_keys['user'][''] );
	}

	/**
	 * Tests UserManager::inject_setup_wizard_data
	 *
	 * @covers UserManager::inject_setup_wizard_data
	 */
	public function test_inject_setup_wizard_data() {
		$data = UserManager::inject_setup_wizard_data( [ 'pre_filtered_data' => 1 ] );

		$this->assertEquals(
			[
				'pre_filtered_data'           => 1,
				'USER_OPTION_DEVELOPER_TOOLS' => 'amp_dev_tools_enabled',
				'USER_REST_ENDPOINT'          => rest_url( 'wp/v2/users/me' ),
			],
			$data
		);
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
	 * Tests UserManager::get_default_enable_developer_tools_setting
	 *
	 * @covers UserManager::get_default_enable_developer_tools_setting
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
		UserManager::get_default_enable_developer_tools_setting( null, $user, 'amp_dev_tools_enabled' );
		$this->assertEquals( $expected, get_user_meta( $user, 'amp_dev_tools_enabled', true ) );
	}

	/**
	 * Tests UserManager::update_enable_developer_tools_permission_check
	 *
	 * @covers UserManager::update_enable_developer_tools_permission_check
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
		$this->assertNull( UserManager::update_enable_developer_tools_permission_check( null, $user, 'amp_dev_tools_enabled', false ) );

		$expected = '1' === $user_can_have_dev_tools ? null : false;
		// Only users with permissions can set it to true.
		$this->assertEquals( $expected, UserManager::update_enable_developer_tools_permission_check( null, $user, 'amp_dev_tools_enabled', true ) );
	}
}
