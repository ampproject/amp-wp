<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\AmpWpPlugin;
use AmpProject\AmpWP\Infrastructure\Injector;
use AmpProject\AmpWP\PluginRegistry;
use AmpProject\AmpWP\Tests\Fixture\DummyService;
use WP_UnitTestCase;

final class AmpWpPluginTest extends WP_UnitTestCase {

	public function test_it_has_filtering_disabled_by_default() {
		$plugin = new AmpWpPlugin();

		add_filter(
			'amp_services',
			static function () {
				return [ 'filtered_service' => DummyService::class ];
			}
		);

		$plugin->register();

		$container = $plugin->get_container();

		$this->assertGreaterThan( 2, count( $container ) );
		$this->assertTrue( $container->has( 'injector' ) );
		$this->assertInstanceof( Injector::class, $container->get( 'injector' ) );
		$this->assertTrue( $container->has( 'plugin_registry' ) );
		$this->assertInstanceof( PluginRegistry::class, $container->get( 'plugin_registry' ) );
		$this->assertFalse( $container->has( 'filtered_service' ) );
	}
}
