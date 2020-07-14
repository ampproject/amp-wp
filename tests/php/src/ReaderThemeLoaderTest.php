<?php

namespace AmpProject\AmpWP\Tests;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\ReaderThemeLoader;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use WP_Customize_Manager;
use WP_Customize_Panel;
use WP_Theme;
use WP_UnitTestCase;

/** @covers ReaderThemeLoader */
final class ReaderThemeLoaderTest extends WP_UnitTestCase {

	use AssertContainsCompatibility;

	/** @var ReaderThemeLoader */
	private $instance;

	private $original_theme_directories;

	public function setUp() {
		parent::setUp();
		$this->instance = new ReaderThemeLoader();

		global $wp_theme_directories;
		$this->original_theme_directories = $wp_theme_directories;
		register_theme_directory( ABSPATH . 'wp-content/themes' );
		delete_site_transient( 'theme_roots' );
	}

	public function tearDown() {
		parent::tearDown();
		global $wp_theme_directories;
		$wp_theme_directories = $this->original_theme_directories;
		delete_site_transient( 'theme_roots' );
	}

	/** @covers ReaderThemeLoader::is_enabled() */
	public function test_is_enabled() {
		$active_theme_slug = 'twentytwenty';
		$reader_theme_slug = 'twentynineteen';
		if ( ! wp_get_theme( $active_theme_slug )->exists() || ! wp_get_theme( $reader_theme_slug )->exists() ) {
			$this->markTestSkipped();
		}

		switch_theme( $active_theme_slug );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->assertFalse( $this->instance->is_enabled() );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, ReaderThemes::DEFAULT_READER_THEME );
		$this->assertFalse( $this->instance->is_enabled() );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, $reader_theme_slug );
		$this->assertTrue( $this->instance->is_enabled() );
		$this->assertNotEquals( get_template(), $reader_theme_slug );

		$_GET[ amp_get_slug() ] = true;
		$this->instance->override_theme();
		$this->assertEquals( get_template(), $reader_theme_slug );
		$this->assertTrue( $this->instance->is_enabled() );
	}

	/** @covers ReaderThemeLoader::is_amp_request() */
	public function test_is_amp_request() {
		$_GET[ amp_get_slug() ] = '1';
		$this->assertTrue( $this->instance->is_amp_request() );

		unset( $_GET[ amp_get_slug() ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->assertFalse( $this->instance->is_amp_request() );
	}

	/** @covers ReaderThemeLoader::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( ReaderThemeLoader::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/** @covers ReaderThemeLoader::register() */
	public function test_register() {
		$this->instance->register();
		$this->assertEquals( 9, has_action( 'plugins_loaded', [ $this->instance, 'override_theme' ] ) );
	}

	/** @covers ReaderThemeLoader::get_reader_theme() */
	public function test_get_reader_theme() {
		$reader_theme_slug = 'twentynineteen';
		if ( ! wp_get_theme( $reader_theme_slug )->exists() ) {
			$this->markTestSkipped();
		}

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, 'gone' );

		$this->assertNull( $this->instance->get_reader_theme() );
		AMP_Options_Manager::update_option( Option::READER_THEME, $reader_theme_slug );
		$theme = $this->instance->get_reader_theme();
		$this->assertInstanceOf( WP_Theme::class, $theme );
		$this->assertEquals( $reader_theme_slug, $theme->get_template() );
	}

	/**
	 * @covers ReaderThemeLoader::override_theme()
	 * @covers ReaderThemeLoader::get_active_theme()
	 * @covers ReaderThemeLoader::is_theme_overridden()
	 */
	public function test_override_theme() {
		$active_theme_slug = 'twentytwenty';
		$reader_theme_slug = 'twentynineteen';
		if ( ! wp_get_theme( $active_theme_slug )->exists() || ! wp_get_theme( $reader_theme_slug )->exists() ) {
			$this->markTestSkipped();
		}
		switch_theme( $active_theme_slug );
		remove_all_filters( 'sidebars_widgets' );

		// No query var.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, $reader_theme_slug );
		unset( $_GET[ amp_get_slug() ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->instance->override_theme();
		$this->assertFalse( has_filter( 'stylesheet' ) );
		$this->assertNull( $this->instance->get_active_theme() );
		$this->assertFalse( $this->instance->is_theme_overridden() );

		// Query var but bad reader theme.
		AMP_Options_Manager::update_option( Option::READER_THEME, 'gone' );
		$_GET[ amp_get_slug() ] = 1;
		$this->instance->override_theme();
		$this->assertFalse( has_filter( 'stylesheet' ) );
		$this->assertFalse( has_filter( 'sidebars_widgets' ) );
		$this->assertNull( $this->instance->get_active_theme() );
		$this->assertFalse( $this->instance->is_theme_overridden() );

		// Query var and good theme.
		$this->assertEquals( $active_theme_slug, get_template() );
		$this->assertEquals( $active_theme_slug, get_option( 'template' ) );
		$this->assertEquals( $active_theme_slug, get_stylesheet() );
		$this->assertEquals( $active_theme_slug, get_option( 'stylesheet' ) );
		$this->assertEquals( 'Twenty Twenty', get_option( 'current_theme' ) );
		$this->assertFalse( has_filter( 'sidebars_widgets' ) );
		AMP_Options_Manager::update_option( Option::READER_THEME, $reader_theme_slug );
		$_GET[ amp_get_slug() ] = 1;
		$this->instance->override_theme();
		$this->assertTrue( $this->instance->is_theme_overridden() );
		$active_theme = $this->instance->get_active_theme();
		$this->assertInstanceOf( WP_Theme::class, $active_theme );
		$this->assertEquals( $active_theme_slug, $active_theme->get_template() );
		$this->assertTrue( has_filter( 'stylesheet' ) );
		$this->assertTrue( has_filter( 'template' ) );
		$this->assertEquals( $reader_theme_slug, get_template() );
		$this->assertEquals( $reader_theme_slug, get_option( 'template' ) );
		$this->assertEquals( $reader_theme_slug, get_stylesheet() );
		$this->assertEquals( $reader_theme_slug, get_option( 'stylesheet' ) );
		$this->assertEquals( 'Twenty Nineteen', get_option( 'current_theme' ) );
		$this->assertTrue( has_filter( 'sidebars_widgets' ) );

		$this->assertEquals( 10, has_filter( 'customize_previewable_devices', [ $this->instance, 'customize_previewable_devices' ] ) );
		$this->assertEquals( 11, has_action( 'customize_register', [ $this->instance, 'remove_customizer_themes_panel' ] ) );

	}

	/** @covers ReaderThemeLoader::disable_widgets() */
	public function test_disable_widgets() {
		remove_all_filters( 'sidebars_widgets' );
		$this->assertNotEmpty( wp_get_sidebars_widgets() );
		$this->assertContains( 'widgets', apply_filters( 'customize_loaded_components', [ 'widgets' ] ) );

		$this->instance->disable_widgets();

		$this->assertTrue( has_filter( 'sidebars_widgets' ) );
		$this->assertEquals( [], wp_get_sidebars_widgets() );
		$this->assertNotContains( 'widgets', apply_filters( 'customize_loaded_components', [ 'widgets' ] ) );
	}

	/** @covers ReaderThemeLoader::customize_previewable_devices() */
	public function test_customize_previewable_devices() {
		$original_devices = [
			'desktop' => [
				'label'   => 'Enter desktop preview mode',
				'default' => true,
			],
			'tablet'  => [
				'label' => 'Enter tablet preview mode',
			],
			'mobile'  => [
				'label' => 'Enter mobile preview mode',
			],
		];

		$devices = $this->instance->customize_previewable_devices( $original_devices );
		$this->assertTrue( empty( $devices['desktop']['default'] ) );
		$this->assertFalse( empty( $devices['tablet']['default'] ) );
	}

	/** @covers ReaderThemeLoader::remove_customizer_themes_panel() */
	public function test_remove_customizer_themes_panel() {
		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		$wp_customize = new WP_Customize_Manager();
		$wp_customize->add_panel( 'themes', [] );
		$wp_customize->add_panel( 'other', [] );
		$this->instance->remove_customizer_themes_panel( $wp_customize );
		$this->assertNull( $wp_customize->get_panel( 'themes' ) );
		$this->assertInstanceOf( WP_Customize_Panel::class, $wp_customize->get_panel( 'other' ) );
	}
}
