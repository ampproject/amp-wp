<?php
// stub classes for AMP_Base_Sanitizer, since it is an abstract class
class StubSanitizer extends AMP_Base_Sanitizer {
	public function sanitize() {
		return $this->dom;
	}
}
