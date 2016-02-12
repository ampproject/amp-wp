<?php
/**
 * AMP class that implements a template style editor in the Customizer.
 */
class AMP_Template_Customizer {

	/**
	 * Customizer instance.
	 *
	 * @access public
	 * @var WP_Customize_Manager $wp_customizer
	 */
	public $wp_customizer;

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	public function __construct( $wp_customize ) {
		$this->wp_customize = $wp_customize;

		$this->register_panel();
		$this->register_sections();
		$this->register_settings();
		$this->register_controls();
		$this->enqueue_scripts();
	}

	/**
	 * Registers the AMP Template panel.
	 *
	 * @access public
	 */
	public function register_panel() {
		// AMP Templates.
		$this->wp_customize->add_panel( 'amp_template_editor', array(
			'type'   => 'amp',
			'title' => __( 'AMP Templates', 'amp' )
		) );
	}

	/**
	 * Registers the AMP Template panel sections.
	 *
	 * @access public
	 */
	public function register_sections() {

		// Navigation Bar.
		$this->wp_customize->add_section( 'amp_navbar_section', array(
			'title' => __( 'AMP Navigation Bar', 'amp' ),
			'panel' => 'amp_template_editor',
		) );
	}

	/**
	 * Registers settings for customizing AMP templates.
	 *
	 * @access public
	 */
	public function register_settings() {
		// Background Color setting (Navbar).
		$this->wp_customize->add_setting( 'amp_navbar_background', array(
			'default'           => '#0a89c0',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage'
		) );
	}

	/**
	 * Registers controls for customizing AMP templates.
	 *
	 * @access public
	 */
	public function register_controls() {
		// Background Color control (Navbar).
		$this->wp_customize->add_control( new WP_Customize_Color_Control( $this->wp_customize, 'amp_navbar_background', array(
			'label'       => __( 'Background Color', 'amp' ),
			'description' => __( 'This color is used by AMP to format the background of the navigation bar on pages generated for AMP.', 'amp' ),
			'section'     => 'amp_navbar_section'
		) ) );
	}

	/**
	 * Enqueues scripts used in the Customizer.
	 *
	 * @access public
	 */
	public function enqueue_scripts() {
		if ( is_customize_preview() ) {
			wp_enqueue_script( 'amp-customizer', AMP__URL__ . '/assets/js/amp-customizer.js', array( 'customize-preview' ) );
		}
	}
}
