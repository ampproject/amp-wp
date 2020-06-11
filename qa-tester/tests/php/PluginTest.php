<?php
/**
 * Class PluginTest.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester\Tests;

use AmpProject\AmpWP_QA_Tester\Plugin;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Plugin.
 *
 * @package AmpProject\AmpWP_QA_Tester
 * @covers Plugin
 */
class PluginTest extends TestCase {

	/**
	 * Instance of Plugin class.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Set up.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		$this->plugin = new Plugin( __DIR__ . '../../amp-qa-tester.php' );
	}

	/**
	 * Test register.
	 *
	 * @covers ::register()
	 */
	public function test_register() {
		$this->plugin->register();

		// Admin bar hooks.
		$this->assertEquals( 99, has_action( 'admin_bar_menu', [ $this->plugin->admin_bar, 'add_menu_button' ] ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->plugin->admin_bar, 'enqueue_plugin_assets' ] ) );

		// REST route hooks.
		$this->assertEquals( 10, has_action( 'rest_api_init', [ $this->plugin->rest_route, 'register_route' ] ) );
	}
}
