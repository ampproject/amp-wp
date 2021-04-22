<?php

namespace AmpProject\AmpWP\Tests;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\ReaderThemeSupportFeatures;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\AmpWP\Tests\Helpers\LoadsCoreThemes;

/** @coversDefaultClass \AmpProject\AmpWP\ReaderThemeSupportFeatures */
final class ReaderThemeSupportFeaturesTest extends DependencyInjectedTestCase {

	use AssertContainsCompatibility, LoadsCoreThemes;

	/** @var ReaderThemeSupportFeatures */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->instance = $this->injector->make( ReaderThemeSupportFeatures::class );

		$this->register_core_themes();
	}

	public function tearDown() {
		parent::tearDown();

		$this->restore_theme_directories();
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/** @covers ::register() */
	public function test_register() {
		$this->markTestIncomplete();
	}

	/** @covers ::has_required_feature_props() */
	public function test_has_required_feature_props() {
		$this->markTestIncomplete();
	}

	/** @covers ::filter_amp_options_updating() */
	public function test_filter_amp_options_updating() {
		$this->markTestIncomplete();
	}

	/** @covers ::handle_theme_update() */
	public function test_handle_theme_update() {
		$this->markTestIncomplete();
	}

	/** @covers ::update_cached_theme_support() */
	public function test_update_cached_theme_support() {
		$this->markTestIncomplete();
	}

	/** @covers ::get_theme_support_features() */
	public function test_get_theme_support_features() {
		$this->markTestIncomplete();
	}

	/** @covers ::is_reader_request() */
	public function test_is_reader_request() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::print_theme_support_styles()
	 * @covers ::print_editor_color_palette_styles()
	 * @covers ::print_editor_font_sizes_styles()
	 * @covers ::print_editor_gradient_presets_styles()
	 */
	public function test_print_theme_support_styles() {
		$this->markTestIncomplete();
	}

	/**  */
	public function test_get_relative_luminance_from_hex() {
		$this->markTestIncomplete();
	}
}
