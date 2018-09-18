<?php
/**
 * Tests for AMP_Options_Menu.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Options_Menu.
 */
class Test_AMP_Options_Menu extends WP_UnitTestCase {

	/**
	 * Instance of AMP_Options_Menu
	 *
	 * @var AMP_Options_Menu
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = new AMP_Options_Menu();
	}

	/**
	 * Test constants.
	 *
	 * @see AMP_Options_Menu::ICON_BASE64_SVG
	 */
	public function test_constants() {
		$this->assertStringStartsWith( 'data:image/svg+xml;base64,', AMP_Options_Menu::ICON_BASE64_SVG );
	}

	/**
	 * Test init.
	 *
	 * @see AMP_Options_Menu::init()
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 9, has_action( 'admin_menu', array( $this->instance, 'add_menu_items' ) ) );
		$this->assertEquals( 10, has_action( 'admin_post_amp_analytics_options', 'AMP_Options_Manager::handle_analytics_submit' ) );
	}

	/**
	 * Test admin_menu.
	 *
	 * @covers AMP_Options_Menu::add_menu_items()
	 */
	public function test_add_menu_items() {
		global $_parent_pages, $submenu, $wp_settings_sections, $wp_settings_fields;

		wp_set_current_user( $this->factory->user->create( array(
			'role' => 'administrator',
		) ) );

		$this->instance->add_menu_items();
		$this->assertArrayHasKey( 'amp-options', $_parent_pages );
		$this->assertEquals( 'amp-options', $_parent_pages['amp-options'] );
		$this->assertArrayHasKey( 'amp-analytics-options', $_parent_pages );
		$this->assertEquals( 'amp-options', $_parent_pages['amp-analytics-options'] );

		$this->assertArrayHasKey( 'amp-options', $submenu );
		$this->assertCount( 2, $submenu['amp-options'] );
		$this->assertEquals( 'amp-options', $submenu['amp-options'][0][2] );
		$this->assertEquals( 'amp-analytics-options', $submenu['amp-options'][1][2] );

		// Test add_setting_section().
		$this->assertArrayHasKey( 'amp-options', $wp_settings_sections );
		$this->assertArrayHasKey( 'general', $wp_settings_sections['amp-options'] );

		// Test add_setting_field().
		$this->assertArrayHasKey( 'amp-options', $wp_settings_fields );
		$this->assertArrayHasKey( 'general', $wp_settings_fields['amp-options'] );
		$this->assertArrayHasKey( 'supported_templates', $wp_settings_fields['amp-options']['general'] );
	}

	/**
	 * Test render_screen for admin users.
	 *
	 * @covers AMP_Options_Menu::render_screen()
	 */
	public function test_render_screen_for_admin_user() {
		wp_set_current_user( $this->factory->user->create( array(
			'role' => 'administrator',
		) ) );

		ob_start();
		$this->instance->render_screen();
		$this->assertContains( '<div class="wrap">', ob_get_clean() );
	}

	/**
	 * Test possibly_replace_settings_saved_notice.
	 *
	 * @covers AMP_Options_Menu::possibly_replace_settings_saved_notice()
	 */
	public function test_possibly_replace_settings_saved_notice() {
		$GLOBALS['wp_settings_errors'] = array(); // WPCS: Global override OK.
		$meta_key                      = 'amp_view_your_site_notice';
		$user_id                       = $this->factory()->user->create();
		wp_set_current_user( $user_id );

		/**
		 * There should now only be one error in $wp_settings_errors, which is taken from privacy.php.
		 * This isn't the 'Settings saved' error, so it should not be overwritten.
		 */
		add_settings_error(
			'page_for_privacy_policy',
			'page_for_privacy_policy',
			'Example message',
			'error'
		);

		$inital_wp_settings_errors = get_settings_errors();
		$this->instance->possibly_replace_settings_saved_notice();
		$this->assertEquals( $inital_wp_settings_errors, get_settings_errors() );

		/*
		 * The 'Settings saved' error is now present, but 'View your site as AMP...' has already shown
		 * as the meta value is true.
		 * So this should not change the message.
		 */
		add_settings_error( 'general', 'settings_updated', 'Settings saved.', 'updated' );
		update_user_meta( $user_id, $meta_key, true );
		$inital_wp_settings_errors = get_settings_errors();
		$this->instance->possibly_replace_settings_saved_notice();
		$this->assertEquals( $inital_wp_settings_errors, get_settings_errors() );

		// Now that the the meta value indicates that this notice hasn't shown yet, this should change the message.
		update_user_meta( $user_id, $meta_key, false );
		$template_mode_messages = array(
			'native'  => 'Native Mode activated! View your site as AMP now or Review Errors',
			'paired'  => 'Paired Mode activated! View your site as AMP now or Review Errors',
			'classic' => 'Classic Mode activated! View your site as AMP now. We recommend upgrading to Native or Paired mode.',
		);
		foreach ( $template_mode_messages as $mode => $message ) {
			$this->assert_template_mode_message( $mode, $message );
			update_user_meta( $user_id, $meta_key, false );
		}
	}

	/**
	 * Assert that the error for a given template mode has the right message.
	 *
	 * @param string $template_mode The template mode.
	 * @param string $message The message that should display for this mode.
	 */
	public function assert_template_mode_message( $template_mode, $message ) {
		AMP_Options_Manager::update_option( 'theme_support', $template_mode );
		$this->instance->possibly_replace_settings_saved_notice();
		$general_errors = get_settings_errors( 'general' );
		$new_error      = reset( $general_errors );
		$this->assertEquals( $message, $new_error['message'] );
	}
}
