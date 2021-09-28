<?php
/**
 * Tests for AMP_Template_Customizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\ReaderThemeLoader;
use AmpProject\AmpWP\Tests\Helpers\LoadsCoreThemes;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;

/**
 * Class Test_AMP_Template_Customizer
 *
 * @covers AMP_Template_Customizer
 */
class Test_AMP_Template_Customizer extends DependencyInjectedTestCase {

	use PrivateAccess;
	use LoadsCoreThemes;

	public static function set_up_before_class() {
		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		parent::set_up_before_class();
	}

	public function set_up() {
		parent::set_up();

		$this->register_core_themes();
	}

	public function tear_down() {
		unset( $GLOBALS['wp_customize'], $GLOBALS['wp_scripts'], $GLOBALS['wp_styles'] );

		$this->restore_theme_directories();

		parent::tear_down();
	}

	/**
	 * Get Customizer Manager.
	 *
	 * @param array $args Args.
	 * @return WP_Customize_Manager
	 */
	protected function get_customize_manager( $args = [] ) {
		global $wp_customize;
		$wp_customize = new WP_Customize_Manager( $args );
		return $wp_customize;
	}

	/** @covers AMP_Template_Customizer::init() */
	public function test_init_canonical() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->assertTrue( amp_is_canonical() );

		add_theme_support( 'header-video', [ 'video' => [] ] );
		$wp_customize = $this->get_customize_manager();
		$wp_customize->register_controls();
		$header_video_setting          = $wp_customize->get_setting( 'header_video' );
		$external_header_video_setting = $wp_customize->get_setting( 'external_header_video' );
		foreach ( [ $header_video_setting, $external_header_video_setting ] as $setting ) {
			$this->assertInstanceOf( WP_Customize_Setting::class, $setting );
			$this->assertEquals( 'postMessage', $setting->transport );
		}

		$instance = AMP_Template_Customizer::init( $wp_customize );
		$this->assertFalse( has_action( 'customize_controls_init', [ $instance, 'set_reader_preview_url' ] ) );
		$this->assertEquals( 0, did_action( 'amp_customizer_init' ) );
		$this->assertFalse( has_action( 'customize_controls_enqueue_scripts', [ $instance, 'add_customizer_scripts' ] ) );

