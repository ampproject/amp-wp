<?php
/**
 * AMP Options.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Option;

/**
 * AMP_Options_Menu class.
 */
class AMP_Options_Menu {

	/**
	 * The AMP svg menu icon.
	 *
	 * @var string
	 */
	const ICON_BASE64_SVG = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjIiIGhlaWdodD0iNjIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTQxLjYyODg2NjcgMjguMTYxNDMzM2wtMTMuMDA0NSAyMS42NDIxMzM0aC0yLjM1NmwyLjMyOTEzMzMtMTQuMTAxOS03LjIxMzcuMDA5M3MtLjA2ODIuMDAyMDY2Ni0uMTAwMjMzMy4wMDIwNjY2Yy0uNjQ5OTY2NyAwLTEuMTc1OTMzNC0uNTI1OTY2Ni0xLjE3NTkzMzQtMS4xNzU5MzMzIDAtLjI3OS4yNTkzNjY3LS43NTEyMzMzLjI1OTM2NjctLjc1MTIzMzNsMTIuOTYyMTMzMy0yMS42MTYzTDM1LjcyNDQgMTIuMTc5OWwtMi4zODgwMzMzIDE0LjEyMzYgNy4yNTA5LS4wMDkzcy4wNzc1LS4wMDEwMzMzLjExNDctLjAwMTAzMzNjLjY0OTk2NjYgMCAxLjE3NTkzMzMuNTI1OTY2NiAxLjE3NTkzMzMgMS4xNzU5MzMzIDAgLjI2MzUtLjEwMzMzMzMuNDk0OTY2Ny0uMjUwMDY2Ny42OTEzbC4wMDEwMzM0LjAwMTAzMzN6TTMxIDBDMTMuODc4NyAwIDAgMTMuODc5NzMzMyAwIDMxYzAgMTcuMTIxMyAxMy44Nzg3IDMxIDMxIDMxIDE3LjEyMDI2NjcgMCAzMS0xMy44Nzg3IDMxLTMxQzYyIDEzLjg3OTczMzMgNDguMTIwMjY2NyAwIDMxIDB6IiBmaWxsPSIjYTBhNWFhIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48L3N2Zz4=';

	/**
	 * Initialize.
	 */
	public function init() {
		add_action( 'admin_post_amp_analytics_options', 'AMP_Options_Manager::handle_analytics_submit' );
		add_action( 'admin_menu', [ $this, 'add_menu_items' ], 9 );

		$plugin_file = preg_replace( '#.+/(?=.+?/.+?)#', '', AMP__FILE__ );
		add_filter( "plugin_action_links_{$plugin_file}", [ $this, 'add_plugin_action_links' ] );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Links.
	 * @return array Modified links.
	 */
	public function add_plugin_action_links( $links ) {
		return array_merge(
			[
				'settings' => sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( add_query_arg( 'page', AMP_Options_Manager::OPTION_NAME, admin_url( 'admin.php' ) ) ),
					__( 'Settings', 'amp' )
				),
			],
			$links
		);
	}

