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
	const PANEL_ID = 'amp_panel';

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

		do_action( 'amp_customizer_init', $self );

		$self->register_settings();
		$self->register_ui();
	}

	/**
	 * Sets up the AMP Customizer preview.
	 */
	public function register_ui() {
		$this->wp_customize->add_panel( self::PANEL_ID, array(
			'type'        => 'amp',
			'title'       => __( 'AMP', 'amp' ),
			'description' => sprintf( __( '<a href="%s" target="_blank">The AMP Project</a> is a Google-led initiative that dramatically improves loading speeds on phones and tablets. You can use the Customizer to preview changes to your AMP template before publishing them.', 'amp' ), 'https://ampproject.org' ),
		) );

		do_action( 'amp_customizer_register_ui', $this->wp_customize );

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'add_customizer_scripts' ) );
		add_action( 'amp_post_template_footer', array( $this, 'add_customizer_preview_script' ) );
	}

	/**
	 * Registers settings for customizing AMP templates.
	 *
	 * @since 0.4
	 * @access public
	 */
	public function register_settings() {
		do_action( 'amp_customizer_register_settings', $this->wp_customize );
	}

	/**
	 * Load up AMP scripts needed for Customizer integrations.
	 *
	 * @since 0.6
	 * @access public
	 */
	public function add_customizer_scripts() {
		wp_enqueue_script(
			'amp-customizer',
			amp_get_asset_url( 'js/amp-customize-controls.js' ),
			array( 'jquery', 'customize-controls' ),
			$version = false,
			$footer  = true
		);

		wp_localize_script( 'amp-customizer', 'ampVars', array(
			'post'  => amp_admin_get_preview_permalink(),
			'query' => AMP_QUERY_VAR,
		) );

		wp_enqueue_style(
			'amp-customizer',
			amp_get_asset_url( 'css/amp-customizer.css' )
		);

		do_action( 'amp_customizer_enqueue_scripts', $this->wp_customize );
	}

	/**
	 * To view AMP templates within the Customizer, we need to ensure the customize-preview.js is loaded.
	 *
	 * @since 0.6
	 * @access public
	 */
	public function add_customizer_preview_script() {
		if ( is_customize_preview() ) {
			global $wp_customize;
			$wp_customize->customize_preview_settings();
			wp_print_scripts( array( 'customize-preview' ) );
		}
	}

	public static function is_amp_customizer() {
		return ! empty( $_REQUEST[ AMP_CUSTOMIZER_QUERY_VAR ] ); // input var ok
	}
}
