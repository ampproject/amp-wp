<?php

namespace AmpProject\AmpWP\Tests\Fixture;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Service;

class DummyServiceWithCondition implements Service, Conditional {

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed()
	{
		return true;
	}
}
