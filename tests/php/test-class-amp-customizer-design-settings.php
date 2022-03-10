<?php
/**
 * Tests for AMP_Customizer_Design_Settings.
 *
 * @package AMP
 */

use AmpProject\AmpWP\DependencySupport;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Class Test_AMP_Customizer_Design_Settings
 *
 * @coversDefaultClass \AMP_Customizer_Design_Settings
 */
class Test_AMP_Customizer_Design_Settings extends TestCase {

	public static function set_up_before_class() {
		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		parent::set_up_before_class();
	}

	/** @var string */
	private $original_wp_version;

	/**
	 * Setup.
	 *
	 * @inheritDoc
	 */
	public function set_up() {
		parent::set_up();

		global $wp_version;
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

	/** @return array */
	public function get_data_to_test_is_amp_customizer_enabled() {
		return [
			'transitional_mode' => [
				static function () {
					AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
				},
				false,
			],
			'reader_mode'       => [
				static function () {
					AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
				},
				true,
			],
			'filter_disabled'   => [
				static function () {
					AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
					add_filter( 'amp_customizer_is_enabled', '__return_false' );
				},
				false,
			],
		];
	}

	/**
	 * @dataProvider get_data_to_test_is_amp_customizer_enabled
	 * @covers ::is_amp_customizer_enabled()
	 * @covers ::init()
	 */
	public function test_is_amp_customizer_enabled_and_init( callable $set_up, $enabled ) {
		remove_all_actions( 'amp_customizer_init' );
		remove_all_filters( 'amp_customizer_get_settings' );

		$set_up();
		$this->assertEquals( $enabled, AMP_Customizer_Design_Settings::is_amp_customizer_enabled() );
		AMP_Customizer_Design_Settings::init();

		$this->assertEquals( $enabled ? 10 : false, has_action( 'amp_customizer_init', [ AMP_Customizer_Design_Settings::class, 'init_customizer' ] ) );
		$this->assertEquals( $enabled ? 10 : false, has_filter( 'amp_customizer_get_settings', [ AMP_Customizer_Design_Settings::class, 'append_settings' ] ) );

	}

	/** @return array */
	public function get_data_to_test_init_customized() {
		return [
			'has_dependency_support'     => [ true ],
			'not_has_dependency_support' => [ false ],
		];
	}

	/**
	 * @dataProvider get_data_to_test_init_customized
	 * @covers ::init_customizer()
	 */
	public function test_init_customizer( $has_dependency_support ) {
		remove_all_actions( 'amp_customizer_register_settings' );
		remove_all_actions( 'amp_customizer_register_ui' );
		remove_all_actions( 'amp_customizer_enqueue_preview_scripts' );

		if ( $has_dependency_support ) {
			$GLOBALS['wp_version'] = '5.6';
		} else {
			$GLOBALS['wp_version'] = '5.5';
		}
		$has_dependency_support = ( new DependencySupport() )->has_support(); // To account for Gutenberg being active.

		AMP_Customizer_Design_Settings::init_customizer();
		$this->assertEquals( $has_dependency_support ? 10 : false, has_action( 'amp_customizer_register_settings', [ AMP_Customizer_Design_Settings::class, 'register_customizer_settings' ] ) );
		$this->assertEquals( $has_dependency_support ? 10 : false, has_action( 'amp_customizer_register_ui', [ AMP_Customizer_Design_Settings::class, 'register_customizer_ui' ] ) );
		$this->assertEquals( $has_dependency_support ? 10 : false, has_action( 'amp_customizer_enqueue_preview_scripts', [ AMP_Customizer_Design_Settings::class, 'enqueue_customizer_preview_scripts' ] ) );
	}

	/**
	 * Test register_customizer_settings().
	 *
	 * @covers ::register_customizer_settings()
	 */
	public function test_register_customizer_settings() {
		$wp_customize = new WP_Customize_Manager();
		AMP_Customizer_Design_Settings::register_customizer_settings( $wp_customize );
		$setting_ids = array_keys( $wp_customize->settings() );

		$expected_setting_ids = [
			'amp_customizer[header_color]',
			'amp_customizer[header_background_color]',
			'amp_customizer[color_scheme]',
		];

		$this->assertEquals( $expected_setting_ids, $setting_ids );
	}

	/**
	 * Test register_customizer_ui().
	 *
	 * @covers ::register_customizer_ui()
	 */
	public function test_register_customizer_ui() {
		$wp_customize = new WP_Customize_Manager();
		AMP_Customizer_Design_Settings::register_customizer_ui( $wp_customize );

		$section_ids = array_keys( $wp_customize->sections() );
		$this->assertContains( 'amp_design', $section_ids );

		$control_ids          = array_keys( $wp_customize->controls() );
		$expected_control_ids = [
			'amp_header_color',
			'amp_header_background_color',
			'amp_color_scheme',
		];
		$this->assertEquals( $expected_control_ids, $control_ids );

		$partial_ids          = array_keys( $wp_customize->selective_refresh->partials() );
		$expected_partial_ids = [
			'amp-wp-header',
			'amp-wp-footer',
		];
		$this->assertEquals( $expected_partial_ids, $partial_ids );
	}

	/**
	 * Data provider for test_sanitize_color_scheme.
	 * @return array
	 */
	public function get_color_schemes() {
		return [
			[ 'light', 'light' ],
			[ 'dark', 'dark' ],
			[ 'white', 'light' ],
		];
	}

	/**
	 * Test sanitize_color_scheme().
	 *
	 * @covers ::sanitize_color_scheme()
	 * @dataProvider get_color_schemes
	 *
	 * @param string $color_scheme Color scheme.
	 * @param string $expected     Sanitized color scheme.
	 */
	public function test_sanitize_color_scheme( $color_scheme, $expected ) {
		$actual = AMP_Customizer_Design_Settings::sanitize_color_scheme( $color_scheme );
		$this->assertEquals( $expected, $actual );
	}
}
