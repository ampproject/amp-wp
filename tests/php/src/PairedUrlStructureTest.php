<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\PairedUrlStructure;
use AmpProject\AmpWP\Tests\Fixture\DummyPairedUrlStructure;

/** @coversDefaultClass \AmpProject\AmpWP\PairedUrlStructure */
class PairedUrlStructureTest extends DependencyInjectedTestCase {

	/** @var PairedUrlStructure */
	private $instance;

	public function set_up() {
		parent::set_up();
		$this->instance = $this->injector->make( DummyPairedUrlStructure::class );
	}

	/** @covers ::has_endpoint() */
	public function test_has_endpoint() {
		$removed = home_url( '/' );
		$added   = $this->instance->add_endpoint( $removed );

		$this->assertFalse( $this->instance->has_endpoint( $removed ) );
		$this->assertTrue( $this->instance->has_endpoint( $added ) );
	}
}
