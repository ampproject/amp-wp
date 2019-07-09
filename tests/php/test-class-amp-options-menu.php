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
		$this->assertEquals( 9, has_action( 'admin_menu', [ $this->instance, 'add_menu_items' ] ) );
		$this->assertEquals( 10, has_action( 'admin_post_amp_analytics_options', 'AMP_Options_Manager::handle_analytics_submit' ) );
	}

	/**
	 * Test admin_menu.
	 *
	 * @covers AMP_Options_Menu::add_menu_items()
	 */
	public function test_add_menu_items() {
		global $_parent_pages, $submenu, $wp_settings_sections, $wp_settings_fields;

		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);

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
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);

		ob_start();
		$this->instance->render_screen();
		$this->assertContains( '<div class="wrap">', ob_get_clean() );
	}
}
