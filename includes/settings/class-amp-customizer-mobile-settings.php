<?php
/**
 * Class AMP_Customizer_Mobile_Settings
 *
 * @package AMP
 */

/**
 * Class AMP_Customizer_Mobile_Settings
 */
class AMP_Customizer_Mobile_Settings {

	/**
	 * Init.
	 */
	public static function init() {
		add_action( 'amp_customizer_init', [ __CLASS__, 'init_customizer' ] );
		add_filter( 'amp_customizer_get_settings', [ __CLASS__, 'append_settings' ] );
	}

	/**
	 * Init customizer.
	 */
	public static function init_customizer() {
		add_action( 'amp_customizer_register_settings', [ __CLASS__, 'register_customizer_settings' ] );
		add_action( 'amp_customizer_register_ui', [ __CLASS__, 'register_customizer_ui' ] );
	}

	/**
	 * Merge default AMP Customizer settings.
	 *
	 * @see AMP_Post_Template::build_customizer_settings()
	 *
	 * @param array $settings Settings.
	 * @return array Merged settings.
	 */
	public static function append_settings( $settings ) {
		$settings = wp_parse_args(
			$settings,
			[
				'display_exit_link' => false,
			]
		);

		return $settings;
	}

	/**
	 * Register default Customizer settings for mobile.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	public static function register_customizer_settings( WP_Customize_Manager $wp_customize ) {
		// Display exit link.
		$wp_customize->add_setting(
			'amp_customizer[display_exit_link]',
			[
				'type'              => 'option',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'transport'         => 'postMessage',
			]
		);
	}

	/**
	 * Register default Customizer sections and controls for AMP.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	public static function register_customizer_ui( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section(
			'amp_mobile',
			[
				'title' => __( 'Mobile', 'amp' ),
				'panel' => AMP_Customizer::PANEL_ID,
			]
		);

		// Display exit link.
		$wp_customize->add_control(
			'amp_display_exit_link',
			[
				'settings' => 'amp_customizer[display_exit_link]',
				'label'    => __( 'Display link to exit reader mode?', 'amp' ),
				'section'  => 'amp_mobile',
				'type'     => 'checkbox',
			]
		);
	}
}
