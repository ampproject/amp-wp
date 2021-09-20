<?php
/**
 * Tests for ReaderThemes.
 *
 * @package AMP
 * @since 2.0
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\ExtraThemeAndPluginHeaders;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\Helpers\LoadsCoreThemes;
use AmpProject\AmpWP\Tests\Helpers\ThemesApiRequestMocking;
use AmpProject\AmpWP\Tests\TestCase;
use Closure;
use WP_Error;

/**
 * Tests for reader themes.
 *
 * @group reader-themes
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\ReaderThemes
 */
class ReaderThemesTest extends TestCase {

	use ThemesApiRequestMocking, LoadsCoreThemes;

	/**
	 * Test instance.
	 *
	 * @var ReaderThemes
	 */
	private $reader_themes;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function set_up() {
		parent::set_up();

		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Requires WordPress 5.0.' );
		}

		delete_transient( 'amp_themes_wporg' );
		$this->add_reader_themes_request_filter();

		switch_theme( 'twentytwenty' );
		$this->reader_themes = new ReaderThemes();

		$this->register_core_themes();
	}

	public function tearDown() {
		parent::tearDown();

		$this->restore_theme_directories();
	}

	/**
	 * Test for get_themes.
	 *
	 * @covers ::get_themes
	 * @covers ::get_default_reader_themes
	 * @covers ::get_legacy_theme
	 */
	public function test_get_themes() {
		register_theme_directory( __DIR__ . '/../../data/themes' );
		delete_site_transient( 'theme_roots' );

		$extra_theme_and_plugin_headers = new ExtraThemeAndPluginHeaders();
		$extra_theme_and_plugin_headers->register();

		$themes = $this->reader_themes->get_themes();
		$this->assertEquals( 'legacy', end( $themes )['slug'] );

		$keys = [
			'name',
			'slug',
			'preview_url',
			'screenshot_url',
			'homepage',
			'description',
			'requires',
			'requires_php',
			'availability',
		];
		foreach ( $themes as $theme ) {
			$this->assertEqualSets( $keys, array_keys( $theme ) );
		}

		AMP_Options_Manager::update_option( Option::READER_THEME, 'child-of-core' );

		$themes = ( new ReaderThemes() )->get_themes();

		$available_theme_slugs = wp_list_pluck( $themes, 'slug' );
		$this->assertStringContainsString( 'child-of-core', $available_theme_slugs );
		$this->assertStringNotContainsString( 'custom', $available_theme_slugs );
		$this->assertStringNotContainsString( 'with-legacy', $available_theme_slugs );
	}

	/**
	 * Test that themes API success does not result in a WP_Error.
	 *
	 * @covers ::get_themes
	 * @covers ::get_default_reader_themes
	 */
	public function test_themes_api_success() {
		$this->reader_themes->get_themes();

		$this->assertNull( $this->reader_themes->get_themes_api_error() );
	}

	/**
	 * Test that a themes API failure results in a WP_Error.
	 *
	 * @covers ::get_themes
	 * @covers ::get_default_reader_themes
	 */
	public function test_themes_api_failure() {
		add_filter( 'themes_api_result', '__return_null' );

		$this->reader_themes->get_themes();

		$error = $this->reader_themes->get_themes_api_error();
		$this->assertWPError( $error );
		$this->assertEquals(
			'The request for reader themes from WordPress.org resulted in an invalid response. Check your Site Health to confirm that your site can communicate with WordPress.org. Otherwise, please try again later or contact your host.',
			$error->get_error_message()
		);

		remove_filter( 'themes_api_result', '__return_null' );
	}

	/**
	 * Test that a themes API response with an empty themes array results in a WP_Error.
	 *
	 * @covers ::get_themes
	 * @covers ::get_default_reader_themes
	 */
	public function test_themes_api_empty_array() {
		$filter_cb = static function() {
			return (object) [ 'themes' => [] ];
		};
		add_filter( 'themes_api_result', $filter_cb );

		$this->reader_themes->get_themes();

		$error = $this->reader_themes->get_themes_api_error();
		$this->assertWPError( $this->reader_themes->get_themes_api_error() );
		$this->assertEquals(
			'The default reader themes cannot be displayed because a plugin appears to be overriding the themes response from WordPress.org.',
			$error->get_error_message()
		);
	}

	/**
	 * Test that an error is stored in state when themes_api returns an error.
	 *
	 * @covers ::get_themes
	 * @covers ::get_default_reader_themes
	 */
	public function test_themes_api_wp_error() {
		$filter_cb = static function() {
			return new WP_Error(
				'amp_test_error',
				'Test message'
			);
		};
		add_filter( 'themes_api_result', $filter_cb );

		$this->reader_themes->get_themes();

		$error = $this->reader_themes->get_themes_api_error();
		$this->assertWPError( $this->reader_themes->get_themes_api_error() );
		$this->assertStringStartsWith(
			'The request for reader themes from WordPress.org resulted in an invalid response. Check your Site Health to confirm that your site can communicate with WordPress.org. Otherwise, please try again later or contact your host.',
			$error->get_error_message()
		);
		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$this->assertStringContainsString( 'Test message', $error->get_error_message() );
			$this->assertStringContainsString( 'amp_test_error', $error->get_error_message() );
		}
	}

	/**
	 * Test for get_reader_theme_by_slug.
	 *
	 * @covers ::get_reader_theme_by_slug
	 */
	public function test_get_reader_theme_by_slug() {
		$this->assertFalse( $this->reader_themes->get_reader_theme_by_slug( 'some-theme' ) );
		$this->assertArrayHasKey( 'slug', $this->reader_themes->get_reader_theme_by_slug( 'legacy' ) );
	}

	/**
	 * Provides test themes to test availability.
	 *
	 * @return array
	 */
	public function get_availability_test_themes() {
		return [
			'from_wp_future'                         => [
				static function () {
					return ReaderThemes::STATUS_NON_INSTALLABLE;
				},
				false,
				[
					'name'         => 'Some Theme',
					'requires'     => '99.9',
					'requires_php' => '5.2',
					'slug'         => 'from_wp_future',
				],
			],
			'from_php_future'                        => [
				static function () {
					return ReaderThemes::STATUS_NON_INSTALLABLE;
				},
				false,
				[
					'name'         => 'Some Theme',
					'requires'     => '4.9',
					'requires_php' => '99.9',
					'slug'         => 'from_php_future',
				],
			],
			'non_reader_theme'                       => [
				static function () {
					return wp_get_theme( 'neve' )->exists() ? ReaderThemes::STATUS_INSTALLED : ReaderThemes::STATUS_INSTALLABLE;
				},
				true,
				[
					'name'         => 'Neve',
					'requires'     => false,
					'requires_php' => '5.2',
					'slug'         => 'neve',
				],
			],
			'twentytwelve_not_requiring_wp_version'  => [
				static function () {
					return wp_get_theme( 'twentytwelve' )->exists() ? ReaderThemes::STATUS_INSTALLED : ReaderThemes::STATUS_NON_INSTALLABLE;
				},
				true,
				[
					'name'         => 'Some Theme',
					'requires'     => false,
					'requires_php' => '5.2',
					'slug'         => 'twentytwelve',
				],
			],
			'twentytwelve_not_requiring_php_version' => [
				static function () {
					return wp_get_theme( 'twentysixteen' )->exists() ? ReaderThemes::STATUS_INSTALLED : ReaderThemes::STATUS_NON_INSTALLABLE;
				},
				true,
				[
					'name'         => 'Some Theme',
					'requires'     => '4.9',
					'requires_php' => false,
					'slug'         => 'twentysixteen',
				],
			],
			'twentytwenty_active'                    => [
				static function () {
					return ReaderThemes::STATUS_ACTIVE;
				},
				true,
				[
					'name'         => 'WordPress Default',
					'requires'     => '4.4',
					'requires_php' => '5.2',
					'slug'         => 'twentytwenty',
				],
			],
		];
	}

	/**
	 * Test for get_theme_availability.
	 *
	 * @covers ::get_theme_availability
	 * @covers ::can_install_theme
	 *
	 * @dataProvider get_availability_test_themes
	 *
	 * @param Closure $get_expected Expected.
	 * @param bool    $can_install  Can install.
	 * @param array   $theme        Theme.
	 */
	public function test_get_theme_availability( $get_expected, $can_install, $theme ) {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$expected = $get_expected();
		$this->assertEquals( $expected, $this->reader_themes->get_theme_availability( $theme ) );
		$this->assertEquals( $can_install, $this->reader_themes->can_install_theme( $theme ) );
	}

	/**
	 * Tests for can_install_theme.
	 *
	 * @covers ::can_install_theme
	 */
	public function test_can_install_theme() {
		$core_theme = [
			'name'         => 'Twenty Twelve',
			'requires'     => false,
			'requires_php' => '5.2',
			'slug'         => 'twentytwelve',
		];

		$neve_theme = [
			'name'         => 'Neve',
			'requires'     => false,
			'requires_php' => '5.2',
			'slug'         => 'neve',
		];

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );
		$this->assertFalse( $this->reader_themes->can_install_theme( $core_theme ) );
		$this->assertFalse( $this->reader_themes->can_install_theme( $neve_theme ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertTrue( $this->reader_themes->can_install_theme( $core_theme ) );
		$this->assertTrue( $this->reader_themes->can_install_theme( $neve_theme ) );

		$core_theme['requires'] = '999.9';
		$this->assertFalse( $this->reader_themes->can_install_theme( $core_theme ) );

		$core_theme['requires']     = false;
		$core_theme['requires_php'] = '999.9';
		$this->assertFalse( $this->reader_themes->can_install_theme( $core_theme ) );
	}

	/**
	 * Tests for theme_data_exists.
	 *
	 * @covers ::theme_data_exists
	 */
	public function test_theme_data_exists() {
		if ( ( new ReaderThemes() )->theme_data_exists( 'neve' ) ) {
			$this->markTestSkipped( 'Neve is already installed.' );
		}

		$neve_theme        = [
			'name'         => 'Neve',
			'requires'     => false,
			'requires_php' => '5.2',
			'slug'         => 'neve',
		];
		$append_neve_theme = static function ( $themes ) use ( $neve_theme ) {
			$themes[] = $neve_theme;
			return $themes;
		};

		add_filter( 'amp_reader_themes', $append_neve_theme );

		$this->assertTrue( ( new ReaderThemes() )->theme_data_exists( 'neve' ) );

		remove_filter( 'amp_reader_themes', $append_neve_theme );
	}

	/** @covers ::using_fallback_theme */
	public function test_using_fallback_theme() {
		$reader_themes = new ReaderThemes();
		AMP_Options_Manager::update_options(
			[
				Option::THEME_SUPPORT => 'reader',
				Option::READER_THEME  => ReaderThemes::DEFAULT_READER_THEME,
			]
		);
		$this->assertFalse( $reader_themes->using_fallback_theme() );

		AMP_Options_Manager::update_option( Option::READER_THEME, 'foobar' );
		$this->assertTrue( $reader_themes->using_fallback_theme() );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->assertFalse( $reader_themes->using_fallback_theme() );
	}
}
