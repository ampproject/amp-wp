<?php
/**
 * Tests for AMPThemes
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\AMPThemes;
use AmpProject\AmpWP\Tests\TestCase;
use stdClass;

/**
 * Tests for AMPThemes.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\AMPThemes
 */
class AMPThemesTest extends TestCase {

	/**
	 * Instance of AMPThemes
	 *
	 * @var AMPThemes
	 */
	public $instance;

	/**
	 * Flag for AMP-compatible themes file initially exists or not.
	 *
	 * @var bool
	 */
	protected $is_file_exists = false;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		global $wp_scripts, $wp_styles;
		$wp_scripts = null;
		$wp_styles  = null;

		$this->instance = new AMPThemes();
	}

	/**
	 * @covers ::get_themes()
	 */
	public function test_get_themes() {
		$themes = $this->instance->get_themes();

		$expected_themes = include TESTS_PLUGIN_DIR . '/includes/ecosystem-data/themes.php';

		$expected = array_map(
			static function ( $theme ) {
				return AMPThemes::normalize_theme_data( $theme );
			},
			$expected_themes
		);

		$this->assertEquals( $expected, $themes );
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
			AMPThemes::normalize_theme_data( $input )
		);
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {

		set_current_screen( 'index.php' );

		$this->instance->register();

		$this->assertEquals( 10, has_filter( 'themes_api', [ $this->instance, 'themes_api' ] ) );
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
		$this->assertTrue( wp_script_is( AMPThemes::ASSET_HANDLE ) );
		$this->assertTrue( wp_style_is( 'amp-admin' ) );
	}

	/**
	 * @covers ::themes_api()
	 */
	public function test_themes_api() {
		$this->instance->register();
		$response = new stdClass();

		// Test 1: Normal request.
		$response = $this->instance->themes_api( $response, 'query_themes', [ 'per_page' => 36 ] );
		$this->assertEmpty( (array) $response );

		// Test 2: Request for PX compatible data.
		$args = [
			'browse'   => 'amp-compatible',
			'per_page' => 36,
		];

		$response = $this->instance->themes_api( $response, 'query_themes', $args );
		$this->assertIsArray( $response->info );
		$this->assertArrayHasKey( 'page', $response->info );
		$this->assertArrayHasKey( 'pages', $response->info );
		$this->assertArrayHasKey( 'results', $response->info );
		$this->assertIsArray( $response->themes );
	}
}
