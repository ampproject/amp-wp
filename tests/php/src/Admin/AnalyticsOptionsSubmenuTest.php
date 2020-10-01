<?php
/**
 * Tests for AnalyticsOptionsSubmenu.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\AnalyticsOptionsSubmenu;
use AmpProject\AmpWP\Admin\GoogleFonts;
use AmpProject\AmpWP\Admin\OptionsMenu;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Admin\RESTPreloader;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use WP_UnitTestCase;

/**
 * Tests for AnalyticsOptionsSubmenu.
 *
 * @group options-menu
 * @coversDefaultClass \AmpProject\AmpWP\Admin\AnalyticsOptionsSubmenu
 */
class AnalyticsOptionsSubmenuTest extends WP_UnitTestCase {

	use AssertContainsCompatibility;

	/**
	 * Instance of OptionsMenu class.
	 *
	 * @var OptionsMenu.
	 */
	public $options_menu_instance;

	/**
	 * Instance of AnalyticsOptionsSubmenu
	 *
	 * @var AnalyticsOptionsSubmenu
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->options_menu_instance = new OptionsMenu(
			new GoogleFonts(),
			new ReaderThemes(),
			new RESTPreloader()
		);
		$this->instance              = new AnalyticsOptionsSubmenu( $this->options_menu_instance );
	}

	/**
	 * Test register.
	 *
	 * @covers ::register()
	 */
	public function test_register() {
		$this->instance->register();
		$this->assertEquals( 99, has_action( 'admin_menu', [ $this->instance, 'add_submenu_link' ] ) );
	}

	/**
	 * Test add_submenu_link.
	 *
	 * @covers ::add_submenu_link()
	 */
	public function test_link_is_added() {
		global $submenu;

		$original_submenu = $submenu;

		$test_user = self::factory()->user->create(
			[
				'role' => 'administrator',
			]
		);
		wp_set_current_user( $test_user );

		$this->options_menu_instance->add_menu_items();
		$this->instance->add_submenu_link();

		$this->assertContains( 'Analytics', wp_list_pluck( $submenu[ $this->options_menu_instance->get_menu_slug() ], 0 ) );

		$submenu = $original_submenu;
	}
}
