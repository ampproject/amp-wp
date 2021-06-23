<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\LoadingError;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;

/** @coversDefaultClass \AmpProject\AmpWP\LoadingError */
class LoadingErrorTest extends DependencyInjectedTestCase {

	use AssertContainsCompatibility;

	/** @var LoadingError */
	private $instance;

	public function setUp() {
		parent::setUp();

		$this->instance = $this->injector->make( LoadingError::class );
	}

	public function test_it_can_be_initialized() {
		$this->assertInstanceOf( Service::class, $this->instance );
	}

	/** @covers ::render() */
	public function test_render() {
		$output = get_echo( [ $this->instance, 'render' ] );
		$this->assertStringContains( '<span class="amp-loading-spinner">', $output );
		$this->assertStringContains( '<div id="amp-loading-failure"', $output );
		$this->assertStringContains( '<p class="amp-loading-failure-script">', $output );
		$this->assertStringContains( '<p class="amp-loading-failure-noscript">', $output );
	}
}
