<?php
/**
 * Class AdminBarTest.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester\Tests;

use AmpProject\AmpWP_QA_Tester\AdminBar;

/**
 * Tests for AdminBar class.
 *
 * @package AmpProject\AmpWP_QA_Tester
 * @coversDefaultClass \AmpProject\AmpWP_QA_Tester\AdminBar
 */
class AdminBarTest extends \WP_UnitTestCase {

	/**
	 * Instance of AdminBar class.
	 *
	 * @var AdminBar
	 */
	private $admin_bar;

	/**
	 * Set up.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		$this->admin_bar = new AdminBar();
	}

	/**
	 * Test register.
	 *
	 * @covers ::register()
	 */
	public function test_register() {
		$this->admin_bar->register();

		$this->assertEquals( false, has_action( 'admin_bar_menu', [ $this->admin_bar, 'add_menu_button' ] ) );
		$this->assertEquals( false, has_action( 'wp_enqueue_scripts', [ $this->admin_bar, 'enqueue_plugin_assets' ] ) );
		$this->assertEquals( false, has_action( 'admin_enqueue_scripts', [ $this->admin_bar, 'enqueue_plugin_assets' ] ) );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->admin_bar->register();
		$this->assertEquals( 99, has_action( 'admin_bar_menu', [ $this->admin_bar, 'add_menu_button' ] ) );
		$this->assertEquals( 10, has_action( 'wp_enqueue_scripts', [ $this->admin_bar, 'enqueue_plugin_assets' ] ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->admin_bar, 'enqueue_plugin_assets' ] ) );
	}
}
