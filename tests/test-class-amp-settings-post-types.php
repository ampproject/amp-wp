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
	 * Test get_value.
	 *
	 * @see AMP_Settings_Post_Types::get_settings()
	 */
	public function test_get_settings() {
		$this->assertEmpty( $this->instance->get_settings() );
		$this->assertInternalType( 'array', $this->instance->get_settings() );
		$this->assertFalse( $this->instance->get_settings( 'foo' ) );

		update_option( AMP_Settings::SETTINGS_KEY, array(
			'post_types_support' => array(
				'post' => true,
			),
		) );

		$this->assertContains( 'post', $this->instance->get_settings() );
		$this->assertInternalType( 'array', $this->instance->get_settings() );
		$this->assertTrue( $this->instance->get_settings( 'post' ) );

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
	 * Test get_name_attribute.
	 *
	 * @see AMP_Settings_Post_Types::get_name_attribute()
	 */
	public function test_get_name_attribute() {
		$this->assertEquals( AMP_Settings::SETTINGS_KEY . '[post_types_support][post]', $this->instance->get_name_attribute( 'post' ) );
	}

	/**
	 * Test disabled.
	 *
	 * @see AMP_Settings_Post_Types::disabled()
	 */
	public function test_disabled() {
		$this->assertFalse( $this->instance->disabled( 'foo' ) );
		add_post_type_support( 'foo', AMP_QUERY_VAR );
		$this->assertTrue( $this->instance->disabled( 'foo' ) );
	}

	/**
	 * Test errors.
	 *
	 * @see AMP_Settings_Post_Types::errors()
	 */
	public function test_errors() {
		update_option( AMP_Settings::SETTINGS_KEY, array(
			'post_types_support' => array(
				'foo' => true,
			),
		) );
		remove_post_type_support( 'foo', AMP_QUERY_VAR );
		$this->instance->errors();
		$this->assertNotEmpty( get_settings_errors() );
		delete_option( AMP_Settings::SETTINGS_KEY );
	}

	/**
	 * Test validate.
	 *
	 * @see AMP_Settings_Post_Types::validate()
	 */
	public function test_validate() {
		$this->assertInternalType( 'array', $this->instance->validate( array() ) );
		update_option( AMP_Settings::SETTINGS_KEY, array(
			'post_types_support' => array(
				'foo' => true,
			),
		) );
		$settings = $this->instance->validate( get_option( AMP_Settings::SETTINGS_KEY ) );
		$this->assertInternalType( 'bool', $settings['post_types_support']['foo'] );
		delete_option( AMP_Settings::SETTINGS_KEY );
	}

	/**
	 * Test render.
	 *
	 * @see AMP_Settings_Post_Types::render()
	 */
	public function test_render() {
		ob_start();
		$this->instance->render();
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
