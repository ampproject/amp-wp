<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\LoadingError;
use AmpProject\AmpWP\Infrastructure\Service;

/** @coversDefaultClass \AmpProject\AmpWP\LoadingError */
class LoadingErrorTest extends DependencyInjectedTestCase {

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
		$this->assertStringContainsString( '<span class="amp-loading-spinner">', $output );
		$this->assertStringContainsString( '<div id="amp-loading-failure"', $output );
		$this->assertStringContainsString( '<p class="amp-loading-failure-script">', $output );
		$this->assertStringContainsString( '<p class="amp-loading-failure-noscript">', $output );
	}
}
