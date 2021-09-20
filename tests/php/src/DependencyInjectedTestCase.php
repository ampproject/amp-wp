<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\AmpWpPlugin;
use AmpProject\AmpWP\Infrastructure\Injector;
use AmpProject\AmpWP\Infrastructure\ServiceContainer;
use AmpProject\AmpWP\Services;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

abstract class DependencyInjectedTestCase extends TestCase {

	use PrivateAccess;

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
	 * Set up the service architecture before each test run.
	 */
	public function set_up() {
		parent::set_up();

		// We're intentionally avoiding the AmpWpPluginFactory here as it uses a
		// static instance, because its whole point is to allow reuse across consumers.
		$this->plugin = new AmpWpPlugin();
		$this->plugin->register();

		$this->container = $this->plugin->get_container();
		$this->injector  = $this->container->get( 'injector' );

		// The static Services helper has to be modified to use the same objects
		// as the ones that are injected into the tests.
		$this->set_private_property( Services::class, 'plugin', $this->plugin );
		$this->set_private_property( Services::class, 'container', $this->container );
		$this->set_private_property( Services::class, 'injector', $this->injector );
	}

	/**
	 * Clean up again after each test run.
	 */
	public function tearDown() {
		parent::tearDown();

		$this->set_private_property( Services::class, 'plugin', null );
		$this->set_private_property( Services::class, 'container', null );
		$this->set_private_property( Services::class, 'injector', null );

		// WordPress core fails to do this.
		$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
		unset( $GLOBALS['current_screen'] );
	}
}
