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
	 * AMP template editor panel ID.
	 *
	 * @since 0.4
	 * @var string
	 */
	const PANEL_ID = 'amp_template_editor';

	/**
	 * Customizer instance.
	 *
	 * @since 0.4
	 * @access protected
	 * @var WP_Customize_Manager $wp_customize
	 */
	protected $wp_customize;

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
			$self->init_ui();
		} elseif ( is_customize_preview() ) {
			// Delay preview-specific actions until we're sure we're rendering an AMP page, since it's too early for `is_amp_endpoint()` here.
			add_action( 'pre_amp_render_post', array( $self, 'init_preview' ) );
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
	 * Sets up the AMP Customizer preview.
	 */
	public function init_preview() {
		// Preview needs controls registered too for postMessage communication.
		$this->init_ui();

		add_action( 'amp_post_template_footer', array( $this, 'add_preview_scripts'  ) );
	}

	/**
	 * Sets up the AMP Templates panel and associated Customizer elements and script enqueues.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function init_ui() {
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'add_customizer_scripts' ) );
		add_filter( 'customize_previewable_devices', array( $this, 'force_mobile_preview' ) );

		$this->register_panel();
		$this->register_sections();
		$this->register_controls();

		do_action( 'amp_customizer_register_ui', $this->wp_customize );
	}

	/**
	 * Registers settings for customizing AMP templates.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function register_settings() {

		// Header text color setting
		$this->wp_customize->add_setting( 'amp_customizer[header_color]', array(
			'type'              => 'option',
			'default'           => '#ffffff',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage'
		) );

		// Header background color
		$this->wp_customize->add_setting( 'amp_customizer[header_background_color]', array(
			'type'              => 'option',
			'default'           => '#0a89c0',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage'
		) );

		// Background color scheme
		$this->wp_customize->add_setting( 'amp_customizer[background_color]', array(
			'type'              => 'option',
			'default'           => 'light',
			'sanitize_callback' => 'amp_sanitize_color_scheme',
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
		$this->wp_customize->add_panel( self::PANEL_ID, array(
			'type'  => 'amp',
			'title' => __( 'AMP', 'amp' ),
		) );
	}

	/**
	 * Registers the AMP Template panel sections.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function register_sections() {
		$this->wp_customize->add_section( 'amp_header_section', array(
			'title' => __( 'Color Options', 'amp' ),
			'panel' => self::PANEL_ID,
		) );
	}

	/**
	 * Registers controls for customizing AMP templates.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function register_controls() {
		// Header text color control.
		$this->wp_customize->add_control(
			new WP_Customize_Color_Control( $this->wp_customize, 'amp_header_color', array(
				'settings'   => 'amp_customizer[header_color]',
				'label'    => __( 'Header Text Color', 'amp' ),
				'section'  => 'amp_header_section',
				'priority' => 10
			) )
		);

		// Header background color control.
		$this->wp_customize->add_control(
			new WP_Customize_Color_Control( $this->wp_customize, 'amp_header_background_color', array(
				'settings'   => 'amp_customizer[header_background_color]',
				'label'    => __( 'Header Background Color & Link Color', 'amp' ),
				'section'  => 'amp_header_section',
				'priority' => 20
			) )
		);

		// Background color scheme
		$this->wp_customize->add_control( 'amp_background_color', array(
			'settings'   => 'amp_customizer[background_color]',
			'label'      => __( 'Background Color Scheme', 'amp' ),
			'section'    => 'amp_header_section',
			'type'       => 'radio',
			'priority'   => 30,
			'choices'    => array(
				'light'   => __( 'Light', 'amp'),
				'dark'    => __( 'Dark', 'amp' ),
			),
		));
	}

	public function add_customizer_scripts() {
		wp_enqueue_script( 'wp-util' ); // fix `wp.template is not a function`
	}

	/**
	 * Enqueues scripts and fires the 'wp_footer' action so we can output customizer scripts.
	 *
	 * This breaks AMP validation in the customizer but is necessary for the live preview.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function add_preview_scripts() {
		wp_enqueue_script(
			'amp-customizer',
			amp_get_asset_url( 'js/amp-customizer-preview.js' ),
			array( 'jquery', 'customize-preview', 'wp-util' ),
			$version = false,
			$footer = true
		);

		/** This action is documented in wp-includes/general-template.php */
		do_action( 'wp_footer' );
	}

	public function force_mobile_preview( $devices ) {
		if ( isset( $devices[ 'mobile' ] ) ) {
			$devices['mobile']['default'] = true;
			unset( $devices['desktop']['default'] );
		}

		return $devices;
	}

	public static function is_amp_customizer() {
		return ! empty( $_REQUEST[ AMP_CUSTOMIZER_QUERY_VAR ] );
	}
}

function amp_sanitize_color_scheme( $value ) {
	if ( ! in_array( $value, array( 'light', 'dark' ) ) ) {
		$value = 'light';
	}

	return $value;
}