		foreach ( [ $header_video_setting, $external_header_video_setting ] as $setting ) {
			$this->assertEquals( 'refresh', $setting->transport );
		}
	}

	/** @covers AMP_Template_Customizer::init() */
	public function test_init_transitional() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->assertFalse( amp_is_canonical() );

		add_theme_support( 'header-video', [ 'video' => [] ] );
		$wp_customize = $this->get_customize_manager();
		$wp_customize->register_controls();
		$header_video_setting          = $wp_customize->get_setting( 'header_video' );
		$external_header_video_setting = $wp_customize->get_setting( 'external_header_video' );
		foreach ( [ $header_video_setting, $external_header_video_setting ] as $setting ) {
			$this->assertInstanceOf( WP_Customize_Setting::class, $setting );
			$this->assertEquals( 'postMessage', $setting->transport );
		}

		$instance = AMP_Template_Customizer::init( $wp_customize );
		$this->assertFalse( has_action( 'customize_controls_init', [ $instance, 'set_reader_preview_url' ] ) );
		$this->assertEquals( 0, did_action( 'amp_customizer_init' ) );
		$this->assertFalse( has_action( 'customize_save_after', [ $instance, 'store_modified_theme_mod_setting_timestamps' ] ) );
		$this->assertFalse( has_action( 'customize_controls_enqueue_scripts', [ $instance, 'add_customizer_scripts' ] ) );

		foreach ( [ $header_video_setting, $external_header_video_setting ] as $setting ) {
			$this->assertEquals( 'postMessage', $setting->transport );
		}
	}

	/**
	 * @covers AMP_Template_Customizer::init()
	 * @covers AMP_Template_Customizer::register_legacy_ui()
	 * @covers AMP_Template_Customizer::register_legacy_settings()
	 * @covers AMP_Template_Customizer::set_refresh_setting_transport()
	 * @covers AMP_Template_Customizer::remove_cover_template_section()
	 * @covers AMP_Template_Customizer::remove_homepage_settings_section()
	 */
	public function test_init_legacy_reader() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertFalse( amp_is_canonical() );
		$this->assertTrue( amp_is_legacy() );

		add_theme_support( 'header-video', [ 'video' => [] ] );
		$wp_customize = $this->get_customize_manager();
		$wp_customize->register_controls();
		$wp_customize->add_section( 'cover_template_options', [] );
		$header_video_setting          = $wp_customize->get_setting( 'header_video' );
		$external_header_video_setting = $wp_customize->get_setting( 'external_header_video' );
		foreach ( [ $header_video_setting, $external_header_video_setting ] as $setting ) {
			$this->assertInstanceOf( WP_Customize_Setting::class, $setting );
			$this->assertEquals( 'postMessage', $setting->transport );
		}

		$instance = AMP_Template_Customizer::init( $wp_customize );
		$this->assertEquals( 10, has_action( 'customize_controls_init', [ $instance, 'set_reader_preview_url' ] ) );
		$this->assertFalse( has_action( 'customize_save_after', [ $instance, 'store_modified_theme_mod_setting_timestamps' ] ) );
		$this->assertFalse( has_action( 'customize_controls_enqueue_scripts', [ $instance, 'add_customizer_scripts' ] ) );
		$this->assertEquals( 1, did_action( 'amp_customizer_init' ) );
		$this->assertEquals( 1, did_action( 'amp_customizer_register_settings' ) );
		$this->assertEquals( 1, did_action( 'amp_customizer_register_ui' ) );
		$this->assertInstanceOf( WP_Customize_Panel::class, $wp_customize->get_panel( AMP_Template_Customizer::PANEL_ID ) );
		$this->assertEquals( 10, has_action( 'customize_controls_print_footer_scripts', [ $instance, 'print_legacy_controls_templates' ] ) );
		$this->assertEquals( 10, has_action( 'customize_preview_init', [ $instance, 'init_legacy_preview' ] ) );
		$this->assertEquals( 10, has_action( 'customize_controls_enqueue_scripts', [ $instance, 'add_legacy_customizer_scripts' ] ) );

		foreach ( [ $header_video_setting, $external_header_video_setting ] as $setting ) {
			$this->assertEquals( 'postMessage', $setting->transport );
		}
		$this->assertInstanceOf( WP_Customize_Section::class, $wp_customize->get_section( 'cover_template_options' ) );
		$this->assertInstanceOf( WP_Customize_Section::class, $wp_customize->get_section( 'static_front_page' ) );
	}

	/**
	 * @covers AMP_Template_Customizer::init()
	 * @covers AMP_Template_Customizer::set_refresh_setting_transport()
	 * @covers AMP_Template_Customizer::remove_cover_template_section()
	 * @covers AMP_Template_Customizer::remove_homepage_settings_section()
	 */
	public function test_init_reader_theme_with_amp() {
		if ( ! wp_get_theme( 'twentynineteen' )->exists() || ! wp_get_theme( 'twentytwenty' )->exists() ) {
			$this->markTestSkipped();
		}

		switch_theme( 'twentynineteen' );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, 'twentytwenty' );

		$reader_theme_loader = $this->injector->make( ReaderThemeLoader::class );

		$post = self::factory()->post->create();
		$this->go_to( amp_get_permalink( $post ) );
		$reader_theme_loader->override_theme();
		$this->assertTrue( $reader_theme_loader->is_theme_overridden() );

		$this->assertFalse( amp_is_canonical() );
		$this->assertFalse( amp_is_legacy() );

		add_theme_support( 'header-video', [ 'video' => [] ] );
		$wp_customize = $this->get_customize_manager();
		$wp_customize->register_controls();
		$wp_customize->add_section( 'cover_template_options', [] );
		$header_video_setting          = $wp_customize->get_setting( 'header_video' );
		$external_header_video_setting = $wp_customize->get_setting( 'external_header_video' );
		foreach ( [ $header_video_setting, $external_header_video_setting ] as $setting ) {
			$this->assertInstanceOf( WP_Customize_Setting::class, $setting );
			$this->assertEquals( 'postMessage', $setting->transport );
		}

		$instance = AMP_Template_Customizer::init( $wp_customize );
		$this->assertEquals( 10, has_action( 'customize_controls_init', [ $instance, 'set_reader_preview_url' ] ) );
		$this->assertEquals( 10, has_action( 'customize_save_after', [ $instance, 'store_modified_theme_mod_setting_timestamps' ] ) );
		$this->assertEquals( 10, has_action( 'customize_controls_enqueue_scripts', [ $instance, 'add_customizer_scripts' ] ) );
		$this->assertEquals( 10, has_action( 'customize_controls_print_footer_scripts', [ $instance, 'render_setting_import_section_template' ] ) );
		$this->assertEquals( 0, did_action( 'amp_customizer_init' ) );
		$this->assertEquals( 0, did_action( 'amp_customizer_register_settings' ) );
		$this->assertEquals( 0, did_action( 'amp_customizer_register_ui' ) );
		$this->assertFalse( has_action( 'customize_controls_print_footer_scripts', [ $instance, 'print_legacy_controls_templates' ] ) );
		$this->assertFalse( has_action( 'customize_preview_init', [ $instance, 'init_legacy_preview' ] ) );
		$this->assertFalse( has_action( 'customize_controls_enqueue_scripts', [ $instance, 'add_legacy_customizer_scripts' ] ) );
		$this->assertFalse( has_action( 'customize_controls_print_footer_scripts', [ $instance, 'add_dark_mode_toggler_button_notice' ] ) );

		foreach ( [ $header_video_setting, $external_header_video_setting ] as $setting ) {
			$this->assertEquals( 'refresh', $setting->transport );
		}

		$this->assertNull( $wp_customize->get_section( 'cover_template_options' ) );
		$this->assertNull( $wp_customize->get_section( 'static_front_page' ) );
	}

	/** @covers AMP_Template_Customizer::set_reader_preview_url() */
	public function test_set_reader_preview_url() {
		$post_id      = self::factory()->post->create();
		$wp_customize = $this->get_customize_manager();
		$instance     = AMP_Template_Customizer::init( $wp_customize );

		$this->assertEquals( amp_get_permalink( $post_id ), amp_admin_get_preview_permalink() );
		$this->assertEquals( home_url( '/' ), $wp_customize->get_preview_url() );

		$instance->set_reader_preview_url();
		$this->assertEquals( home_url( '/' ), $wp_customize->get_preview_url() );

		$_GET[ QueryVar::AMP_PREVIEW ] = '1';
		$instance->set_reader_preview_url();
		$this->assertNotEquals( home_url( '/' ), $wp_customize->get_preview_url() );
		$this->assertEquals( amp_admin_get_preview_permalink(), $wp_customize->get_preview_url() );

		$wp_customize->set_preview_url( home_url( '/foo/' ) );
		$_GET[ QueryVar::AMP_PREVIEW ] = '1';
		$_GET['url']                   = home_url( '/foo/' );
		$instance->set_reader_preview_url();
		$this->assertEquals( home_url( '/foo/' ), $wp_customize->get_preview_url() );
	}

	/**
	 * @covers AMP_Template_Customizer::init()
	 * @covers AMP_Template_Customizer::add_dark_mode_toggler_button_notice()
	 */
	public function test_init_for_twentytwentyone() {
		if ( ! wp_get_theme( 'twentytwentyone' )->exists() ) {
			$this->markTestSkipped();
		}
		switch_theme( 'twentytwentyone' );

		$wp_customize = $this->get_customize_manager();
		$instance     = AMP_Template_Customizer::init( $wp_customize );
		$this->assertEquals( 10, has_action( 'customize_controls_print_footer_scripts', [ $instance, 'add_dark_mode_toggler_button_notice' ] ) );

		$output = get_echo( [ $instance, 'add_dark_mode_toggler_button_notice' ] );
		$this->assertStringContainsString( 'wp.customize.control', $output );
	}

	/**
	 * @covers AMP_Template_Customizer::init()
	 * @covers AMP_Template_Customizer::set_refresh_setting_transport()
	 * @covers AMP_Template_Customizer::remove_cover_template_section()
	 * @covers AMP_Template_Customizer::remove_homepage_settings_section()
	 */
	public function test_init_reader_theme_without_amp() {
		if ( ! wp_get_theme( 'twentynineteen' )->exists() || ! wp_get_theme( 'twentytwenty' )->exists() ) {
			$this->markTestSkipped();
		}

		switch_theme( 'twentynineteen' );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, 'twentytwenty' );

		$reader_theme_loader = $this->injector->make( ReaderThemeLoader::class );

		$post = self::factory()->post->create();
		$this->go_to( get_permalink( $post ) );
		$reader_theme_loader->override_theme();
		$this->assertFalse( $reader_theme_loader->is_theme_overridden() );

		$this->assertFalse( amp_is_canonical() );
		$this->assertFalse( amp_is_legacy() );

		add_theme_support( 'header-video', [ 'video' => [] ] );
		$wp_customize = $this->get_customize_manager();
		$wp_customize->register_controls();
		$wp_customize->add_section( 'cover_template_options', [] );
		$header_video_setting          = $wp_customize->get_setting( 'header_video' );
		$external_header_video_setting = $wp_customize->get_setting( 'external_header_video' );
		foreach ( [ $header_video_setting, $external_header_video_setting ] as $setting ) {
			$this->assertInstanceOf( WP_Customize_Setting::class, $setting );
			$this->assertEquals( 'postMessage', $setting->transport );
		}

		$instance = AMP_Template_Customizer::init( $wp_customize );
		$this->assertEquals( 10, has_action( 'customize_save_after', [ $instance, 'store_modified_theme_mod_setting_timestamps' ] ) );

		$this->assertFalse( has_action( 'customize_controls_enqueue_scripts', [ $instance, 'add_customizer_scripts' ] ) );
		$this->assertFalse( has_action( 'customize_controls_print_footer_scripts', [ $instance, 'render_setting_import_section_template' ] ) );
		$this->assertEquals( 0, did_action( 'amp_customizer_init' ) );
		$this->assertEquals( 0, did_action( 'amp_customizer_register_settings' ) );
		$this->assertEquals( 0, did_action( 'amp_customizer_register_ui' ) );
		$this->assertFalse( has_action( 'customize_controls_print_footer_scripts', [ $instance, 'print_legacy_controls_templates' ] ) );
		$this->assertFalse( has_action( 'customize_preview_init', [ $instance, 'init_legacy_preview' ] ) );
		$this->assertFalse( has_action( 'customize_controls_enqueue_scripts', [ $instance, 'add_legacy_customizer_scripts' ] ) );

		foreach ( [ $header_video_setting, $external_header_video_setting ] as $setting ) {
			$this->assertEquals( 'postMessage', $setting->transport );
		}

		$this->assertInstanceOf( WP_Customize_Section::class, $wp_customize->get_section( 'cover_template_options' ) );
		$this->assertInstanceOf( WP_Customize_Section::class, $wp_customize->get_section( 'static_front_page' ) );
	}

	/** @covers AMP_Template_Customizer::init_legacy_preview() */
	public function test_init_legacy_preview_controls() {
		$wp_customize = $this->get_customize_manager();
		$instance     = AMP_Template_Customizer::init( $wp_customize );
		$instance->init_legacy_preview();
		if ( version_compare( strtok( get_bloginfo( 'version' ), '-' ), '5.7', '<' ) ) {
			$this->assertEquals( 10, has_action( 'amp_post_template_head', 'wp_no_robots' ) );
		} else {
			$this->assertFalse( has_action( 'amp_post_template_head', 'wp_no_robots' ) );
		}
		$this->assertEquals( 10, has_action( 'amp_customizer_enqueue_preview_scripts', [ $instance, 'enqueue_legacy_preview_scripts' ] ) );
		$this->assertNull( $wp_customize->get_messenger_channel() );
	}

	/** @covers AMP_Template_Customizer::init_legacy_preview() */
	public function test_init_legacy_preview_iframe() {
		$wp_customize = $this->get_customize_manager( [ 'messenger_channel' => '123' ] );
		$wp_customize->start_previewing_theme();
		$instance = AMP_Template_Customizer::init( $wp_customize );
		$instance->init_legacy_preview();
		if ( version_compare( strtok( get_bloginfo( 'version' ), '-' ), '5.7', '<' ) ) {
			$this->assertEquals( 10, has_action( 'amp_post_template_head', 'wp_no_robots' ) );
		} else {
			$this->assertFalse( has_action( 'amp_post_template_head', 'wp_no_robots' ) );
		}
		$this->assertEquals( 10, has_action( 'amp_customizer_enqueue_preview_scripts', [ $instance, 'enqueue_legacy_preview_scripts' ] ) );
		$this->assertEquals( '123', $wp_customize->get_messenger_channel() );

		$this->assertEquals( 10, has_action( 'amp_post_template_head', [ $wp_customize, 'customize_preview_loading_style' ] ) );
		$this->assertEquals( 10, has_action( 'amp_post_template_css', [ $instance, 'add_legacy_customize_preview_styles' ] ) );
		$this->assertEquals( 10, has_action( 'amp_post_template_head', [ $wp_customize, 'remove_frameless_preview_messenger_channel' ] ) );
		$this->assertEquals( 10, has_action( 'amp_post_template_footer', [ $instance, 'add_legacy_preview_scripts' ] ) );
	}

	/** @covers AMP_Template_Customizer::add_customizer_scripts() */
	public function test_add_customizer_scripts() {
		$instance = AMP_Template_Customizer::init( $this->get_customize_manager() );
		$instance->add_customizer_scripts();

		$this->assertTrue( wp_script_is( 'amp-customize-controls', 'enqueued' ) );
		$this->assertStringContainsString( 'amp-customize-controls.js', wp_scripts()->registered['amp-customize-controls']->src );
		$this->assertTrue( wp_style_is( 'amp-customizer', 'enqueued' ) );
		$this->assertStringContainsString( 'amp-customizer.css', wp_styles()->registered['amp-customizer']->src );
		$this->assertEquals( 0, did_action( 'amp_customizer_enqueue_scripts' ) );
	}

	/** @covers AMP_Template_Customizer::store_modified_theme_mod_setting_timestamps() */
	public function test_store_modified_theme_mod_setting_timestamps() {
		if ( ! wp_get_theme( 'twentytwenty' )->exists() ) {
			$this->markTestSkipped();
		}

		$menu_location = 'primary';
		register_nav_menu( $menu_location, 'Primary' );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		switch_theme( 'twentytwenty' );
		$wp_customize = $this->get_customize_manager();
		$wp_customize->register_controls();
		$wp_customize->nav_menus->customize_register();
		$instance = AMP_Template_Customizer::init( $wp_customize );

		$option_setting    = $wp_customize->add_setting( 'some_option', [ 'type' => 'option' ] );
		$theme_mod_setting = $wp_customize->add_setting( 'some_theme_mod', [ 'type' => 'theme_mod' ] );
		$filter_setting    = $wp_customize->add_setting( new WP_Customize_Filter_Setting( $wp_customize, 'some_filter' ) );

		$custom_css_setting = $wp_customize->get_setting( sprintf( 'custom_css[%s]', get_stylesheet() ) );
		$this->assertInstanceOf( WP_Customize_Custom_CSS_Setting::class, $custom_css_setting );

		$nav_menu_location_setting = $wp_customize->get_setting( "nav_menu_locations[{$menu_location}]" );
		$this->assertInstanceOf( WP_Customize_Setting::class, $nav_menu_location_setting );

		// Ensure initial state.
		$this->assertFalse( get_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ) );

		// Ensure empty when no changes have been made.
		$instance->store_modified_theme_mod_setting_timestamps();
		$this->assertFalse( get_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ) );

		// Ensure updating an option does not cause the theme_mod to be updated.
		$wp_customize->set_post_value( $option_setting->id, 'foo' );
		$instance->store_modified_theme_mod_setting_timestamps();
		$this->assertFalse( get_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ) );

		// Ensure updating a "filter setting" does not cause the theme_mod to be updated.
		$wp_customize->set_post_value( $filter_setting->id, 'foo2' );
		$instance->store_modified_theme_mod_setting_timestamps();
		$this->assertFalse( get_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ) );

		// Ensure updating a theme_mod does cause the theme_mod to be updated.
		$wp_customize->set_post_value( $theme_mod_setting->id, 'bar' );
		$instance->store_modified_theme_mod_setting_timestamps();
		$this->assertEqualSets(
			[ $theme_mod_setting->id ],
			array_keys( get_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ) )
		);

		// Ensure that setting a nav menu updates the timestamps.
		$wp_customize->set_post_value( $nav_menu_location_setting->id, wp_create_nav_menu( 'Menu!' ) );
		$instance->store_modified_theme_mod_setting_timestamps();
		$this->assertEqualSets(
			[ $theme_mod_setting->id, $nav_menu_location_setting->id ],
			array_keys( get_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ) )
		);

		// Ensure that changing custom CSS also updates timestamps.
		$wp_customize->set_post_value( $custom_css_setting->id, 'body { color:red }' );
		$instance->store_modified_theme_mod_setting_timestamps();
		$this->assertEqualSets(
			[ $theme_mod_setting->id, $nav_menu_location_setting->id, 'custom_css' ],
			array_keys( get_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ) )
		);

		foreach ( get_theme_mod( AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ) as $timestamp ) {
			$this->assertGreaterThanOrEqual( time(), $timestamp );
		}
	}

	/** @covers AMP_Template_Customizer::get_active_theme_import_settings() */
	public function test_get_active_theme_import_settings() {
		$active_theme_slug = 'twentynineteen';
		$reader_theme_slug = 'twentytwenty';
		if ( ! wp_get_theme( $active_theme_slug )->exists() || ! wp_get_theme( $reader_theme_slug )->exists() ) {
			$this->markTestSkipped();
		}

		switch_theme( $active_theme_slug );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::READER_THEME, $reader_theme_slug );

		// Set initial theme mods for active theme.
		$menu_location = 'primary';
		register_nav_menu( $menu_location, 'Primary' );
		$menu_id = wp_create_nav_menu( 'Top' );
		set_theme_mod( 'background_color', '60af88' );
		set_theme_mod(
			'nav_menu_locations',
			[
				$menu_location => $menu_id,
			]
		);
		$background_color_value  = '60af88';
		$child_value             = 'baby';
		$background_image_value  = 'https://example.com/bg.jpg';
		$background_repeat_value = 'repeat-y';
		update_option(
			"theme_mods_{$active_theme_slug}",
			[
				'background_color'   => $background_color_value,
				'family'             => [
					'parent' => [
						'child' => $child_value,
					],
				],
				'nav_menu_locations' => [
					$menu_location => $menu_id,
				],
				'background_image'   => $background_image_value,
				'background_repeat'  => $background_repeat_value,
			]
		);
		$custom_css_value = 'body { color: red }';
		wp_update_custom_css_post( $custom_css_value );
		$this->assertEqualSets(
			[ 'background_color', 'nav_menu_locations', 'custom_css_post_id', 'family', 'background_image', 'background_repeat' ],
			array_keys( get_option( "theme_mods_{$active_theme_slug}" ) )
		);

		// Switch to Reader theme.
		$reader_theme_loader = $this->injector->make( ReaderThemeLoader::class );
		$this->go_to( amp_get_permalink( self::factory()->post->create() ) );
		$reader_theme_loader->override_theme();
		$this->assertTrue( $reader_theme_loader->is_theme_overridden() );
		$this->assertEquals( $reader_theme_slug, get_stylesheet() );

		// Initialize Customizer.
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$wp_customize = $this->get_customize_manager();
		$instance     = AMP_Template_Customizer::init( $wp_customize, $reader_theme_loader );
		add_theme_support( 'custom-background' );
		$wp_customize->register_controls();
		$wp_customize->nav_menus->customize_register();
		$child_setting            = $wp_customize->add_setting( 'family[parent][child]' );
		$background_color_setting = $wp_customize->get_setting( 'background_color' );
		$this->assertInstanceOf( WP_Customize_Setting::class, $background_color_setting );
		$background_image_setting = $wp_customize->get_setting( 'background_image' );
		$this->assertInstanceOf( WP_Customize_Setting::class, $background_image_setting );
		$background_repeat_setting = $wp_customize->get_setting( 'background_repeat' );
		$this->assertInstanceOf( WP_Customize_Setting::class, $background_repeat_setting );
		$nav_menu_location_setting = $wp_customize->get_setting( "nav_menu_locations[{$menu_location}]" );
		$this->assertInstanceOf( WP_Customize_Setting::class, $nav_menu_location_setting );
		$custom_css_setting = $wp_customize->get_setting( "custom_css[{$reader_theme_slug}]" );
		$this->assertInstanceOf( WP_Customize_Custom_CSS_Setting::class, $custom_css_setting );

		// Ensure initially populated settings to import.
		$expected_active_theme_import_settings = [
			$child_setting->id             => $child_value,
			$nav_menu_location_setting->id => $menu_id,
			$background_color_setting->id  => "#{$background_color_value}",
			$custom_css_setting->id        => $custom_css_value,
			$background_image_setting->id  => $background_image_value,
			$background_repeat_setting->id => $background_repeat_value,
		];
		$this->assertEquals(
			$expected_active_theme_import_settings,
			$this->call_private_method( $instance, 'get_active_theme_import_settings' )
		);

		$theme_mod_timestamp_keys = [ $nav_menu_location_setting->id, $background_color_setting->id, 'custom_css', $child_setting->id, $background_repeat_setting->id, $background_image_setting->id ];

		// Update the timestamps in just the active theme, and this should not impact what is exported since the reader theme has no timestamps yet.
		$active_theme_mods = get_option( "theme_mods_{$active_theme_slug}" );
		$active_theme_mods[ AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY ] = array_fill_keys(
			$theme_mod_timestamp_keys,
			time() - 1
		);
		update_option( "theme_mods_{$active_theme_slug}", $active_theme_mods );
		$this->assertEquals(
			$expected_active_theme_import_settings,
			$this->call_private_method( $instance, 'get_active_theme_import_settings' )
		);

		// Set the timestamps for the Reader theme to be before the active theme, which will ensure the Active theme's settings still will export.
		set_theme_mod(
			AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY,
			array_fill_keys(
				$theme_mod_timestamp_keys,
				time() - 2
			)
		);
		$this->assertEquals(
			$expected_active_theme_import_settings,
			$this->call_private_method( $instance, 'get_active_theme_import_settings' )
		);

		// Set the background_image to empty, which should prevent the background_repeat setting from being exported.
		$active_theme_mods['background_image'] = '';
		update_option( "theme_mods_{$active_theme_slug}", $active_theme_mods );
		unset( $expected_active_theme_import_settings['background_repeat'] );
		$expected_active_theme_import_settings['background_image'] = '';
		$this->assertEquals(
			$expected_active_theme_import_settings,
			$this->call_private_method( $instance, 'get_active_theme_import_settings' )
		);

		// Now update the Reader theme's timestamps to be after the Active theme. No settings should now be exported.
		set_theme_mod(
			AMP_Template_Customizer::THEME_MOD_TIMESTAMPS_KEY,
			array_fill_keys(
				$theme_mod_timestamp_keys,
				time() + 1
			)
		);
		$this->assertEquals( [], $this->call_private_method( $instance, 'get_active_theme_import_settings' ) );
	}

	/** @covers AMP_Template_Customizer::render_setting_import_section_template() */
	public function test_render_setting_import_section_template() {
		$instance = AMP_Template_Customizer::init( $this->get_customize_manager() );
		$this->assertStringContainsString(
			'<script type="text/html" id="tmpl-customize-section-amp_active_theme_settings_import">',
			get_echo( [ $instance, 'render_setting_import_section_template' ] )
		);
	}

	/** @covers AMP_Template_Customizer::add_legacy_customizer_scripts() */
	public function test_add_legacy_customizer_scripts() {
		$instance = AMP_Template_Customizer::init( $this->get_customize_manager() );
		$instance->add_legacy_customizer_scripts();

		$this->assertTrue( wp_script_is( 'amp-customize-controls', 'enqueued' ) );
		$this->assertStringContainsString( 'amp-customize-controls-legacy.js', wp_scripts()->registered['amp-customize-controls']->src );
		$this->assertTrue( wp_style_is( 'amp-customizer', 'enqueued' ) );
		$this->assertStringContainsString( 'amp-customizer-legacy.css', wp_styles()->registered['amp-customizer']->src );
		$this->assertEquals( 1, did_action( 'amp_customizer_enqueue_scripts' ) );
	}

	/** @covers AMP_Template_Customizer::enqueue_legacy_preview_scripts() */
	public function test_enqueue_legacy_preview_scripts() {
		$post = self::factory()->post->create( [ 'post_type' => 'post' ] );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertTrue( amp_is_legacy() );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->go_to( amp_get_permalink( $post ) );
		$instance = AMP_Template_Customizer::init( $this->get_customize_manager() );
		$instance->enqueue_legacy_preview_scripts();

		$this->assertTrue( wp_script_is( 'amp-customize-preview', 'enqueued' ) );
		$this->assertStringContainsString( 'amp-customize-preview-legacy.js', wp_scripts()->registered['amp-customize-preview']->src );

		$this->assertTrue( wp_script_is( 'amp-customize-preview', 'enqueued' ) );
	}

	/** @covers AMP_Template_Customizer::add_legacy_customize_preview_styles() */
	public function test_add_legacy_customize_preview_styles() {
		$instance = AMP_Template_Customizer::init( $this->get_customize_manager() );
		$output   = get_echo( [ $instance, 'add_legacy_customize_preview_styles' ] );
		$this->assertStringContainsString( '.screen-reader-text', $output );
	}

	/** @covers AMP_Template_Customizer::add_legacy_preview_scripts() */
	public function test_add_legacy_preview_scripts() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertTrue( amp_is_legacy() );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$instance = AMP_Template_Customizer::init( $this->get_customize_manager() );
		get_echo( [ $instance, 'add_legacy_preview_scripts' ] );
		$this->assertTrue( wp_script_is( 'customize-selective-refresh', 'enqueued' ) );
		$this->assertEquals( 1, did_action( 'amp_customizer_enqueue_preview_scripts' ) );
	}

	/** @covers AMP_Template_Customizer::print_legacy_controls_templates() */
	public function test_print_legacy_controls_templates() {
		$instance = AMP_Template_Customizer::init( $this->get_customize_manager() );
		$output   = get_echo( [ $instance, 'print_legacy_controls_templates' ] );
		$this->assertStringContainsString( '<script type="text/html" id="tmpl-customize-amp-enabled-toggle">', $output );
	}
}
