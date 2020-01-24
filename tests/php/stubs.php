<?php
// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound

// stub classes for AMP_Base_Sanitizer, since it is an abstract class
class AMP_Test_Stub_Sanitizer extends AMP_Base_Sanitizer {
	public function sanitize() {
		return $this->dom;
	}
}
