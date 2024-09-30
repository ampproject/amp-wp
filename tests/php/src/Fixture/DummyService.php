<?php

namespace AmpProject\AmpWP\Tests\Fixture;

use AmpProject\AmpWP\Infrastructure\Service;

final class DummyService implements Service {

	/**
	 * A method that can be triggered for tests.
	 *
	 * @return bool Always returns true.
	 */
	public function method_to_trigger()
	{
		return true;
	}
}
