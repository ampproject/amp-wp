<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\ExtraThemeAndPluginHeaders;

/** @coversDefaultClass \AmpProject\AmpWP\ExtraThemeAndPluginHeaders */
final class ExtraThemeAndPluginHeadersTest extends TestCase {

	/** @var ExtraThemeAndPluginHeaders */
	private $instance;

	public function set_up() {
		parent::set_up();
		$this->instance = new ExtraThemeAndPluginHeaders();
	}

	public function test__construct() {
		$this->assertInstanceOf( ExtraThemeAndPluginHeaders::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/** @covers ::register() */
	public function test_register() {
		$this->instance->register();
		$this->assertEquals( 10, has_action( 'extra_theme_headers', [ $this->instance, 'filter_extra_headers' ] ) );
	}

	/** @covers ::filter_extra_headers() */
	public function test_filter_extra_headers() {
		$this->assertStringContainsString( ExtraThemeAndPluginHeaders::AMP_HEADER, $this->instance->filter_extra_headers( [ 'Woo' ] ) );
	}
}
