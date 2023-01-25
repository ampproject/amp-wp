<?php

namespace AmpProject\AmpWP\Tests\Infrastructure;

use AmpProject\AmpWP\Infrastructure\Injector\InjectionChain;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class InjectionChainTest extends TestCase {

	public function test_it_can_be_initialized() {
		$chain = new InjectionChain();

		$this->assertInstanceOf( InjectionChain::class, $chain );
	}

	public function test_it_accepts_new_resolutions() {
		$chain = ( new InjectionChain() )
			->add_resolution( 'something' );

		$this->assertTrue( $chain->has_resolution( 'something' ) );
		$this->assertFalse( $chain->has_resolution( 'something_else' ) );
	}

	public function test_it_accepts_new_chain_entries() {
		$chain = ( new InjectionChain() )
			->add_to_chain( 'something' );

		$this->assertEquals( 'something', $chain->get_class() );
	}

	public function test_it_returns_the_last_class_in_the_chain() {
		$chain = ( new InjectionChain() )
			->add_to_chain( 'first' )
			->add_to_chain( 'second' )
			->add_to_chain( 'third' );

		$this->assertEquals( 'third', $chain->get_class() );
	}

	public function test_it_retains_all_elements_in_the_chain() {
		$chain = ( new InjectionChain() )
			->add_to_chain( 'first' )
			->add_to_chain( 'second' )
			->add_to_chain( 'third' );

		$this->assertEquals( [ 'third', 'second', 'first' ], $chain->get_chain() );
	}
}
