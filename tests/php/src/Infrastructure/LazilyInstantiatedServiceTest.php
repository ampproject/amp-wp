<?php

namespace AmpProject\AmpWP\Tests\Infrastructure;

use AmpProject\AmpWP\Exception\InvalidService;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\ServiceContainer\LazilyInstantiatedService;
use PHPUnit\Framework\TestCase;
use stdClass;

final class LazilyInstantiatedServiceTest extends TestCase {

	public function test_it_can_be_instantiated() {
		$callable     = static function () {};
		$lazy_service = new LazilyInstantiatedService( $callable );

		$this->assertInstanceOf( LazilyInstantiatedService::class, $lazy_service );
	}

	public function test_it_can_return_the_actual_service_it_represents() {
		$callable     = function () {
			return $this->createMock( Service::class );
		};
		$lazy_service = new LazilyInstantiatedService( $callable );

		$this->assertInstanceOf( Service::class, $lazy_service->instantiate() );
	}

	public function test_it_throws_when_instantiating_an_invalid_service() {
		$callable     = function () {
			return new stdClass();
		};
		$lazy_service = new LazilyInstantiatedService( $callable );

		$this->expectException( InvalidService::class );
		$this->expectExceptionMessage( 'The service "stdClass" is not recognized and cannot be registered.' );
		$lazy_service->instantiate();
	}
}
