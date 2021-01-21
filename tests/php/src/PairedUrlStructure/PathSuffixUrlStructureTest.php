<?php

namespace AmpProject\AmpWP\Tests\PairedUrlStructure;

use AmpProject\AmpWP\PairedUrlStructure\PathSuffixUrlStructure;

/** @coversDefaultClass \AmpProject\AmpWP\PairedUrlStructure\PathSuffixUrlStructure */
class PathSuffixUrlStructureTest extends DependencyInjectedTestCase {

	/** @var PathSuffixUrlStructure */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' ); // Needed for user_trailingslashit().
		$this->instance = $this->injector->make( PathSuffixUrlStructure::class );
	}

	/** @covers ::add_endpoint() */
	public function test_add_endpoint() {
		$slug = amp_get_slug();
		$this->assertEquals(
			home_url( "/foo/$slug/" ),
			$this->instance->add_endpoint( home_url( '/foo/' ) )
		);
	}

	/** @covers ::has_endpoint() */
	public function test_has_endpoint() {
		$slug = amp_get_slug();
		$this->assertFalse( $this->instance->has_endpoint( home_url( '/foo/' ) ) );
		$this->assertTrue( $this->instance->has_endpoint( home_url( "/foo/$slug/" ) ) );
	}

	/** @covers ::remove_endpoint() */
	public function test_remove_endpoint() {
		$slug = amp_get_slug();
		$this->assertEquals(
			home_url( '/foo/' ),
			$this->instance->remove_endpoint( home_url( "/foo/$slug/" ) )
		);
	}
}
