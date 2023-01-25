<?php

namespace AmpProject\AmpWP\Tests\Infrastructure;

use AmpProject\AmpWP\Exception\InvalidService;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\ServiceContainer\SimpleServiceContainer;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class SimpleServiceContainerTest extends TestCase {

	public function test_it_can_be_initialized() {
		$container = new SimpleServiceContainer();

		$this->assertInstanceOf( SimpleServiceContainer::class, $container );
	}

	public function test_it_implements_the_interface() {
		$injector = new SimpleServiceContainer();

		$this->assertInstanceOf( SimpleServiceContainer::class, $injector );
	}

	public function test_it_can_be_populated_at_initialization() {
		$service = $this->createMock( Service::class );

		$container = new SimpleServiceContainer( [ 'some_service' => $service ] );

		$this->assertInstanceOf( SimpleServiceContainer::class, $container );
	}

	public function test_it_can_check_for_service_existence() {
		$service   = $this->createMock( Service::class );
		$container = new SimpleServiceContainer( [ 'known_service' => $service ] );

		$this->assertTrue( $container->has( 'known_service' ) );
		$this->assertFalse( $container->has( 'unknown_service' ) );
	}

	public function test_it_can_return_services() {
		$service   = $this->createMock( Service::class );
		$container = new SimpleServiceContainer( [ 'some_service' => $service ] );

		$retrieved_service = $container->get( 'some_service' );

		$this->assertInstanceOf( Service::class, $retrieved_service );
	}

	public function test_it_can_accept_new_services() {
		$service   = $this->createMock( Service::class );
		$container = new SimpleServiceContainer( [ 'service_a' => $service ] );

		$container->put( 'service_b', $service );

		$this->assertTrue( $container->has( 'service_a' ) );
		$this->assertInstanceOf( Service::class, $container->get( 'service_a' ) );
		$this->assertTrue( $container->has( 'service_b' ) );
		$this->assertInstanceOf( Service::class, $container->get( 'service_b' ) );
	}

	public function test_it_throws_when_retrieving_an_unknown_service() {
		$container = new SimpleServiceContainer();

		$this->expectException( InvalidService::class );
		$this->expectExceptionMessage( 'The service ID "unknown_service" is not recognized and cannot be retrieved.' );
		$container->get( 'unknown_service' );
	}
}
