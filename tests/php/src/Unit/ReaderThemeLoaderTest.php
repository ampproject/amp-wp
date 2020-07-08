<?php

namespace AmpProject\AmpWP\Tests\Unit;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\ReaderThemeLoader;
use AmpProject\AmpWP\Tests\AssertContainsCompatibility;
use WP_UnitTestCase;

/** @covers ReaderThemeLoader */
final class ReaderThemeLoaderTest extends WP_UnitTestCase {

	use AssertContainsCompatibility;

	/** @var ReaderThemeLoader */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->instance = new ReaderThemeLoader();
	}

	/** @covers ReaderThemeLoader::is_needed() */
	public function test_is_needed() {
		$this->markTestIncomplete();
	}

	/** @covers ReaderThemeLoader::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( ReaderThemeLoader::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
	}

	/** @covers ReaderThemeLoader::register() */
	public function test_register() {
		$this->instance->register();
		$this->assertEquals( 9, has_action( 'plugins_loaded', [ $this->instance, 'override_theme' ] ) );
	}

	/** @covers ReaderThemeLoader::get_reader_theme() */
	public function test_get_reader_theme() {
		$this->markTestIncomplete();
	}

	/** @covers ReaderThemeLoader::override_theme() */
	public function test_override_theme() {
		$this->markTestIncomplete();
	}

	/** @covers ReaderThemeLoader::disable_widgets() */
	public function test_disable_widgets() {
		$this->markTestIncomplete();
	}

	/** @covers ReaderThemeLoader::customize_previewable_devices() */
	public function test_customize_previewable_devices() {
		$this->markTestIncomplete();
	}

	/** @covers ReaderThemeLoader::remove_customizer_themes_panel() */
	public function test_remove_customizer_themes_panel() {
		$this->markTestIncomplete();
	}
}
