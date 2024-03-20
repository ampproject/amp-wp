<?php

namespace AmpProject\AmpWP\Tests\Fixture;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Services;

class DummyServiceWithReferencingCondition implements Service, Conditional {

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed()
	{
		/** @var DummyService $service */
		$service = Services::get( 'service_a' );

		return $service->method_to_trigger();
	}
}