	/**
	 * Add menu.
	 */
	public function add_menu_items() {
		/*
		 * Note that the admin items for Validated URLs and Validation Errors will also be placed under this admin menu
		 * page when the current user can manage_options.
		 */
		add_menu_page(
			__( 'AMP Options', 'amp' ),
			__( 'AMP', 'amp' ),
			'manage_options',
			AMP_Options_Manager::OPTION_NAME,
			[ $this, 'render_screen' ],
			self::ICON_BASE64_SVG
		);

		add_submenu_page(
			AMP_Options_Manager::OPTION_NAME,
			__( 'AMP Settings', 'amp' ),
			__( 'General', 'amp' ),
			'manage_options',
			AMP_Options_Manager::OPTION_NAME
		);

		add_settings_section(
			'general',
			false,
			'__return_false',
			AMP_Options_Manager::OPTION_NAME
		);

		add_settings_field(
			Option::THEME_SUPPORT,
			__( 'Template Mode', 'amp' ),
			[ $this, 'render_theme_support' ],
			AMP_Options_Manager::OPTION_NAME,
			'general',
			[
				'class' => 'amp-website-mode',
			]
		);

		add_settings_field(
			Option::SUPPORTED_TEMPLATES,
			__( 'Supported Templates', 'amp' ),
			[ $this, 'render_supported_templates' ],
			AMP_Options_Manager::OPTION_NAME,
			'general',
			[
				'class' => 'amp-template-support-field',
			]
		);

		add_settings_field(
			Option::DEV_TOOLS,
			__( 'Developer Tools', 'amp' ),
			[ $this, 'render_dev_tools' ],
			AMP_Options_Manager::OPTION_NAME,
			'general'
		);

		$submenus = [
			new AMP_Analytics_Options_Submenu( AMP_Options_Manager::OPTION_NAME ),
		];

		// Create submenu items and calls on the Submenu Page object to render the actual contents of the page.
		foreach ( $submenus as $submenu ) {
			$submenu->init();
		}
	}

	/**
	 * Render theme support.
	 *
	 * @since 1.0
	 */
	public function render_theme_support() {
		$theme_support = AMP_Theme_Support::get_support_mode();

		/* translators: %s: URL to the documentation. */
		$standard_description = sprintf( __( 'The active theme integrates AMP as the framework for your site by using its templates and styles to render webpages. This means your site is <b>AMP-first</b> and your canonical URLs are AMP! Depending on your theme/plugins, a varying level of <a href="%s">development work</a> may be required.', 'amp' ), esc_url( 'https://amp-wp.org/documentation/developing-wordpress-amp-sites/' ) );
		/* translators: %s: URL to the documentation. */
		$transitional_description = sprintf( __( 'The active theme’s templates are used to generate non-AMP and AMP versions of your content, allowing for each canonical URL to have a corresponding (paired) AMP URL. This mode is useful to progressively transition towards a fully AMP-first site. Depending on your theme/plugins, a varying level of <a href="%s">development work</a> may be required.', 'amp' ), esc_url( 'https://amp-wp.org/documentation/developing-wordpress-amp-sites/' ) );
		$reader_description       = __( 'Formerly called the <b>classic mode</b>, this mode generates paired AMP content using simplified templates which may not match the look-and-feel of your site. Only posts/pages can be served as AMP in Reader mode. No redirection is performed for mobile visitors; AMP pages are served by AMP consumption platforms.', 'amp' );
		/* translators: %s: URL to the ecosystem page. */
		$ecosystem_description = sprintf( __( 'For a list of themes and plugins that are known to be AMP compatible, please see the <a href="%s">ecosystem page</a>.', 'amp' ), esc_url( 'https://amp-wp.org/ecosystem/' ) );

		$builtin_support     = in_array( get_template(), AMP_Core_Theme_Sanitizer::get_supported_themes(), true );
		$reader_mode_support = __( 'Your theme indicates it works best in <strong>Reader mode.</strong>', 'amp' );
		?>

		<fieldset>
			<?php if ( AMP_Theme_Support::READER_MODE_SLUG === AMP_Theme_Support::get_support_mode() ) : ?>
				<?php if ( AMP_Theme_Support::STANDARD_MODE_SLUG === AMP_Theme_Support::get_support_mode_added_via_theme() ) : ?>
					<div class="notice notice-success notice-alt inline">
						<p><?php esc_html_e( 'Your active theme is known to work well in standard mode.', 'amp' ); ?></p>
					</div>
				<?php elseif ( $builtin_support || AMP_Theme_Support::TRANSITIONAL_MODE_SLUG === AMP_Theme_Support::get_support_mode_added_via_theme() ) : ?>
					<div class="notice notice-success notice-alt inline">
						<p><?php esc_html_e( 'Your active theme is known to work well in standard or transitional mode.', 'amp' ); ?></p>
					</div>
				<?php endif; ?>
			<?php elseif ( AMP_Theme_Support::supports_reader_mode() ) : ?>
				<div class="notice notice-success notice-alt inline">
					<p><?php echo wp_kses( $reader_mode_support, [ 'strong' => [] ] ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! AMP_Theme_Support::get_support_mode_added_via_theme() && ! AMP_Theme_Support::supports_reader_mode() && ! $builtin_support ) : ?>
				<p>
					<?php echo wp_kses_post( $ecosystem_description ); ?>
				</p>
			<?php endif; ?>

			<dl>
				<dt>
					<input type="radio" id="theme_support_standard" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[theme_support]' ); ?>" value="<?php echo esc_attr( AMP_Theme_Support::STANDARD_MODE_SLUG ); ?>" <?php checked( $theme_support, AMP_Theme_Support::STANDARD_MODE_SLUG ); ?>>
					<label for="theme_support_standard">
						<strong><?php esc_html_e( 'Standard', 'amp' ); ?></strong>
					</label>
				</dt>
				<dd>
					<p><?php echo wp_kses_post( $standard_description ); ?></p>
				</dd>
				<dt>
					<input type="radio" id="theme_support_transitional" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[theme_support]' ); ?>" value="<?php echo esc_attr( AMP_Theme_Support::TRANSITIONAL_MODE_SLUG ); ?>" <?php checked( $theme_support, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG ); ?>>
					<label for="theme_support_transitional">
						<strong><?php esc_html_e( 'Transitional', 'amp' ); ?></strong>
					</label>
				</dt>
				<dd>
					<p><?php echo wp_kses_post( $transitional_description ); ?></p>
				</dd>
				<dt>
					<input type="radio" id="theme_support_disabled" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[theme_support]' ); ?>" value="<?php echo esc_attr( AMP_Theme_Support::READER_MODE_SLUG ); ?>" <?php checked( $theme_support, AMP_Theme_Support::READER_MODE_SLUG ); ?>>
					<label for="theme_support_disabled">
						<strong><?php esc_html_e( 'Reader', 'amp' ); ?></strong>
					</label>
				</dt>
				<dd>
					<p><?php echo wp_kses_post( $reader_description ); ?></p>

					<style>
						#reader_mode_themes {
							margin-top: 1em;
						}

