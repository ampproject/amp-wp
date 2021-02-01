<?php

namespace AmpProject\AmpWP\Tests\Fixture;

final class DummyClassWithDependency implements DummyInterface {

	/** @var DummyClass */
	private $dummy;

	public function __construct( DummyClass $dummy ) {
		$this->dummy = $dummy;
	}

	public function get_dummy() {
		return $this->dummy;
	}
}
