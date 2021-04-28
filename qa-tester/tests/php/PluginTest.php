<?php
/**
 * Class PluginTest.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester\Tests;

use AmpProject\AmpWP_QA_Tester\AdminBar;
use AmpProject\AmpWP_QA_Tester\Plugin;
use AmpProject\AmpWP_QA_Tester\RestRoute;

/**
 * Tests for Plugin.
 *
 * @package AmpProject\AmpWP_QA_Tester
 * @coversDefaultClass \AmpProject\AmpWP_QA_Tester\Plugin
 */
class PluginTest extends \WP_UnitTestCase {

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
	 * Test constructor method.
	 *
	 * @covers ::__construct()
	 */
	public function test___construct() {
		$this->assertInstanceOf( AdminBar::class, $this->plugin->admin_bar );
		$this->assertInstanceOf( RestRoute::class, $this->plugin->rest_route );
	}
}
