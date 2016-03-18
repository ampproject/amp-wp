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

		// Determine whether the AMP editor is called for, and if not, restore core components.
		$self->_maybe_restore_core_components();
		$self->set_up_customizer();
	}

	/**
	 * Determines whether the AMP editor is loaded, and if not, restore core panels.
	 *
	 * The AMP editor is loaded based on the presence of an 'amp' URL variable.
	 *
	 * The 'Menus' and 'Widgets' panels are filtered out via the 'customize_loaded_components'
	 * hook when the plugin is first loaded, see amp_initially_drop_core_panels(). This approach
	 * is necessary because the filter has to be added in a callback hooked on 'plugins_loaded'
	 * to fire in time.
	 *
	 * @since 0.4
	 * @access private
	 */
	private function _maybe_restore_core_components() {
		$amp_editor = ! empty( $_REQUEST['amp'] );

		// If not in the AMP Templates panel, bail and reregister the nav_menus and widgets panels.
		if ( ! $amp_editor ) {
			require_once( ABSPATH . WPINC . '/class-wp-customize-widgets.php' );
			$this->wp_customize->widgets = new WP_Customize_Widgets( $this->wp_customize );

			require_once( ABSPATH . WPINC . '/class-wp-customize-nav-menus.php' );
			$this->wp_customize->nav_menus = new WP_Customize_Nav_Menus( $this->wp_customize );

			// Restore setup for nav menus (normally hooked at priority 11).
			add_action( 'customize_register', array( $this->wp_customize->nav_menus, 'customize_register' ), 501 );

			return;
		}

		// The AMP editor is loaded so remove the other sections and panels.
		$this->_remove_panels_and_sections();
	}

	/**
	 * Removes all non-AMP sections and panels.
	 *
	 * Provides a clean, standalone instance-like experience by removing all non-AMP
	 * registered panels and sections.
	 *
	 * @since 0.4
	 * @access private
	 */
	private function _remove_panels_and_sections() {
		$panels   = $this->wp_customize->panels();
		$sections = $this->wp_customize->sections();

		// Remove all panels except "AMP Templates".
		foreach ( $panels as $panel_id => $object ) {
			if ( 'amp_template_editor' !== $panel_id ) {
				$this->wp_customize->remove_panel( $panel_id );
			}
		}

		// Remove all sections except "AMP Navigation Bar".
		foreach ( $sections as $section_id => $object ) {
			if ( 'amp_navbar_section' !== $section_id ) {
				$this->wp_customize->remove_section( $section_id );
			}
		}
	}

	/**
	 * Sets up the AMP Templates panel and associated Customizer elements and script enqueues.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function set_up_customizer() {
		$this->register_panel();
		$this->register_sections();
		$this->register_settings();
		$this->register_controls();

		// Enqueue scripts.
		add_action( 'customize_preview_init',   array( $this, 'enqueue_scripts' ) );

		// Needed for postMessage purposes.
		add_action( 'amp_post_template_head',   array( $this, 'enqueue_jquery'  ) );
		add_action( 'amp_post_template_footer', array( $this, 'fire_wp_footer'  ) );
	}

	/**
	 * Registers the AMP Template panel.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function register_panel() {
		// AMP Templates.
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
		// Navigation Bar.
		$this->wp_customize->add_section( 'amp_navbar_section', array(
			'title' => __( 'Navigation Bar', 'amp' ),
			'panel' => $this->panel_id,
		) );
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

}
