<?php
/**
 * AMP class that implements a template style editor in the Customizer.
 *
 * A direct, formed link to the AMP editor in the Customizer is added via
 * {@see amp_customizer_editor_link()} as a submenu to the Appearance menu.
 *
 * @since 0.4
 */
class AMP_Template_Customizer {

	/**
	 * Customizer instance.
	 *
	 * @since 0.4
	 * @access protected
	 * @var WP_Customize_Manager $wp_customize
	 */
	protected $wp_customize;

	/**
	 * AMP template editor panel ID.
	 *
	 * @since 0.4
	 * @access protected
	 * @var string
	 */
	protected $panel_id = 'amp_template_editor';

	/**
	 * Initialize the template Customizer feature class.
	 *
	 * @static
	 * @since 0.4
	 * @access public
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	public static function init( $wp_customize ) {
		$self = new self();

		$self->wp_customize = $wp_customize;

		// Settings need to be registered for regular customize requests as well (since save is handled there)
		$self->register_settings();

		// Our custom panels only need to go for AMP Customizer requests though
		if ( self::is_amp_customizer() ) {
			$self->_unregister_core_ui();
			$self->register_ui();
		}
	}

	/**
	 * Filters the core components to unhook the nav_menus and widgets panels.
	 *
	 * @since 0.4
	 * @access private
	 *
	 * @return array Array of core Customizer components to keep active.
	 */
	public static function _unregister_core_panels( $panels ) {
		if ( self::is_amp_customizer() ) {
			$panels = array();
		}
		return $panels;
	}

	/**
	 * Removes all non-AMP sections and panels.
	 *
	 * Provides a clean, standalone instance-like experience by removing all non-AMP registered panels and sections.
	 *
	 * @since 0.4
	 * @access private
	 */
	private function _unregister_core_ui() {
		$panels   = $this->wp_customize->panels();
		$sections = $this->wp_customize->sections();

		foreach ( $panels as $panel_id => $object ) {
			$this->wp_customize->remove_panel( $panel_id );
		}

		foreach ( $sections as $section_id => $object ) {
			$this->wp_customize->remove_section( $section_id );
		}
	}

	/**
	 * Sets up the AMP Templates panel and associated Customizer elements and script enqueues.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function register_ui() {
		$this->register_panel();
		$this->register_sections();
		$this->register_controls();

		add_action( 'customize_preview_init',   array( $this, 'enqueue_scripts' ) );

		// Needed for postMessage purposes.
		add_action( 'amp_post_template_head',   array( $this, 'enqueue_jquery'  ) );
		add_action( 'amp_post_template_footer', array( $this, 'fire_wp_footer'  ) );

		do_action( 'amp_customizer_register_ui', $this->wp_customize );
	}

	/**
	 * Registers settings for customizing AMP templates.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function register_settings() {
		// Navbar text color setting.
		$this->wp_customize->add_setting( 'amp_navbar_color', array(
			'default'           => '#ffffff',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage'
		) );

		// Navbar background color setting.
		$this->wp_customize->add_setting( 'amp_navbar_background', array(
			'default'           => '#0a89c0',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage'
		) );

		do_action( 'amp_customizer_register_settings', $this->wp_customize );
	}

	/**
	 * Registers the AMP Template panel.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function register_panel() {
		$this->wp_customize->add_panel( $this->panel_id, array(
			'type'            => 'amp',
			'title'           => __( 'AMP', 'amp' ),
			'active_callback' => 'is_amp_endpoint'
		) );
	}

	/**
	 * Registers the AMP Template panel sections.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function register_sections() {
		$this->wp_customize->add_section( 'amp_navbar_section', array(
			'title' => __( 'Navigation Bar', 'amp' ),
			'panel' => $this->panel_id,
		) );
	}

	/**
	 * Registers controls for customizing AMP templates.
	 *
	 * @since 0.4
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

		// Navbar site icon.
		$this->wp_customize->add_control( new WP_Customize_Site_Icon_Control( $this->wp_customize, 'site_icon_amp', array(
			'label'       => __( 'Site Icon' ),
			'settings'    => 'site_icon',
			'description' => sprintf(
			/* translators: %s: site icon size in pixels */
				__( 'The Site Icon is used as a browser and app icon for your site. Icons must be square, and at least %s pixels wide and tall.' ),
				'<strong>512</strong>'
			),
			'section'     => 'amp_navbar_section',
			'priority'    => 30,
			'height'      => 512,
			'width'       => 512,
		) ) );

	}

	/**
	 * Enqueues jQuery inside the AMP template header preview for postMessage purposes.
	 *
	 * This breaks AMP validation in the customizer but necessary for the live preview.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function enqueue_jquery() {
		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Fires the 'wp_footer' action in the AMP template footer preview for postMessage purposes.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function fire_wp_footer() {
		/** This action is documented in wp-includes/general-template.php */
		do_action( 'wp_footer' );
	}

	/**
	 * Enqueues scripts used in the Customizer preview.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function enqueue_scripts() {
		if ( is_customize_preview() ) {
			wp_enqueue_script(
				'amp-customizer',
				amp_get_asset_url( 'js/amp-customizer-preview.js' ),
				array( 'customize-preview', 'wp-util' ),
				$version = false,
				$footer = true
			);
		}
	}

	private static function is_amp_customizer() {
		return ! empty( $_REQUEST['amp'] );
	}
}
