<?php

namespace AmpProject\AmpWP\Tests\Infrastructure;

use AmpProject\AmpWP\Exception\FailedToMakeInstance;
use AmpProject\AmpWP\Infrastructure\Injector;
use AmpProject\AmpWP\Infrastructure\Injector\SimpleInjector;
use AmpProject\AmpWP\Tests\Fixture;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;
use stdClass;

final class SimpleInjectorTest extends TestCase {

	public function test_it_can_be_initialized() {
		$injector = new SimpleInjector();

		$this->assertInstanceOf( SimpleInjector::class, $injector );
	}

	public function test_it_implements_the_interface() {
		$injector = new SimpleInjector();

		$this->assertInstanceOf( Injector::class, $injector );
	}

	public function test_it_can_instantiate_a_concrete_class() {
		$object = ( new SimpleInjector() )
			->make( Fixture\DummyClass::class );

		$this->assertInstanceOf( Fixture\DummyClass::class, $object );
	}

	public function test_it_can_autowire_a_class_with_a_dependency() {
		$object = ( new SimpleInjector() )
			->make( Fixture\DummyClassWithDependency::class );

		$this->assertInstanceOf( Fixture\DummyClassWithDependency::class, $object );
		$this->assertInstanceOf( Fixture\DummyClass::class, $object->get_dummy() );
	}

	public function test_it_can_instantiate_a_bound_interface() {
		$injector = ( new SimpleInjector() )
			->bind(
				Fixture\DummyInterface::class,
				Fixture\DummyClassWithDependency::class
			);
		$object   = $injector->make( Fixture\DummyInterface::class );

		$this->assertInstanceOf( Fixture\DummyInterface::class, $object );
		$this->assertInstanceOf( Fixture\DummyClassWithDependency::class, $object );
		$this->assertInstanceOf( Fixture\DummyClass::class, $object->get_dummy() );
	}

	public function test_it_returns_separate_instances_by_default() {
		$injector = new SimpleInjector();
		$object_a = $injector->make( Fixture\DummyClass::class );
		$object_b = $injector->make( Fixture\DummyClass::class );

		$this->assertNotSame( $object_a, $object_b );
	}

	public function test_it_returns_same_instances_if_shared() {
		$injector = ( new SimpleInjector() )
			->share( Fixture\DummyClass::class );
		$object_a = $injector->make( Fixture\DummyClass::class );
		$object_b = $injector->make( Fixture\DummyClass::class );

		$this->assertSame( $object_a, $object_b );
	}

	public function test_it_can_instantiate_a_class_with_named_arguments() {
		$object = ( new SimpleInjector() )
			->make(
				Fixture\DummyClassWithNamedArguments::class,
				[
					'argument_a' => 42,
					'argument_b' => 'Mr Alderson',
				]
			);

		$this->assertInstanceOf( Fixture\DummyClassWithNamedArguments::class, $object );
		$this->assertEquals( 42, $object->get_argument_a() );
		$this->assertEquals( 'Mr Alderson', $object->get_argument_b() );
	}

	public function test_it_allows_for_skipping_named_arguments_with_default_values() {
		$object = ( new SimpleInjector() )
			->make(
				Fixture\DummyClassWithNamedArguments::class,
				[ 'argument_a' => 42 ]
			);

		$this->assertInstanceOf( Fixture\DummyClassWithNamedArguments::class, $object );
		$this->assertEquals( 42, $object->get_argument_a() );
		$this->assertEquals( 'Mr Meeseeks', $object->get_argument_b() );
	}

	public function test_it_throws_if_a_required_named_arguments_is_missing() {
		$this->expectException( FailedToMakeInstance::class );

		( new SimpleInjector() )
			->make( Fixture\DummyClassWithNamedArguments::class );
	}

	public function test_it_throws_if_a_circular_reference_is_detected() {
		$this->expectException( FailedToMakeInstance::class );
		$this->expectExceptionCode( FailedToMakeInstance::CIRCULAR_REFERENCE );

		( new SimpleInjector() )
			->bind(
				Fixture\DummyClass::class,
				Fixture\DummyClassWithDependency::class
			)
			->make( Fixture\DummyClassWithDependency::class );
	}

	public function test_it_can_delegate_instantiation() {
		$injector = ( new SimpleInjector() )
			->delegate(
				Fixture\DummyInterface::class,
				static function ( $class ) {
					$object             = new stdClass();
					$object->class_name = $class;
					return $object;
				}
			);
		$object   = $injector->make( Fixture\DummyInterface::class );

		$this->assertInstanceOf( stdClass::class, $object );
		$this->assertObjectHasAttribute( 'class_name', $object );
		$this->assertEquals( Fixture\DummyInterface::class, $object->class_name );
	}

	public function test_delegation_works_across_resolution() {
		$injector = ( new SimpleInjector() )
			->bind(
				Fixture\DummyInterface::class,
				Fixture\DummyClassWithDependency::class
			)
			->delegate(
				Fixture\DummyClassWithDependency::class,
				static function ( $class ) {
					$object             = new stdClass();
					$object->class_name = $class;
					return $object;
				}
			);
		$object   = $injector->make( Fixture\DummyInterface::class );

		$this->assertInstanceOf( stdClass::class, $object );
		$this->assertObjectHasAttribute( 'class_name', $object );
		$this->assertEquals( Fixture\DummyClassWithDependency::class, $object->class_name );
	}

	public function test_arguments_can_be_bound() {
		$object = ( new SimpleInjector() )
			->bind_argument(
				Fixture\DummyClassWithNamedArguments::class,
				'argument_a',
				42
			)
			->bind_argument(
				SimpleInjector::GLOBAL_ARGUMENTS,
				'argument_b',
				'Mr Alderson'
			)
			->make( Fixture\DummyClassWithNamedArguments::class );

		$this->assertInstanceOf( Fixture\DummyClassWithNamedArguments::class, $object );
		$this->assertEquals( 42, $object->get_argument_a() );
		$this->assertEquals( 'Mr Alderson', $object->get_argument_b() );
	}

	public function test_callable_arguments_are_lazily_resolved() {
		$injector = new SimpleInjector();
		$injector->bind_argument(
			Fixture\DummyClassWithNamedArguments::class,
			'argument_a',
			static function ( $class, $parameter, $arguments ) {
				return $arguments['number']; }
		);

		$object = $injector->make( Fixture\DummyClassWithNamedArguments::class, [ 'number' => 123 ] );

		$this->assertInstanceOf( Fixture\DummyClassWithNamedArguments::class, $object );
		$this->assertEquals( 123, $object->get_argument_a() );
	}
}
