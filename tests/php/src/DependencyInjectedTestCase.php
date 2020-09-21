<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\AmpWpPlugin;
use AmpProject\AmpWP\Infrastructure\Injector;
use AmpProject\AmpWP\Infrastructure\ServiceContainer;
use WP_UnitTestCase;

class DependencyInjectedTestCase extends WP_UnitTestCase {

	/**
	 * Plugin instance to test with.
	 *
	 * @var AmpWpPlugin
	 */
	protected $plugin;

	/**
	 * Service container instance to test with.
	 *
	 * @var ServiceContainer
	 */
	protected $container;

	/**
	 * Injector instance to test with.
	 *
	 * @var Injector
	 */
	protected $injector;

	/**
	 * Runs the routine before each test is executed.
	 */
	public function setUp() {
		parent::setUp();

		$this->plugin = new AmpWpPlugin();
		$this->plugin->register();

		$this->container = $this->plugin->get_container();
		$this->injector  = $this->container->get( 'injector' );
	}
}
