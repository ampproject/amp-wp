<?php
/**
 * Tests for AmpThemes
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\AmpThemes;
use AmpProject\AmpWP\Tests\TestCase;
use stdClass;

/**
 * Tests for AmpThemes.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\AmpThemes
 */
class AmpThemesTest extends TestCase {

	/**
	 * Instance of AmpThemes
	 *
	 * @var AmpThemes
	 */
	public $instance;

	/** @var string */
	private $original_wp_version;

	/**
	 * Setup.
	 *
	 * @inheritDoc
	 */
	public function set_up() {

		parent::set_up();

		global $wp_scripts, $wp_styles, $wp_version;
		$wp_scripts = null;
		$wp_styles  = null;

		$this->instance = new AmpThemes();

		$this->original_wp_version = $wp_version;
	}

	/**
	 * Tear down.
	 *
	 * @inheritDoc
	 */
	public function tear_down() {
		parent::tear_down();

		global $wp_version;
		$wp_version = $this->original_wp_version;
	}

	/**
	 * @covers ::get_registration_action()
	 */
	public function test_get_registration_action() {

		$this->assertEquals( 'admin_init', AmpThemes::get_registration_action() );
	}

	/**
	 * @covers ::is_needed()
	 */
	public function test_is_needed() {
		global $wp_version;

		set_current_screen( 'front' );

		// Test 1: Not admin request.
		$this->assertFalse( is_admin() );
		$this->assertFalse( AmpThemes::is_needed() );

		// Test 2: Check with older version of WordPress.
		$wp_version = '4.9';
		$this->assertFalse( AmpThemes::is_needed() );

		// Test 3: Admin request.
		$wp_version = '5.6';
		set_current_screen( 'index.php' );
		$this->assertTrue( is_admin() );
		$this->assertTrue( AmpThemes::is_needed() );

		// Test 4: Filter disables.
		add_filter(
			'amp_compatible_ecosystem_shown',
			static function ( $shown, $type ) {
				if ( 'themes' === $type ) {
					$shown = false;
				}
				return $shown;
			},
			10,
			2
		);
		$this->assertFalse( AmpThemes::is_needed() );

		set_current_screen( 'front' );
	}

	/**
	 * @covers ::get_themes()
	 */
	public function test_get_themes() {
		$themes = $this->instance->get_themes();

		$expected_themes = include TESTS_PLUGIN_DIR . '/includes/ecosystem-data/themes.php';

		$expected = array_map(
			static function ( $theme ) {
				return AmpThemes::normalize_theme_data( $theme );
			},
			$expected_themes
		);

		$this->assertEqualSets( $expected, $themes );
	}

	/**
	 * @covers ::normalize_theme_data()
	 */
	public function test_normalize_theme_data() {

		$input = [
			'name'   => 'sample theme',
			'author' => [
				'user_nicename' => 'author_nicename',
			],
		];

		$expected = [
			'name'           => 'sample theme',
			'slug'           => '',
			'version'        => '',
			'preview_url'    => '',
			'author'         => [
				'user_nicename' => 'author_nicename',
				'profile'       => '',
				'avatar'        => '',
				'display_name'  => '',
				'author'        => '',
				'author_url'    => '',
			],
			'screenshot_url' => '',
			'rating'         => 0,
			'num_ratings'    => 0,
			'homepage'       => '',
			'description'    => '',
			'requires'       => '',
			'requires_php'   => '',
		];

		$this->assertEquals(
			$expected,
			AmpThemes::normalize_theme_data( $input )
		);
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {

		set_current_screen( 'index.php' );

		$this->instance->register();

		$this->assertEquals( 10, has_filter( 'themes_api', [ $this->instance, 'filter_themes_api' ] ) );
		$this->assertEquals( 10, has_filter( 'theme_row_meta', [ $this->instance, 'filter_theme_row_meta' ] ) );
		$this->assertEquals( 10, has_action( 'current_screen', [ $this->instance, 'register_hooks' ] ) );

		set_current_screen( 'front' );
	}

	/**
	 * @covers ::register_hooks()
	 */
	public function test_register_hooks() {

		set_current_screen( 'themes' );

		$this->instance->register_hooks();
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_scripts' ] ) );
	}

	/**
	 * @covers ::enqueue_scripts()
	 */
	public function test_enqueue_scripts() {
		$this->instance->enqueue_scripts();
		$this->assertTrue( wp_script_is( AmpThemes::ASSET_HANDLE ) );
		$this->assertTrue( wp_style_is( 'amp-admin' ) );
	}

	/**
	 * @covers ::filter_themes_api()
	 */
	public function test_filter_themes_api() {
		$this->instance->register();
		$response = new stdClass();

		// Test 1: Normal request.
		$response = $this->instance->filter_themes_api( $response, 'query_themes', [ 'per_page' => 36 ] );
		$this->assertEmpty( (array) $response );

		// Test 2: Request for AMP-compatible data.
		$args = [
			'browse'   => 'amp-compatible',
			'per_page' => 36,
		];

		$response = $this->instance->filter_themes_api( $response, 'query_themes', $args );
		$this->assertIsArray( $response->info );
		$this->assertArrayHasKey( 'page', $response->info );
		$this->assertArrayHasKey( 'pages', $response->info );
		$this->assertArrayHasKey( 'results', $response->info );
		$this->assertIsArray( $response->themes );
	}

	/**
	 * @covers ::filter_theme_row_meta()
	 */
	public function test_filter_theme_row_meta() {

		$this->assertEmpty( $this->instance->filter_theme_row_meta( [], 'non-amp' ) );

		$this->assertEquals(
			[
				'AMP Compatible',
			],
			$this->instance->filter_theme_row_meta( [], 'astra' )
		);
	}
}
