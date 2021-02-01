<?php

namespace AmpProject\AmpWP\Tests\Helpers;

use AMP_Base_Sanitizer;

/**
 * Class StubSanitizer.
 *
 * Stub class for AMP_Base_Sanitizer, since it is an abstract class.
 */
class StubSanitizer extends AMP_Base_Sanitizer {

	public function sanitize() {
		return $this->dom;
	}
}
