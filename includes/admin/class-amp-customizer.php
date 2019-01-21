<?php
/**
 * Class AMP_Template_Customizer
 *
 * @package AMP
 */

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

		/**
		 * Fires when the AMP Template Customizer initializes.
		 *
		 * In practice the `customize_register` hook should be used instead.
		 *
		 * @since 0.4
		 * @param AMP_Template_Customizer $self Instance.
		 */
		do_action( 'amp_customizer_init', $self );

		$self->register_settings();
		$self->register_ui();

		add_action( 'customize_controls_enqueue_scripts', array( $self, 'add_customizer_scripts' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $self, 'print_controls_templates' ) );
		add_action( 'customize_preview_init', array( $self, 'init_preview' ) );
	}

	/**
	 * Init Customizer preview.
	 *
	 * @since 0.4
	 * @global WP_Customize_Manager $wp_customize
	 */
	public function init_preview() {
		add_action( 'amp_post_template_head', 'wp_no_robots' );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_preview_scripts' ) );
		add_action( 'amp_customizer_enqueue_preview_scripts', array( $this, 'enqueue_preview_scripts' ) );

		// Output scripts and styles which will break AMP validation only when preview is opened with controls for manipulation.
		if ( $this->wp_customize->get_messenger_channel() ) {
			add_action( 'amp_post_template_head', array( $this->wp_customize, 'customize_preview_loading_style' ) );
			add_action( 'amp_post_template_css', array( $this, 'add_customize_preview_styles' ) );
			add_action( 'amp_post_template_head', array( $this->wp_customize, 'remove_frameless_preview_messenger_channel' ) );
			add_action( 'amp_post_template_footer', array( $this, 'add_preview_scripts' ) );
		}
	}

	/**
	 * Sets up the AMP Customizer preview.
	 */
	public function register_ui() {
		$this->wp_customize->add_panel(
			self::PANEL_ID,
			array(
				'type'        => 'amp',
				'title'       => __( 'AMP', 'amp' ),
				/* translators: placeholder is URL to AMP project. */
				'description' => sprintf( __( '<a href="%s" target="_blank">The AMP Project</a> is a Google-led initiative that dramatically improves loading speeds on phones and tablets. You can use the Customizer to preview changes to your AMP template before publishing them.', 'amp' ), 'https://ampproject.org' ),
			)
		);

		/**
		 * Fires after the AMP panel has been registered for plugins to add additional controls.
		 *
		 * In practice the `customize_register` hook should be used instead.
		 *
		 * @since 0.4
		 * @param WP_Customize_Manager $manager Manager.
		 */
		do_action( 'amp_customizer_register_ui', $this->wp_customize );
	}

	/**
	 * Registers settings for customizing AMP templates.
	 *
	 * @since 0.4
	 */
	public function register_settings() {

		/**
		 * Fires when plugins should register settings for AMP.
		 *
		 * In practice the `customize_register` hook should be used instead.
		 *
		 * @since 0.4
		 * @param WP_Customize_Manager $manager Manager.
		 */
		do_action( 'amp_customizer_register_settings', $this->wp_customize );
	}

	/**
	 * Load up AMP scripts needed for Customizer integrations.
	 *
	 * @since 0.6
	 */
	public function add_customizer_scripts() {
		if ( ! amp_is_canonical() ) {
			wp_enqueue_script(
				'amp-customize-controls',
				amp_get_asset_url( 'js/amp-customize-controls.js' ),
				array( 'jquery', 'customize-controls' ),
				AMP__VERSION,
				true
			);

			wp_add_inline_script(
				'amp-customize-controls',
				sprintf(
					'ampCustomizeControls.boot( %s );',
					wp_json_encode(
						array(
							'queryVar' => amp_get_slug(),
							'panelId'  => self::PANEL_ID,
							'ampUrl'   => amp_admin_get_preview_permalink(),
							'l10n'     => array(
								'unavailableMessage'  => __( 'AMP is not available for the page currently being previewed.', 'amp' ),
								'unavailableLinkText' => __( 'Navigate to an AMP compatible page', 'amp' ),
							),
						)
					)
				)
			);

			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_style(
				'amp-customizer',
				amp_get_asset_url( 'css/amp-customizer.css' )
			);
		}

		/**
		 * Fires when plugins should register settings for AMP.
		 *
		 * In practice the `customize_controls_enqueue_scripts` hook should be used instead.
		 *
		 * @since 0.4
		 * @param WP_Customize_Manager $manager Manager.
		 */
		do_action( 'amp_customizer_enqueue_scripts', $this->wp_customize );
	}

	/**
	 * Enqueues scripts used in both the AMP and non-AMP Customizer preview.
	 *
	 * @since 0.6
	 * @global WP_Query $wp_query
	 */
	public function enqueue_preview_scripts() {
		global $wp_query;

		// Bail if user can't customize anyway.
		if ( ! current_user_can( 'customize' ) ) {
			return;
		}

		wp_enqueue_script(
			'amp-customize-preview',
			amp_get_asset_url( 'js/amp-customize-preview.js' ),
			array( 'jquery', 'customize-preview' ),
			AMP__VERSION,
			true
		);

		if ( current_theme_supports( AMP_Theme_Support::SLUG ) ) {
			$availability = AMP_Theme_Support::get_template_availability();
			$available    = $availability['supported'];
		} elseif ( is_singular() || $wp_query->is_posts_page ) {
			/**
			 * Queried object.
			 *
			 * @var WP_Post $queried_object
			 */
			$queried_object = get_queried_object();
			$available      = post_supports_amp( $queried_object );
		} else {
			$available = false;
		}

		wp_add_inline_script(
			'amp-customize-preview',
			sprintf(
				'ampCustomizePreview.boot( %s );',
				wp_json_encode(
					array(
						'available' => $available,
						'enabled'   => is_amp_endpoint(),
					)
				)
			)
		);
	}

	/**
	 * Add AMP Customizer preview styles.
	 */
	public function add_customize_preview_styles() {
		?>
		/* Text meant only for screen readers; this is needed for wp.a11y.speak() */
		.screen-reader-text {
			border: 0;
			clip: rect(1px, 1px, 1px, 1px);
			-webkit-clip-path: inset(50%);
			clip-path: inset(50%);
			height: 1px;
			margin: -1px;
			overflow: hidden;
			padding: 0;
			position: absolute;
			width: 1px;
			word-wrap: normal !important;
		}
		body.wp-customizer-unloading {
			opacity: 0.25 !important; /* Because AMP sets body to opacity:1 once layout complete. */
		}
		<?php
	}

	/**
	 * Enqueues scripts and does wp_print_footer_scripts() so we can output customizer scripts.
	 *
	 * This breaks AMP validation in the customizer but is necessary for the live preview.
	 *
	 * @since 0.6
	 */
	public function add_preview_scripts() {

		// Bail if user can't customize anyway.
		if ( ! current_user_can( 'customize' ) ) {
			return;
		}

		wp_enqueue_script( 'customize-selective-refresh' );
		wp_enqueue_script( 'amp-customize-preview' );

		/**
		 * Fires when plugins should enqueue their own scripts for the AMP Customizer preview.
		 *
		 * @since 0.4
		 * @param WP_Customize_Manager $wp_customize Manager.
		 */
		do_action( 'amp_customizer_enqueue_preview_scripts', $this->wp_customize );

		$this->wp_customize->customize_preview_settings();
		$this->wp_customize->selective_refresh->export_preview_data();

		wp_print_footer_scripts();
	}

	/**
	 * Print templates needed for AMP in Customizer.
	 *
	 * @since 0.6
	 */
	public function print_controls_templates() {
		?>
		<script type="text/html" id="tmpl-customize-amp-enabled-toggle">
			<div class="amp-toggle">
				<# var elementIdPrefix = _.uniqueId( 'customize-amp-enabled-toggle' ); #>
				<div id="{{ elementIdPrefix }}tooltip" aria-hidden="true" class="tooltip" role="tooltip">
					{{ data.message }}
					<# if ( data.url ) { #>
						<a href="{{ data.url }}">{{ data.linkText }}</a>
					<# } #>
				</div>
				<input id="{{ elementIdPrefix }}checkbox" type="checkbox" class="disabled" aria-describedby="{{ elementIdPrefix }}tooltip">
				<span class="slider"></span>
				<label for="{{ elementIdPrefix }}checkbox" class="screen-reader-text"><?php esc_html_e( 'AMP preview enabled', 'amp' ); ?></label>
			</div>
		</script>
		<script type="text/html" id="tmpl-customize-amp-unavailable-notification">
			<li class="notice notice-{{ data.type || 'info' }} {{ data.alt ? 'notice-alt' : '' }} {{ data.containerClasses || '' }}" data-code="{{ data.code }}" data-type="{{ data.type }}">
				<div class="notification-message">
					{{ data.message }}
					<# if ( data.url ) { #>
						<a href="{{ data.url }}">{{ data.linkText }}</a>
					<# } #>
				</div>
			</li>
		</script>
		<?php
	}

	/**
	 * Whether the Customizer is AMP. This is always true since the AMP Customizer has been merged with the main Customizer.
	 *
	 * @deprecated 0.6
	 * @return bool
	 */
	public static function is_amp_customizer() {
		_deprecated_function( __METHOD__, '0.6' );
		return true;
	}
}
