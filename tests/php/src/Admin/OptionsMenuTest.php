<?php
/**
 * Tests for OptionsMenu.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\GoogleFonts;
use AmpProject\AmpWP\Admin\OptionsMenu;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use WP_UnitTestCase;

/**
 * Tests for OptionsMenu.
 *
 * @group options-menu
 */
class OptionsMenuTest extends WP_UnitTestCase {

	use AssertContainsCompatibility;

	/**
	 * Instance of OptionsMenu
	 *
	 * @var OptionsMenu
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = new OptionsMenu( new GoogleFonts(), new ReaderThemes() );
	}

	/**
	 * Test constants.
	 *
	 * @see OptionsMenu::ICON_BASE64_SVG
	 */
	public function test_constants() {
		$this->assertStringStartsWith( 'data:image/svg+xml;base64,', OptionsMenu::ICON_BASE64_SVG );
	}

	/**
	 * Test add_hooks.
	 *
	 * @see OptionsMenu::add_hooks()
	 */
	public function test_register() {
		$this->instance->register();
		$this->assertEquals( 9, has_action( 'admin_menu', [ $this->instance, 'add_menu_items' ] ) );
		$this->assertEquals( 10, has_action( 'admin_post_amp_analytics_options', 'AMP_Options_Manager::handle_analytics_submit' ) );

		$this->assertEquals( 10, has_filter( 'plugin_action_links_amp/amp.php', [ $this->instance, 'add_plugin_action_links' ] ) );

		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_assets' ] ) );
	}

	/**
	 * Test admin_menu.
	 *
	 * @covers OptionsMenu::add_menu_items()
	 */
	public function test_add_menu_items() {
		global $_parent_pages, $submenu, $wp_settings_fields;

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
		$this->assertCount( 3, $submenu['amp-options'] );
		$this->assertEquals( 'edit-tags.php?taxonomy=amp_validation_error&amp;post_type=amp_validated_url', $submenu['amp-options'][0][2] );
		$this->assertEquals( 'amp-options', $submenu['amp-options'][1][2] );

		// Test add_setting_field().
		$this->assertArrayHasKey( 'amp-options', $wp_settings_fields );
		$this->assertArrayHasKey( 'general', $wp_settings_fields['amp-options'] );
		$this->assertArrayNotHasKey( 'stories_settings', $wp_settings_fields['amp-options']['general'] );
	}

	/**
	 * Test render_screen for admin users.
	 *
	 * @covers OptionsMenu::render_screen()
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
		$this->assertStringContains( '<div class="wrap">', ob_get_clean() );
	}
}
