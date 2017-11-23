<?php
/**
 * Tests for AMP_Settings_Post_Types.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Settings_Post_Types.
 */
class Test_AMP_Settings_Post_Types extends WP_UnitTestCase {

	/**
	 * Instance of AMP_Settings_Post_Types
	 *
	 * @var AMP_Settings_Post_Types
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = AMP_Settings_Post_Types::get_instance();
	}

	/**
	 * Test init.
	 *
	 * @see AMP_Settings_Post_Types::init()
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 10, has_action( 'admin_init', array( $this->instance, 'register_settings' ) ) );
		$this->assertEquals( 10, has_action( 'update_option_' . AMP_Settings::SETTINGS_KEY, 'flush_rewrite_rules' ) );
	}

	/**
	 * Test register_settings.
	 *
	 * @see AMP_Settings_Post_Types::register_settings()
	 */
	public function test_register_settings() {
		global $wp_settings_sections, $wp_settings_fields;

		$menu_slug    = AMP_Settings::MENU_SLUG;
		$option_group = AMP_Settings::SETTINGS_KEY;
		$section_id   = 'post_types';
		$setting_id   = 'post_types_support';

		$this->instance->register_settings();
		$this->assertArrayHasKey( $menu_slug, $wp_settings_sections );

		if ( ! isset( $wp_settings_sections[ $menu_slug ] ) ) {
			$this->markTestIncomplete( 'Setting sections page could not be found.' );
		}

		$sections = $wp_settings_sections[ $menu_slug ];
		$this->assertArrayHasKey( $section_id, $sections );

		if ( ! isset( $sections[ $section_id ] ) ) {
			$this->markTestIncomplete( 'Settings section could not be found.' );
		}

		$this->assertEquals( $section_id, $sections[ $section_id ]['id'] );

		if ( ! isset( $wp_settings_fields[ $menu_slug ][ $section_id ][ $setting_id ] ) ) {
			$this->markTestIncomplete( 'Settings field could not be found.' );
		}

		$this->assertEquals( 'post_types_support', $wp_settings_fields[ $menu_slug ][ $section_id ][ $setting_id ]['id'] );
	}

	/**
	 * Test get_settings_value.
	 *
	 * @see AMP_Settings_Post_Types::get_settings_value()
	 */
	public function test_get_settings_value() {
		$this->assertFalse( $this->instance->get_settings_value( 'foo' ) );

		update_option( AMP_Settings::SETTINGS_KEY, array(
			'post_types_support' => array(
				'post' => true,
			),
		) );

		$this->assertTrue( $this->instance->get_settings_value( 'post' ) );

		// Cleanup.
		delete_option( AMP_Settings::SETTINGS_KEY );
	}

	/**
	 * Test get_supported_post_types.
	 *
	 * @see AMP_Settings_Post_Types::get_supported_post_types()
	 */
	public function test_get_supported_post_types() {
		// It would be redundant to add further test already covered in Core.
		$this->assertInternalType( 'array', $this->instance->get_supported_post_types() );
	}

	/**
	 * Test get_setting_name.
	 *
	 * @see AMP_Settings_Post_Types::get_setting_name()
	 */
	public function test_get_setting_name() {
		$this->assertEquals( AMP_Settings::SETTINGS_KEY . '[post_types_support][post]', $this->instance->get_setting_name( 'post' ) );
	}

	/**
	 * Test render_setting.
	 *
	 * @see AMP_Settings_Post_Types::render_setting()
	 */
	public function test_render_setting() {
		ob_start();
		$this->instance->render_setting();
		$this->assertContains( '<fieldset>', ob_get_clean() );
	}

	/**
	 * Test get_instance.
	 *
	 * @see AMP_Settings_Post_Types::get_instance()
	 */
	public function test_get_instance() {
		$this->assertInstanceOf( 'AMP_Settings_Post_Types', AMP_Settings_Post_Types::get_instance() );
	}

}
