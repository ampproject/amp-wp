<?php
/**
 * Tests for AMP_Customizer_Design_Settings.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Option;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Class Test_AMP_Customizer_Design_Settings
 *
 * @covers AMP_Customizer_Design_Settings
 */
class Test_AMP_Customizer_Design_Settings extends TestCase {

	public static function setUpBeforeClass() {
		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		return parent::setUpBeforeClass();
	}

	/**
	 * Test is_amp_customizer_enabled().
	 *
	 * @covers AMP_Customizer_Design_Settings::is_amp_customizer_enabled
	 */
	public function test_is_amp_customizer_enabled() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, 'foo' );
		$this->assertEquals( false, AMP_Customizer_Design_Settings::is_amp_customizer_enabled() );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertEquals( true, AMP_Customizer_Design_Settings::is_amp_customizer_enabled() );

		add_filter( 'amp_customizer_is_enabled', '__return_false' );
		$this->assertEquals( false, AMP_Customizer_Design_Settings::is_amp_customizer_enabled() );
	}

	/**
	 * Test register_customizer_settings().
	 *
	 * @covers AMP_Customizer_Design_Settings::register_customizer_settings
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
	 * @covers AMP_Customizer_Design_Settings::register_customizer_ui
	 */
	public function test_register_customizer_ui() {
		$wp_customize = new WP_Customize_Manager();
		AMP_Customizer_Design_Settings::register_customizer_ui( $wp_customize );

		$section_ids = array_keys( $wp_customize->sections() );
		$this->assertStringContainsString( 'amp_design', $section_ids );

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
	 * @covers AMP_Customizer_Design_Settings::sanitize_color_scheme
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