						#reader_mode_themes legend {
							font-family: inherit;
							font-weight: 600;
						}

						#reader_mode_themes ul {
							margin: 0;
						}

						#reader_mode_themes li {
							display: inline-block;
							line-height: 1;
							margin: 0;
							position: relative;
						}

						#reader_mode_themes input {
							position: absolute;
							opacity: 0;
						}

						#reader_mode_themes label {
							display: inline-block;
							border: 1px solid #ddd;
							margin: 10px !important;
							cursor: pointer;
						}

						#reader_mode_themes .theme-screenshot {
							display: block;
						}

						#reader_mode_themes .theme-name {
							display: block;
							font-size: 15px;
							font-weight: 600;
							height: 18px;
							margin: 0;
							padding: 15px;
							white-space: nowrap;
							text-overflow: ellipsis;
							background: rgba(255, 255, 255, 0.65);
							box-shadow: inset 0 1px 0 rgba(0, 0, 0, 0.1);
						}

						#reader_mode_themes input:checked + label {
							border-color: #5b9dd9;
							box-shadow: 0 0 2px rgba(30, 140, 190, 0.8);
							color: white;
						}

						#reader_mode_themes input:checked + label .theme-name {
							background-color: #5b9dd9;
						}
					</style>
					<fieldset
						id="reader_mode_themes"
						<?php if ( AMP_Theme_Support::READER_MODE_SLUG !== $theme_support ) : ?>
							class="hidden"
						<?php endif; ?>
					>
						<legend><?php esc_html_e( 'Selected Theme:', 'amp' ); ?></legend>

						<ul>
							<?php
							$name          = AMP_Options_Manager::OPTION_NAME . '[' . Option::READER_THEME . ']';
							$current_theme = AMP_Theme_Support::get_current_reader_theme();
							$reader_themes = [];
							foreach ( AMP_Theme_Support::get_reader_themes() as $reader_theme_slug ) {
								$reader_theme_obj = wp_get_theme( $reader_theme_slug );
								if ( $reader_theme_obj->errors() ) {
									continue;
								}
								$reader_themes[ $reader_theme_slug ] = [
									'screenshot' => $reader_theme_obj->get_screenshot(),
									'name'       => $reader_theme_obj->get( 'Name' ),
								];
							}

							$reader_themes[''] = [
								'name'       => __( 'Classic', 'amp' ),
								'screenshot' => plugin_dir_url( AMP__FILE__ ) . 'templates/screenshot.png',
							];
							?>
							<?php foreach ( $reader_themes as $slug => $theme ) : ?>
								<?php $id = $name . "[$slug]"; ?>
								<li>
									<input
										type="radio"
										id="<?php echo esc_attr( $id ); ?>"
										name="<?php echo esc_attr( $name ); ?>"
										value="<?php echo esc_attr( $slug ); ?>"
										<?php checked( $current_theme, $slug ); ?>
									>
									<label for="<?php echo esc_attr( $name . "[$slug]" ); ?>">
										<img class="theme-screenshot" src="<?php echo esc_url( $theme['screenshot'] ); ?>" width="300" height="225" alt="<?php echo esc_attr( $theme['name'] ); ?>">
										<span class="theme-name"><?php echo esc_html( $theme['name'] ); ?></span>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					</fieldset>

					<script>
						// Update the visibility of the fieldset based on the selected template mode.
						(function ( $ ) {
							const templateModeInputs = $( 'input[type=radio][name="amp-options[theme_support]"]' );
							const readerModeThemesFieldset = $( '#reader_mode_themes' );
							const themeSupportDisabledInput = $( '#theme_support_disabled' );

							function isReaderMode() {
								return themeSupportDisabledInput.prop( 'checked' );
							}

							function updateFieldsetVisibility() {
								readerModeThemesFieldset.toggleClass( 'hidden', ! isReaderMode() )
							}
							templateModeInputs.on( 'change', updateFieldsetVisibility );
						})( jQuery );
					</script>
				</dd>
			</dl>

			<?php if ( AMP_Theme_Support::get_support_mode_added_via_theme() ) : ?>
				<p>
					<?php echo wp_kses_post( $ecosystem_description ); ?>
				</p>
			<?php endif; ?>
		</fieldset>
		<?php
	}

	/**
	 * Supported templates section renderer.
	 *
	 * @since 1.0
	 */
	public function render_supported_templates() {
		$theme_support_args = AMP_Theme_Support::get_theme_support_args();
		?>

		<?php if ( ! isset( $theme_support_args['available_callback'] ) ) : ?>
			<fieldset id="all_templates_supported_fieldset">
				<?php if ( isset( $theme_support_args['templates_supported'] ) && 'all' === $theme_support_args['templates_supported'] ) : ?>
					<div class="notice notice-info notice-alt inline">
						<p>
							<?php esc_html_e( 'The current theme requires all templates to support AMP.', 'amp' ); ?>
						</p>
					</div>
				<?php else : ?>
					<p>
						<label for="all_templates_supported">
							<input id="all_templates_supported" type="checkbox" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[all_templates_supported]' ); ?>" <?php checked( AMP_Options_Manager::get_option( Option::ALL_TEMPLATES_SUPPORTED ) ); ?>>
							<?php esc_html_e( 'Serve all templates as AMP regardless of what is being queried.', 'amp' ); ?>
						</label>
					</p>
					<p class="description">
						<?php esc_html_e( 'This will allow all of the URLs on your site to be served as AMP by default.', 'amp' ); ?>
					</p>
				<?php endif; ?>
			</fieldset>
		<?php else : ?>
			<div class="notice notice-warning notice-alt inline">
				<p>
					<?php
					printf(
						/* translators: %s: available_callback */
						esc_html__( 'Your theme is using the deprecated %s argument for AMP theme support.', 'amp' ),
						'available_callback'
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<fieldset id="supported_post_types_fieldset">
			<?php
			$element_name         = AMP_Options_Manager::OPTION_NAME . '[supported_post_types][]';
			$supported_post_types = AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES );
			?>
			<h4 class="title"><?php esc_html_e( 'Content Types', 'amp' ); ?></h4>
			<p>
				<?php esc_html_e( 'The following content types will be available as AMP:', 'amp' ); ?>
			</p>
			<ul>
			<?php foreach ( array_map( 'get_post_type_object', AMP_Post_Type_Support::get_eligible_post_types() ) as $post_type ) : ?>
				<?php
				$checked = (
					post_type_supports( $post_type->name, AMP_Post_Type_Support::SLUG )
					||
					in_array( $post_type->name, $supported_post_types, true )
				);
				?>
				<li>
					<?php $element_id = AMP_Options_Manager::OPTION_NAME . "-supported_post_types-{$post_type->name}"; ?>
					<input
						type="checkbox"
						id="<?php echo esc_attr( $element_id ); ?>"
						name="<?php echo esc_attr( $element_name ); ?>"
						value="<?php echo esc_attr( $post_type->name ); ?>"
						<?php checked( $checked ); ?>
						>
					<label for="<?php echo esc_attr( $element_id ); ?>">
						<?php echo esc_html( $post_type->label ); ?>
					</label>
				</li>
			<?php endforeach; ?>
			</ul>
		</fieldset>

		<?php if ( ! isset( $theme_support_args['available_callback'] ) ) : ?>
			<fieldset id="supported_templates_fieldset">
				<style>
					#supported_templates_fieldset ul ul {
						margin-left: 40px;
					}
				</style>
				<h4 class="title"><?php esc_html_e( 'Templates', 'amp' ); ?></h4>
				<?php
				$this->list_template_conditional_options( AMP_Theme_Support::get_supportable_templates() );
				?>
				<script>
					// Let clicks on parent items automatically cause the children checkboxes to have same checked state applied.
					(function ( $ ) {
						$( '#supported_templates_fieldset input[type=checkbox]' ).on( 'click', function() {
							$( this ).siblings( 'ul' ).find( 'input[type=checkbox]' ).prop( 'checked', this.checked );
						} );
					})( jQuery );
				</script>
			</fieldset>

			<script>
				// Update the visibility of the fieldsets based on the selected template mode and then whether all templates are indicated to be supported.
				(function ( $ ) {
					const templateModeInputs = $( 'input[type=radio][name="amp-options[theme_support]"]' );
					const themeSupportDisabledInput = $( '#theme_support_disabled' );
					const allTemplatesSupportedInput = $( '#all_templates_supported' );

					function isThemeSupportDisabled() {
						return Boolean( themeSupportDisabledInput.length && themeSupportDisabledInput.prop( 'checked' ) );
					}

					function updateFieldsetVisibility() {
						const allTemplatesSupported = 0 === allTemplatesSupportedInput.length || allTemplatesSupportedInput.prop( 'checked' );
						$( '#all_templates_supported_fieldset, #supported_post_types_fieldset > .title' ).toggleClass(
							'hidden',
							isThemeSupportDisabled()
						);
						$( '#supported_post_types_fieldset' ).toggleClass(
							'hidden',
							allTemplatesSupported && ! isThemeSupportDisabled()
						);
						$( '#supported_templates_fieldset' ).toggleClass(
							'hidden',
							allTemplatesSupported || isThemeSupportDisabled()
						);
					}

					templateModeInputs.on( 'change', updateFieldsetVisibility );
					allTemplatesSupportedInput.on( 'click', updateFieldsetVisibility );
					updateFieldsetVisibility();
				})( jQuery );
			</script>
		<?php endif; ?>
		<?php
	}

	/**
	 * List template conditional options.
	 *
	 * @param array       $options Options.
	 * @param string|null $parent  ID of the parent option.
	 */
	private function list_template_conditional_options( $options, $parent = null ) {
		$element_name = AMP_Options_Manager::OPTION_NAME . '[supported_templates][]';
		?>
		<ul>
			<?php foreach ( $options as $id => $option ) : ?>
				<?php
				$element_id = AMP_Options_Manager::OPTION_NAME . '-supported-templates-' . $id;
				if ( $parent ? empty( $option['parent'] ) || $parent !== $option['parent'] : ! empty( $option['parent'] ) ) {
					continue;
				}

				// Skip showing an option if it doesn't have a label.
				if ( empty( $option['label'] ) ) {
					continue;
				}

				?>
				<li>
					<?php if ( empty( $option['immutable'] ) ) : ?>
						<input
							type="checkbox"
							id="<?php echo esc_attr( $element_id ); ?>"
							name="<?php echo esc_attr( $element_name ); ?>"
							value="<?php echo esc_attr( $id ); ?>"
							<?php checked( ! empty( $option['user_supported'] ) ); ?>
						>
					<?php else : // Persist user selection even when checkbox disabled, when selection forced by theme/filter. ?>
						<input
							type="checkbox"
							id="<?php echo esc_attr( $element_id ); ?>"
							<?php checked( ! empty( $option['supported'] ) ); ?>
							<?php disabled( true ); ?>
						>
						<?php if ( ! empty( $option['user_supported'] ) ) : ?>
							<input type="hidden" name="<?php echo esc_attr( $element_name ); ?>" value="<?php echo esc_attr( $id ); ?>">
						<?php endif; ?>
					<?php endif; ?>
					<label for="<?php echo esc_attr( $element_id ); ?>">
						<?php echo esc_html( $option['label'] ); ?>
					</label>

					<?php if ( ! empty( $option['description'] ) ) : ?>
						<span class="description">
							&mdash; <?php echo wp_kses_post( $option['description'] ); ?>
						</span>
					<?php endif; ?>

					<?php self::list_template_conditional_options( $options, $id ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Render developer tools.
	 *
	 * @since 1.0
	 */
	public function render_dev_tools() {
		?>
		<p>
			<label for="dev_tools">
				<input id="dev_tools" type="checkbox" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[' . Option::DEV_TOOLS . ']' ); ?>" <?php checked( AMP_Options_Manager::get_option( Option::DEV_TOOLS ) ); ?>>
				<?php esc_html_e( 'Enable developer tools to expose validated URLs and validation errors for invalid markup.', 'amp' ); ?>
			</label>
		</p>
		<p class="description">
			<?php esc_html_e( 'This will reveal warnings in the post editor when invalid AMP markup is generated by themes and plugins or the content of a post or page. This is most useful when you have have some technical experience to be able to evaluate the errors and take action to either fix the issues or decide whether the invalid markup can be safely removed.', 'amp' ); ?>
		</p>
		<?php
	}

	/**
	 * Display Settings.
	 *
	 * @since 0.6
	 */
	public function render_screen() {
		if ( ! empty( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			AMP_Options_Manager::check_supported_post_type_update_errors();
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<form id="amp-settings" action="options.php" method="post">
				<?php
				settings_fields( AMP_Options_Manager::OPTION_NAME );
				do_settings_sections( AMP_Options_Manager::OPTION_NAME );
				if ( current_user_can( 'manage_options' ) ) {
					submit_button();
				}
				?>
			</form>
		</div>
		<?php
	}
}
