<?php
/**
 * AMP class that implements a template style editor in the Customizer.
 */
class AMP_Template_Customizer {

	/**
	 * Customizer instance.
	 *
	 * @access public
	 * @var WP_Customize_Manager $wp_customize
	 */
	public $wp_customize;

	/**
	 * Initialize the template Customizer feature class.
	 *
	 * @access public
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	public function init( $wp_customize ) {
		$self = new self();

		$self->wp_customize = $wp_customize;

		// Set up panel, sections, settings, controls.
		$self->register_panel();
		$self->register_sections();
		$self->register_settings();
		$self->register_controls();

		// Enqueue scripts.
		if ( is_customize_preview() ) {
			add_action( 'customize_preview_init',   array( $self, 'enqueue_scripts' ) );
			add_action( 'amp_post_template_head',   array( $self, 'enqueue_jquery'  ) );
			add_action( 'amp_post_template_footer', array( $self, 'fire_wp_footer'  ) );
		}
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
		// Navbar text color setting.
		$this->wp_customize->add_setting( 'amp_navbar_color', array(
			'default'           => '#fff',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage'
		) );

		// Navbar background color setting.
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
		// Navbar text color control.
		$this->wp_customize->add_control(
			new WP_Customize_Color_Control( $this->wp_customize, 'amp_navbar_color', array(
				'label'    => __( 'Header Text Color', 'amp' ),
				'section'  => 'amp_navbar_section',
				'priority' => 10
			) )
		);

		// Navbar background color control.
		$this->wp_customize->add_control(
			new WP_Customize_Color_Control( $this->wp_customize, 'amp_navbar_background', array(
				'label'    => __( 'Header Background Color', 'amp' ),
				'section'  => 'amp_navbar_section',
				'priority' => 20
			) )
		);
	}

	/**
	 * Enqueues jQuery inside the AMP template header preview for postMessage purposes.
	 *
	 * @access public
	 */
	public function enqueue_jquery() {
		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Fires the 'wp_footer' action in the AMP template footer preview for postMessage purposes.
	 *
	 * @access public
	 */
	public function fire_wp_footer() {
		/** This action is documented in wp-includes/general-template.php */
		do_action( 'wp_footer' );
	}

	/**
	 * Enqueues scripts used in the Customizer.
	 *
	 * @access public
	 */
	public function enqueue_scripts() {
		if ( is_customize_preview() ) {
			wp_enqueue_script(
				'amp-customizer',
				AMP__URL__ . '/assets/js/amp-customizer.js',
				array( 'customize-preview' ),
				$version = false,
				$footer = true
			);
		}
	}
}
