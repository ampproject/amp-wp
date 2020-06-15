<?php
/**
 * Tests for AMP_User_Options class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_User_Options class.
 *
 * @group user-options
 *
 * @since 1.6.0
 *
 * @covers AMP_User_Options
 */
class Test_AMP_User_Options extends WP_UnitTestCase {

	/**
	 * Tests AMP_User_Options::init
	 *
	 * @covers AMP_User_Options::init
	 */
	public function test_init() {
		AMP_User_Options::init();

		$this->assertEquals( 10, has_filter( 'amp_setup_wizard_data', [ AMP_User_Options::class, 'inject_setup_wizard_data' ] ) );
		$this->assertEquals( 10, has_action( 'rest_api_init', [ AMP_User_Options::class, 'register_user_meta' ] ) );
		$this->assertEquals( 10, has_filter( 'get_user_metadata', [ AMP_User_Options::class, 'maybe_initialize_enable_developer_tools_setting' ] ) );
		$this->assertEquals( 10, has_filter( 'update_user_metadata', [ AMP_User_Options::class, 'update_enable_developer_tools_permission_check' ] ) );
	}

	/**
	 * Tests AMP_User_Options::register_user_meta
	 *
	 * @covers AMP_User_Options::register_user_meta
	 */
	public function test_register_user_meta() {
		global $wp_meta_keys;

		AMP_User_Options::register_user_meta();

		$this->assertArrayHasKey( 'amp_dev_tools_enabled', $wp_meta_keys['user'][''] );
	}

	/**
	 * Tests AMP_User_Options::inject_setup_wizard_data
	 *
	 * @covers AMP_User_Options::inject_setup_wizard_data
	 */
	public function test_inject_setup_wizard_data() {
		$data = AMP_User_Options::inject_setup_wizard_data( [ 'pre_filtered_data' => 1 ] );

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
	 * Tests AMP_User_Options::maybe_initialize_enable_developer_tools_setting
	 *
	 * @covers AMP_User_Options::maybe_initialize_enable_developer_tools_setting
	 */
	public function test_maybe_initialize_enable_developer_tools_setting() {
		// Administrator.
		$user = self::factory()->user->create(
			[
				'role' => 'administrator',
			]
		);

		$meta = get_user_meta( $user );
		$this->assertFalse( array_key_exists( 'amp_dev_tools_enabled', $meta ) );

		AMP_User_Options::maybe_initialize_enable_developer_tools_setting( null, $user, 'amp_dev_tools_enabled' );
	}
}
