<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\PairedUrlStructure\QueryVarUrlStructure;

/** @coversDefaultClass \AmpProject\AmpWP\PairedUrlStructure\QueryVarUrlStructure */
class QueryVarUrlStructureTest extends DependencyInjectedTestCase {

	/** @var QueryVarUrlStructure */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->instance = $this->injector->make( QueryVarUrlStructure::class );
	}

	/** @covers ::add_endpoint() */
	public function test_add_endpoint() {
		$slug = amp_get_slug();
		$this->assertEquals(
			home_url( "/foo/?{$slug}=1" ),
			$this->instance->add_endpoint( home_url( '/foo/' ) )
		);
	}

	/** @covers ::has_endpoint() */
	public function test_has_endpoint() {
		$slug = amp_get_slug();
		$this->assertFalse( $this->instance->has_endpoint( home_url( '/foo/' ) ) );
		$this->assertTrue( $this->instance->has_endpoint( home_url( "/foo/?{$slug}=1" ) ) );
	}

	/** @covers ::remove_endpoint() */
	public function test_remove_endpoint() {
		$slug = amp_get_slug();
		$this->assertEquals(
			home_url( '/foo/' ),
			$this->instance->remove_endpoint( home_url( "/foo/?{$slug}=1" ) )
		);
	}
}
