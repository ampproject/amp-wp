<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\PairedUrl;
use AmpProject\AmpWP\Infrastructure\Service;

/** @coversDefaultClass \AmpProject\AmpWP\PairedUrl */
class PairedUrlTest extends DependencyInjectedTestCase {

	/** @var PairedUrl */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->instance = $this->injector->make( PairedUrl::class );
	}

	public function test__construct() {
		$this->assertInstanceOf( PairedUrl::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
	}

	/** @covers ::remove_query_var() */
	public function test_remove_query_var() {
		$slug = amp_get_slug();

		$this->assertEquals(
			'/foo/?bar=1',
			$this->instance->remove_query_var( "/foo/?bar=1&{$slug}=1" )
		);

		$this->assertEquals(
			'/foo/',
			$this->instance->remove_query_var( "/foo/?{$slug}=1" )
		);

		$this->assertEquals(
			'/foo/',
			$this->instance->remove_query_var( "/foo/?{$slug}" )
		);
	}

	/** @covers ::has_path_suffix() */
	public function test_has_path_suffix() {
		$slug = amp_get_slug();
		$this->assertFalse( $this->instance->has_path_suffix( '/foo/' ) );
		$this->assertTrue( $this->instance->has_path_suffix( "/foo/$slug/" ) );
		$this->assertTrue( $this->instance->has_path_suffix( "/foo/$slug/?bar=1" ) );
		$this->assertTrue( $this->instance->has_path_suffix( "/foo/$slug/#bar" ) );
		$this->assertTrue( $this->instance->has_path_suffix( "/foo/$slug" ) );
		$this->assertTrue( $this->instance->has_path_suffix( "/foo/$slug?bar=1" ) );
		$this->assertTrue( $this->instance->has_path_suffix( "/foo/$slug#bar" ) );
	}

	/** @covers ::remove_path_suffix() */
	public function test_remove_path_suffix() {
		$slug = amp_get_slug();
		$this->assertEquals( '/foo/', $this->instance->remove_path_suffix( '/foo/' ) );
		$this->assertEquals( '/foo/', $this->instance->remove_path_suffix( "/foo/$slug/" ) );
		$this->assertEquals( '/foo', $this->instance->remove_path_suffix( "/foo/$slug" ) );
		$this->assertEquals( '/foo/#bar', $this->instance->remove_path_suffix( "/foo/$slug/#bar" ) );
		$this->assertEquals( '/foo/?bar=1', $this->instance->remove_path_suffix( "/foo/$slug/?bar=1" ) );
	}

	/** @covers ::has_query_var() */
	public function test_has_query_var() {
		$slug = amp_get_slug();
		$this->assertTrue( $this->instance->has_query_var( home_url( "/foo/?$slug=1" ) ) );
		$this->assertTrue( $this->instance->has_query_var( "/foo/?bar=1&$slug=1" ) );
		$this->assertFalse( $this->instance->has_query_var( '/foo/?bar=1' ) );
		$this->assertFalse( $this->instance->has_query_var( '/foo/' ) );
		$this->assertFalse( $this->instance->has_query_var( "/foo/#$slug=1" ) );
	}

	/** @covers ::add_query_var() */
	public function test_add_query_var() {
		$slug = amp_get_slug();
		$this->assertEquals( "/foo/?$slug=1", $this->instance->add_query_var( '/foo/' ) );
		$this->assertEquals( "/foo/?bar=1&$slug=1", $this->instance->add_query_var( '/foo/?bar=1' ) );
		$this->assertEquals( "/foo/?$slug=1#bar", $this->instance->add_query_var( '/foo/#bar' ) );
	}

	/** @covers ::add_path_suffix() */
	public function test_add_path_suffix() {
		$this->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' ); // Needed for user_trailingslashit().
		$slug = amp_get_slug();
		$this->assertEquals( home_url( "/foo/$slug/" ), $this->instance->add_path_suffix( home_url( '/foo/' ) ) );
		$this->assertEquals( home_url( "/foo/$slug/?bar=1" ), $this->instance->add_path_suffix( home_url( '/foo/?bar=1' ) ) );
		$this->assertEquals( home_url( "/foo/$slug/#bar" ), $this->instance->add_path_suffix( home_url( '/foo/#bar' ) ) );
	}
}
