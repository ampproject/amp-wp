<?php

namespace AmpProject\AmpWP\Tests;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\ReaderThemeLoader;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\ReaderThemeSupportFeatures;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\AmpWP\Tests\Helpers\LoadsCoreThemes;
use AmpProject\AmpWp\Tests\Helpers\PrivateAccess;

/** @coversDefaultClass \AmpProject\AmpWP\ReaderThemeSupportFeatures */
final class ReaderThemeSupportFeaturesTest extends DependencyInjectedTestCase {

	use AssertContainsCompatibility, LoadsCoreThemes, PrivateAccess;

	const TEST_GRADIENT_PRESETS = [
		[
			'name'     => 'Purple to yellow',
			'gradient' => 'linear-gradient(160deg, #D1D1E4 0%, #EEEADD 100%)',
			'slug'     => 'purple-to-yellow',
		],
		[
			'name'     => 'Yellow to purple',
			'gradient' => 'linear-gradient(160deg, #EEEADD 0%, #D1D1E4 100%)',
			'slug'     => 'yellow-to-purple',
		],
	];

	const TEST_FONT_SIZES = [
		[
			'name'      => 'Extra small',
			'shortName' => 'XS',
			'size'      => 16,
			'slug'      => 'extra-small',
		],
		[
			'name'      => 'Gigantic',
			'shortName' => 'XXXL',
			'size'      => 144,
			'slug'      => 'gigantic',
		],
	];

	const TEST_COLOR_PALETTE = [
		[
			'name'  => 'Black',
			'slug'  => 'black',
			'color' => '#000000',
		],
		[
			'name'  => 'White',
			'slug'  => 'white',
			'color' => '#FFFFFF',
		],
	];

	/**
	 * Primary theme slug.
	 *
	 * @var string
	 */
	const THEME_PRIMARY = 'twentytwenty';

	/**
	 * Reader theme slug.
	 *
	 * @var string
	 */
	const THEME_READER = 'twentynineteen';

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
		remove_all_actions( 'upgrader_process_complete' );
		$this->instance->register();

		$this->assertSame( 10, has_filter( 'amp_options_updating', [ $this->instance, 'filter_amp_options_updating' ] ) );
		$this->assertSame( 10, has_action( 'after_switch_theme', [ $this->instance, 'handle_theme_update' ] ) );
		$this->assertSame( 10, has_action( ReaderThemeSupportFeatures::ACTION_UPDATE_CACHED_PRIMARY_THEME_SUPPORT, [ $this->instance, 'update_cached_theme_support' ] ) );
		$this->assertTrue( has_action( 'upgrader_process_complete' ) );

