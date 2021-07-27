<?php

namespace AmpProject\AmpWP\Tests\Fixture;

use AmpProject\AmpWP\Infrastructure\HasRequirements;
use AmpProject\AmpWP\Infrastructure\Service;

class DummyServiceWithRequirements implements Service, HasRequirements {

	/**
	 * Get the list of service IDs required for this service to be registered.
	 *
	 * @return array<string> List of required services.
	 */
	public static function get_requirements()
	{
		return [ 'service_a' ];
	}
}
