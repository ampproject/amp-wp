<?php

namespace AmpProject\AmpWP\Tests\Fixture;

use AmpProject\AmpWP\Infrastructure\ServiceBasedPlugin;

class DummyServiceBasedPlugin extends ServiceBasedPlugin {

	/**
	 * Get the list of services to register.
	 *
	 * @return array<string> Associative array of identifiers mapped to fully
	 *                       qualified class names.
	 */
	protected function get_service_classes() {
		return [
			'service_a' => DummyService::class,
			'service_b' => DummyService::class,
		];
	}
}