		$this->assertSame( 10, has_action( 'amp_post_template_head', [ $this->instance, 'print_theme_support_styles' ] ) );
		$this->assertSame( 9, has_action( 'wp_head', [ $this->instance, 'print_theme_support_styles' ] ) );
	}

	/** @covers ::has_required_feature_props() */
	public function test_has_required_feature_props() {
		foreach ( ReaderThemeSupportFeatures::SUPPORTED_FEATURES as $feature => $required_keys ) {
			$this->assertFalse( $this->instance->has_required_feature_props( $feature, [] ) );
			$this->assertFalse( $this->instance->has_required_feature_props( $feature, [ 'foo' => 'bar' ] ) );
			$this->assertFalse( $this->instance->has_required_feature_props( $feature, [ ReaderThemeSupportFeatures::KEY_SLUG => '' ] ) );

			$this->assertTrue( $this->instance->has_required_feature_props( $feature, array_fill_keys( $required_keys, '' ) ) );
			$this->assertTrue( $this->instance->has_required_feature_props( $feature, array_merge( array_fill_keys( $required_keys, '' ), [ 'extra' => '' ] ) ) );
		}
	}

	/** @return array[] */
	public function get_data_for_test_filter_amp_options_updating() {
		$theme_supports = [
			ReaderThemeSupportFeatures::FEATURE_EDITOR_GRADIENT_PRESETS => self::TEST_GRADIENT_PRESETS,
			ReaderThemeSupportFeatures::FEATURE_EDITOR_COLOR_PALETTE    => self::TEST_COLOR_PALETTE,
			ReaderThemeSupportFeatures::FEATURE_EDITOR_FONT_SIZES       => self::TEST_FONT_SIZES,
		];

		return [
			'standard'      => [
				$theme_supports,
				[ Option::THEME_SUPPORT => AMP_Theme_Support::STANDARD_MODE_SLUG ],
				null,
			],
			'transitional'  => [
				$theme_supports,
				[ Option::THEME_SUPPORT => AMP_Theme_Support::TRANSITIONAL_MODE_SLUG ],
				null,
			],
			'reader_legacy' => [
				$theme_supports,
				[
					Option::THEME_SUPPORT => AMP_Theme_Support::READER_MODE_SLUG,
					Option::READER_THEME  => ReaderThemes::DEFAULT_READER_THEME,
				],
				null,
			],
			'reader_theme'  => [
				$theme_supports,
				[
					Option::THEME_SUPPORT => AMP_Theme_Support::READER_MODE_SLUG,
					Option::READER_THEME  => self::THEME_READER,
				],
				$theme_supports,
			],
		];
	}

	/**
	 * @dataProvider get_data_for_test_filter_amp_options_updating
	 * @covers ::filter_amp_options_updating()
	 * @covers ::get_theme_support_features()
	 *
	 * @param array      $theme_supports        Theme supports.
	 * @param array      $initial_options       Initial options.
	 * @param null|array $primary_theme_support Primary theme support.
	 */
	public function test_filter_amp_options_updating( $theme_supports, $initial_options, $primary_theme_support ) {
		if ( ! wp_get_theme( self::THEME_PRIMARY )->exists() || ! wp_get_theme( self::THEME_READER )->exists() ) {
			$this->markTestSkipped();
		}

		switch_theme( self::THEME_PRIMARY );
		foreach ( $theme_supports as $feature => $supports ) {
			add_theme_support( $feature, $supports );
		}

		$filtered = $this->instance->filter_amp_options_updating( $initial_options );
		$this->assertArraySubset( $initial_options, $filtered );
		$this->assertArrayHasKey( Option::PRIMARY_THEME_SUPPORT, $filtered );
		if ( null === $primary_theme_support ) {
			$this->assertNull( $filtered[ Option::PRIMARY_THEME_SUPPORT ] );
		} else {
			$this->assertInternalType( 'array', $filtered[ Option::PRIMARY_THEME_SUPPORT ] );
			foreach ( $theme_supports as $feature => $supports ) {
				$this->assertArrayHasKey( $feature, $filtered[ Option::PRIMARY_THEME_SUPPORT ] );
				$this->assertEquals(
					array_map(
						static function ( $item ) use ( $feature ) {
							return wp_array_slice_assoc( $item, ReaderThemeSupportFeatures::SUPPORTED_FEATURES[ $feature ] );
						},
						$primary_theme_support[ $feature ]
					),
					$filtered[ Option::PRIMARY_THEME_SUPPORT ][ $feature ]
				);
			}
		}
	}

	/** @covers ::handle_theme_update() */
	public function test_handle_theme_update_with_reader_theme_not_enabled() {
		AMP_Options_Manager::update_option( Option::PRIMARY_THEME_SUPPORT, [] );

		/** @var ReaderThemeLoader $reader_theme_loader */
		$reader_theme_loader = $this->get_private_property( $this->instance, 'reader_theme_loader' );
		$this->assertFalse( $reader_theme_loader->is_theme_overridden() );
		$this->assertFalse( $reader_theme_loader->is_enabled() );
		$this->instance->handle_theme_update();
		$this->assertFalse( wp_next_scheduled( ReaderThemeSupportFeatures::ACTION_UPDATE_CACHED_PRIMARY_THEME_SUPPORT ) );
		$this->assertNull( AMP_Options_Manager::get_option( Option::PRIMARY_THEME_SUPPORT ) );
	}

	/** @covers ::handle_theme_update() */
	public function test_handle_theme_update_with_reader_theme_enabled_but_not_overriding() {
		$this->markTestIncomplete();
	}

	/** @covers ::handle_theme_update() */
	public function test_handle_theme_update_with_reader_theme_enabled_and_overriding() {
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
