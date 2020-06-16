<?php
/**
 * Tests for AMP_User_Manager class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_User_Manager class.
 *
 * @group user-options
 *
 * @since 1.6.0
 *
 * @covers AMP_User_Manager
 */
class Test_AMP_User_Manager extends WP_UnitTestCase {

	/**
	 * Tests AMP_User_Manager::init
	 *
	 * @covers AMP_User_Manager::init
	 */
	public function test_init() {
		AMP_User_Manager::init();

		$this->assertEquals( 10, has_filter( 'amp_setup_wizard_data', [ AMP_User_Manager::class, 'inject_setup_wizard_data' ] ) );
		$this->assertEquals( 10, has_action( 'rest_api_init', [ AMP_User_Manager::class, 'register_user_meta' ] ) );
		$this->assertEquals( 10, has_filter( 'get_user_metadata', [ AMP_User_Manager::class, 'get_default_enable_developer_tools_setting' ] ) );
		$this->assertEquals( 10, has_filter( 'update_user_metadata', [ AMP_User_Manager::class, 'update_enable_developer_tools_permission_check' ] ) );
	}

	/**
	 * Tests AMP_User_Manager::register_user_meta
	 *
	 * @covers AMP_User_Manager::register_user_meta
	 */
	public function test_register_user_meta() {
		global $wp_meta_keys;

		AMP_User_Manager::register_user_meta();

		$this->assertArrayHasKey( 'amp_dev_tools_enabled', $wp_meta_keys['user'][''] );
	}

	/**
	 * Tests AMP_User_Manager::inject_setup_wizard_data
	 *
	 * @covers AMP_User_Manager::inject_setup_wizard_data
	 */
	public function test_inject_setup_wizard_data() {
		$data = AMP_User_Manager::inject_setup_wizard_data( [ 'pre_filtered_data' => 1 ] );

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
	 * Provides test users to test initializing the developer tools setting.
	 *
	 * @return array
	 */
	public function get_test_users() {
		return [
			'administrator'               => [
				'1',
				'administrator',
				false,
			],
			'author_without_amp_validate' => [
				'',
				'author',
				false,
			],
			'author_with_amp_validate'    => [
				'1',
				'author',
				true,
			],
		];
	}

	/**
	 * Tests AMP_User_Manager::get_default_enable_developer_tools_setting
	 *
	 * @covers AMP_User_Manager::get_default_enable_developer_tools_setting
	 *
	 * @dataProvider get_test_users
	 *
	 * @param string $expected             The expected meta valud after the test method runs.
	 * @param string $role                 The user role to test.
	 * @param bool   $has_amp_validate_cap Whether the user has the amp_validate cap added.
	 */
	public function test_get_default_enable_developer_tools_setting( $expected, $role, $has_amp_validate_cap ) {
		$user = self::factory()->user->create( compact( 'role' ) );

		if ( $has_amp_validate_cap ) {
			$user_object = get_user_by( 'ID', $user );
			$user_object->add_cap( 'amp_validate' );
		}

		wp_set_current_user( $user );

		// Double check that the meta does not already exist.
		$meta = get_user_meta( $user );
		$this->assertFalse( array_key_exists( 'amp_dev_tools_enabled', $meta ) );

		AMP_User_Manager::get_default_enable_developer_tools_setting( null, $user, 'amp_dev_tools_enabled' );
		$this->assertEquals( $expected, get_user_meta( $user, 'amp_dev_tools_enabled', true ) );
	}

	/**
	 * Tests AMP_User_Manager::update_enable_developer_tools_permission_check
	 *
	 * @covers AMP_User_Manager::update_enable_developer_tools_permission_check
	 *
	 * @dataProvider get_test_users
	 *
	 * @param string $expected             The expected meta valud after the test method runs.
	 * @param string $role                 The user role to test.
	 * @param bool   $has_amp_validate_cap Whether the user has the amp_validate cap added.
	 */
	public function test_update_enable_developer_tools_permission_check( $expected, $role, $has_amp_validate_cap ) {
		$user = self::factory()->user->create( compact( 'role' ) );

		if ( $has_amp_validate_cap ) {
			$user_object = get_user_by( 'ID', $user );
			$user_object->add_cap( 'amp_validate' );
		}

		// Anyone can set it to false.
		$this->assertNull( AMP_User_Manager::update_enable_developer_tools_permission_check( null, $user, 'amp_dev_tools_enabled', false ) );

		// Only users with permissions can set it to true.
		$this->assertEquals( '1' === $expected ? null : false, AMP_User_Manager::update_enable_developer_tools_permission_check( null, $user, 'amp_dev_tools_enabled', true ) );
	}
}
