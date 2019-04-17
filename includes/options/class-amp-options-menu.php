<?php
/**
 * AMP Options.
 *
 * @package AMP
 */

/**
 * AMP_Options_Menu class.
 */
class AMP_Options_Menu {

	/**
	 * The AMP svg menu icon.
	 *
	 * @var string
	 */
	const ICON_BASE64_SVG = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyB3aWR0aD0iNjJweCIgaGVpZ2h0PSI2MnB4IiB2aWV3Qm94PSIwIDAgNjIgNjIiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+ICAgICAgICA8dGl0bGU+QU1QLUJyYW5kLUJsYWNrLUljb248L3RpdGxlPiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4gICAgPGRlZnM+PC9kZWZzPiAgICA8ZyBpZD0iYW1wLWxvZ28taW50ZXJuYWwtc2l0ZSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+ICAgICAgICA8ZyBpZD0iQU1QLUJyYW5kLUJsYWNrLUljb24iIGZpbGw9IiMwMDAwMDAiPiAgICAgICAgICAgIDxwYXRoIGQ9Ik00MS42Mjg4NjY3LDI4LjE2MTQzMzMgTDI4LjYyNDM2NjcsNDkuODAzNTY2NyBMMjYuMjY4MzY2Nyw0OS44MDM1NjY3IEwyOC41OTc1LDM1LjcwMTY2NjcgTDIxLjM4MzgsMzUuNzEwOTY2NyBDMjEuMzgzOCwzNS43MTA5NjY3IDIxLjMxNTYsMzUuNzEzMDMzMyAyMS4yODM1NjY3LDM1LjcxMzAzMzMgQzIwLjYzMzYsMzUuNzEzMDMzMyAyMC4xMDc2MzMzLDM1LjE4NzA2NjcgMjAuMTA3NjMzMywzNC41MzcxIEMyMC4xMDc2MzMzLDM0LjI1ODEgMjAuMzY3LDMzLjc4NTg2NjcgMjAuMzY3LDMzLjc4NTg2NjcgTDMzLjMyOTEzMzMsMTIuMTY5NTY2NyBMMzUuNzI0NCwxMi4xNzk5IEwzMy4zMzYzNjY3LDI2LjMwMzUgTDQwLjU4NzI2NjcsMjYuMjk0MiBDNDAuNTg3MjY2NywyNi4yOTQyIDQwLjY2NDc2NjcsMjYuMjkzMTY2NyA0MC43MDE5NjY3LDI2LjI5MzE2NjcgQzQxLjM1MTkzMzMsMjYuMjkzMTY2NyA0MS44Nzc5LDI2LjgxOTEzMzMgNDEuODc3OSwyNy40NjkxIEM0MS44Nzc5LDI3LjczMjYgNDEuNzc0NTY2NywyNy45NjQwNjY3IDQxLjYyNzgzMzMsMjguMTYwNCBMNDEuNjI4ODY2NywyOC4xNjE0MzMzIFogTTMxLDAgQzEzLjg3ODcsMCAwLDEzLjg3OTczMzMgMCwzMSBDMCw0OC4xMjEzIDEzLjg3ODcsNjIgMzEsNjIgQzQ4LjEyMDI2NjcsNjIgNjIsNDguMTIxMyA2MiwzMSBDNjIsMTMuODc5NzMzMyA0OC4xMjAyNjY3LDAgMzEsMCBMMzEsMCBaIiBpZD0iRmlsbC0xIj48L3BhdGg+ICAgICAgICA8L2c+ICAgIDwvZz48L3N2Zz4=';

	/**
	 * Initialize.
	 */
	public function init() {
		add_action( 'admin_post_amp_analytics_options', 'AMP_Options_Manager::handle_analytics_submit' );
		add_action( 'admin_menu', array( $this, 'add_menu_items' ), 9 );

		$plugin_file = preg_replace( '#.+/(?=.+?/.+?)#', '', AMP__FILE__ );
		add_filter( "plugin_action_links_{$plugin_file}", array( $this, 'add_plugin_action_links' ) );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Links.
	 * @return array Modified links.
	 */
	public function add_plugin_action_links( $links ) {
		return array_merge(
			array(
				'settings' => sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( add_query_arg( 'page', AMP_Options_Manager::OPTION_NAME, admin_url( 'admin.php' ) ) ),
					__( 'Settings', 'amp' )
				),
			),
			$links
		);
	}

	/**
	 * Add menu.
	 */
	public function add_menu_items() {

		add_menu_page(
			__( 'AMP Options', 'amp' ),
			__( 'AMP', 'amp' ),
			'edit_posts',
			AMP_Options_Manager::OPTION_NAME,
			array( $this, 'render_screen' ),
			self::ICON_BASE64_SVG
		);

		add_submenu_page(
			AMP_Options_Manager::OPTION_NAME,
			__( 'AMP Settings', 'amp' ),
			__( 'General', 'amp' ),
			'edit_posts',
			AMP_Options_Manager::OPTION_NAME
		);

		add_settings_section(
			'general',
			false,
			'__return_false',
			AMP_Options_Manager::OPTION_NAME
		);

		add_settings_field(
			'theme_support',
			__( 'Template Mode', 'amp' ),
			array( $this, 'render_theme_support' ),
			AMP_Options_Manager::OPTION_NAME,
			'general',
			array(
				'class' => 'theme_support',
			)
		);

		add_settings_field(
			'validation',
			__( 'Validation Handling', 'amp' ),
			array( $this, 'render_validation_handling' ),
			AMP_Options_Manager::OPTION_NAME,
			'general',
			array(
				'class' => 'amp-validation-field',
			)
		);

		add_settings_field(
			'supported_templates',
			__( 'Supported Templates', 'amp' ),
			array( $this, 'render_supported_templates' ),
			AMP_Options_Manager::OPTION_NAME,
			'general',
			array(
				'class' => 'amp-template-support-field',
			)
		);

		if ( wp_using_ext_object_cache() ) {
			add_settings_field(
				'caching',
				__( 'Caching', 'amp' ),
				array( $this, 'render_caching' ),
				AMP_Options_Manager::OPTION_NAME,
				'general',
				array(
					'class' => 'amp-caching-field',
				)
			);
		}

		$submenus = array(
			new AMP_Analytics_Options_Submenu( AMP_Options_Manager::OPTION_NAME ),
		);

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
		$theme_support = AMP_Options_Manager::get_option( 'theme_support' );

		/* translators: %s: URL to the documentation. */
		$native_description = sprintf( __( 'Integrates AMP as the framework for your site by using the active’s theme templates and styles to render AMP responses. This means your site is <b>AMP-first</b> and your canonical URLs are AMP! Depending on your theme/plugins, a varying level of <a href="%s">development work</a> may be required.', 'amp' ), esc_url( 'https://amp-wp.org/documentation/developing-wordpress-amp-sites/' ) );
		/* translators: %s: URL to the documentation. */
		$transitional_description = sprintf( __( 'Uses the active theme’s templates to generate non-AMP and AMP versions of your content, allowing for each canonical URL to have a corresponding (paired) AMP URL. This mode is useful to progressively transition towards a fully AMP-first site. Depending on your theme/plugins, a varying level of <a href="%s">development work</a> may be required.', 'amp' ), esc_url( 'https://amp-wp.org/documentation/developing-wordpress-amp-sites/' ) );
		$reader_description       = __( 'Formerly called the <b>classic mode</b>, this mode generates paired AMP content using simplified templates which may not match the look-and-feel of your site. Only posts/pages can be served as AMP in Reader mode. No redirection is performed for mobile visitors; AMP pages are served by AMP consumption platforms.', 'amp' );
		/* translators: %s: URL to the ecosystem page. */
		$ecosystem_description = sprintf( __( 'For a list of themes and plugins that are known to be AMP compatible, please see the <a href="%s">ecosystem page</a>.' ), esc_url( 'https://amp-wp.org/ecosystem/' ) );

		$builtin_support = in_array( get_template(), AMP_Core_Theme_Sanitizer::get_supported_themes(), true );
		?>
		<?php if ( current_theme_supports( AMP_Theme_Support::SLUG ) && ! AMP_Theme_Support::is_support_added_via_option() ) : ?>
			<div class="notice notice-info notice-alt inline">
				<p><?php esc_html_e( 'Your active theme has built-in AMP support.', 'amp' ); ?></p>
			</div>
			<p>
				<?php echo wp_kses_post( $ecosystem_description ); ?>
			</p>
			<p>
				<?php if ( amp_is_canonical() ) : ?>
					<strong><?php esc_html_e( 'Native:', 'amp' ); ?></strong>
					<?php echo wp_kses_post( $native_description ); ?>
				<?php else : ?>
					<strong><?php esc_html_e( 'Transitional:', 'amp' ); ?></strong>
					<?php echo wp_kses_post( $transitional_description ); ?>
				<?php endif; ?>
			</p>
		<?php else : ?>
			<fieldset <?php disabled( ! current_user_can( 'manage_options' ) ); ?>>
				<?php if ( $builtin_support ) : ?>
					<div class="notice notice-success notice-alt inline">
						<p><?php esc_html_e( 'Your active theme is known to work well in transitional or native mode.', 'amp' ); ?></p>
					</div>
				<?php endif; ?>
				<p>
					<?php echo wp_kses_post( $ecosystem_description ); ?>
				</p>
				<dl>
					<dt>
						<input type="radio" id="theme_support_native" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[theme_support]' ); ?>" value="native" <?php checked( $theme_support, 'native' ); ?>>
						<label for="theme_support_native">
							<strong><?php esc_html_e( 'Native', 'amp' ); ?></strong>
						</label>
					</dt>
					<dd>
						<?php echo wp_kses_post( $native_description ); ?>
					</dd>
					<dt>
						<input type="radio" id="theme_support_transitional" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[theme_support]' ); ?>" value="paired" <?php checked( $theme_support, 'paired' ); ?>>
						<label for="theme_support_transitional">
							<strong><?php esc_html_e( 'Transitional', 'amp' ); ?></strong>
						</label>
					</dt>
					<dd>
						<?php echo wp_kses_post( $transitional_description ); ?>
					</dd>
					<dt>
						<input type="radio" id="theme_support_disabled" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[theme_support]' ); ?>" value="disabled" <?php checked( $theme_support, 'disabled' ); ?>>
						<label for="theme_support_disabled">
							<strong><?php esc_html_e( 'Reader', 'amp' ); ?></strong>
						</label>
					</dt>
					<dd>
						<?php echo wp_kses_post( $reader_description ); ?>

						<?php if ( ! current_theme_supports( AMP_Theme_Support::SLUG ) && wp_count_posts( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG )->publish > 0 ) : ?>
							<div class="notice notice-info inline notice-alt">
								<p>
									<?php
									echo wp_kses_post(
										sprintf(
											/* translators: %1: link to invalid URLs. 2: link to validation errors. */
											__( 'View current site compatibility results for native and transitional modes: %1$s and %2$s.', 'amp' ),
											sprintf(
												'<a href="%s">%s</a>',
												esc_url( add_query_arg( 'post_type', AMP_Validated_URL_Post_Type::POST_TYPE_SLUG, admin_url( 'edit.php' ) ) ),
												esc_html( get_post_type_object( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG )->labels->name )
											),
											sprintf(
												'<a href="%s">%s</a>',
												esc_url(
													add_query_arg(
														array(
															'taxonomy' => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
															'post_type' => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
														),
														admin_url( 'edit-tags.php' )
													)
												),
												esc_html( get_taxonomy( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG )->labels->name )
											)
										)
									);
									?>
								</p>
							</div>
						<?php endif; ?>
					</dd>
				</dl>
			</fieldset>
		<?php endif; ?>
		<?php
	}

	/**
	 * Post types support section renderer.
	 *
	 * @todo If dirty AMP is ever allowed (that is, post-processed documents which can be served with non-sanitized valdation errors), then automatically forcing sanitization in native should be able to be turned off.
	 *
	 * @since 1.0
	 */
	public function render_validation_handling() {
		?>
		<fieldset <?php disabled( ! current_user_can( 'manage_options' ) ); ?>>
			<?php
			$auto_sanitization = AMP_Validation_Error_Taxonomy::get_validation_error_sanitization(
				array(
					'code' => 'non_existent',
				)
			);
			remove_filter( 'amp_validation_error_sanitized', array( 'AMP_Validation_Manager', 'filter_tree_shaking_validation_error_as_accepted' ) );
			$tree_shaking_sanitization = AMP_Validation_Error_Taxonomy::get_validation_error_sanitization(
				array(
					'code' => AMP_Style_Sanitizer::TREE_SHAKING_ERROR_CODE,
				)
			);

			$forced_sanitization = 'with_filter' === $auto_sanitization['forced'];
			$forced_tree_shaking = $forced_sanitization || 'with_filter' === $tree_shaking_sanitization['forced'];
			?>

			<?php if ( $forced_sanitization ) : ?>
				<div class="notice notice-info notice-alt inline">
					<p><?php esc_html_e( 'Your install is configured via a theme or plugin to automatically sanitize any AMP validation error that is encountered.', 'amp' ); ?></p>
				</div>
				<input type="hidden" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[auto_accept_sanitization]' ); ?>" value="<?php echo AMP_Options_Manager::get_option( 'auto_accept_sanitization' ) ? 'on' : ''; ?>">
			<?php else : ?>
				<div class="amp-auto-accept-sanitize-canonical notice notice-info notice-alt inline">
					<p><?php esc_html_e( 'All new validation errors are automatically accepted when in native mode.', 'amp' ); ?></p>
				</div>
				<div class="amp-auto-accept-sanitize">
					<p>
						<label for="auto_accept_sanitization">
							<input id="auto_accept_sanitization" type="checkbox" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[auto_accept_sanitization]' ); ?>" <?php checked( AMP_Options_Manager::get_option( 'auto_accept_sanitization' ) ); ?>>
							<?php esc_html_e( 'Automatically accept sanitization for any newly encountered AMP validation errors.', 'amp' ); ?>
						</label>
					</p>
					<p class="description">
						<?php esc_html_e( 'This will ensure your responses are always valid AMP but some important content may get stripped out (e.g. scripts).', 'amp' ); ?>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s is URL to validation errors screen */
								__( 'Existing validation errors which you have already rejected will not be modified (you may want to consider <a href="%s">bulk-accepting them</a>).', 'amp' ),
								esc_url(
									add_query_arg(
										array(
											'taxonomy'  => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
											'post_type' => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
										),
										admin_url( 'edit-tags.php' )
									)
								)
							)
						)
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $forced_tree_shaking ) : ?>
				<input type="hidden" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[accept_tree_shaking]' ); ?>" value="<?php echo AMP_Options_Manager::get_option( 'accept_tree_shaking' ) ? 'on' : ''; ?>">
			<?php else : ?>
				<div class="amp-tree-shaking">
					<p>
						<label for="accept_tree_shaking">
							<input id="accept_tree_shaking" type="checkbox" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[accept_tree_shaking]' ); ?>" <?php checked( AMP_Options_Manager::get_option( 'accept_tree_shaking' ) ); ?>>
							<?php esc_html_e( 'Automatically remove CSS rules that are not relevant to a given page (tree shaking).', 'amp' ); ?>
						</label>
					</p>
					<p class="description">
						<?php esc_html_e( 'AMP limits the total amount of CSS to no more than 50KB; any more than this will cause a validation error. The need to tree shake the CSS is not done by default because in some situations (in particular for dynamic content) it can result in CSS rules being removed that are needed.', 'amp' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<script>
			(function( $ ) {
				var getThemeSupportMode = function() {
					var checkedInput = $( 'input[type=radio][name="amp-options[theme_support]"]:checked' );
					if ( 0 === checkedInput.length ) {
						return <?php echo wp_json_encode( amp_is_canonical() ? 'native' : 'paired' ); ?>;
					}
					return checkedInput.val();
				};

				var updateTreeShakingHiddenClass = function() {
					var checkbox = $( '#auto_accept_sanitization' );
					$( '.amp-tree-shaking' ).toggleClass( 'hidden', checkbox.prop( 'checked' ) && 'native' !== getThemeSupportMode() );
				};

				var updateHiddenClasses = function() {
					var themeSupportMode = getThemeSupportMode();
					$( '.amp-auto-accept-sanitize' ).toggleClass( 'hidden', 'native' === themeSupportMode );
					$( '.amp-validation-field' ).toggleClass( 'hidden', 'disabled' === themeSupportMode );
					$( '.amp-auto-accept-sanitize-canonical' ).toggleClass( 'hidden', 'native' !== themeSupportMode );
					updateTreeShakingHiddenClass();
				};

				$( 'input[type=radio][name="amp-options[theme_support]"]' ).change( updateHiddenClasses );
				$( '#auto_accept_sanitization' ).change( updateTreeShakingHiddenClass );

				updateHiddenClasses();
			})( jQuery );
			</script>

			<p>
				<label for="disable_admin_bar">
					<input id="disable_admin_bar" type="checkbox" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[disable_admin_bar]' ); ?>" <?php checked( AMP_Options_Manager::get_option( 'disable_admin_bar' ) ); ?>>
					<?php esc_html_e( 'Disable admin bar on AMP pages.', 'amp' ); ?>
				</label>
			</p>
			<p class="description">
				<?php esc_html_e( 'An additional stylesheet is required to properly render the admin bar. If the additional stylesheet causes the total CSS to surpass 50KB then the admin bar should be disabled to prevent a validation error or an unstyled admin bar in AMP responses.', 'amp' ); ?>
			</p>
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
			<fieldset id="all_templates_supported_fieldset" <?php disabled( ! current_user_can( 'manage_options' ) ); ?>>
				<?php if ( isset( $theme_support_args['templates_supported'] ) && 'all' === $theme_support_args['templates_supported'] ) : ?>
					<div class="notice notice-info notice-alt inline">
						<p>
							<?php esc_html_e( 'The current theme requires all templates to support AMP.', 'amp' ); ?>
						</p>
					</div>
				<?php else : ?>
					<p>
						<label for="all_templates_supported">
							<input id="all_templates_supported" type="checkbox" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[all_templates_supported]' ); ?>" <?php checked( AMP_Options_Manager::get_option( 'all_templates_supported' ) ); ?>>
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

		<fieldset id="supported_post_types_fieldset" <?php disabled( ! current_user_can( 'manage_options' ) ); ?>>
			<?php $element_name = AMP_Options_Manager::OPTION_NAME . '[supported_post_types][]'; ?>
			<h4 class="title"><?php esc_html_e( 'Content Types', 'amp' ); ?></h4>
			<p>
				<?php esc_html_e( 'The following content types will be available as AMP:', 'amp' ); ?>
			</p>
			<ul>
			<?php foreach ( array_map( 'get_post_type_object', AMP_Post_Type_Support::get_eligible_post_types() ) as $post_type ) : ?>
				<li>
					<?php $element_id = AMP_Options_Manager::OPTION_NAME . "-supported_post_types-{$post_type->name}"; ?>
					<input
						type="checkbox"
						id="<?php echo esc_attr( $element_id ); ?>"
						name="<?php echo esc_attr( $element_name ); ?>"
						value="<?php echo esc_attr( $post_type->name ); ?>"
						<?php checked( post_type_supports( $post_type->name, AMP_Post_Type_Support::SLUG ) ); ?>
						>
					<label for="<?php echo esc_attr( $element_id ); ?>">
						<?php echo esc_html( $post_type->label ); ?>
					</label>
				</li>
			<?php endforeach; ?>
			</ul>
		</fieldset>

		<?php if ( ! isset( $theme_support_args['available_callback'] ) ) : ?>
			<fieldset id="supported_templates_fieldset" <?php disabled( ! current_user_can( 'manage_options' ) ); ?>>
				<style>
					#supported_templates_fieldset ul ul {
						margin-left: 40px;
					}
				</style>
				<h4 class="title"><?php esc_html_e( 'Templates', 'amp' ); ?></h4>
				<?php
				self::list_template_conditional_options( AMP_Theme_Support::get_supportable_templates() );
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
					var templateModeInputs, themeSupportDisabledInput, allTemplatesSupportedInput, supportForced;
					templateModeInputs = $( 'input[type=radio][name="amp-options[theme_support]"]' );
					themeSupportDisabledInput = $( '#theme_support_disabled' );
					allTemplatesSupportedInput = $( '#all_templates_supported' );
					supportForced = <?php echo wp_json_encode( current_theme_supports( AMP_Theme_Support::SLUG ) && ! AMP_Theme_Support::is_support_added_via_option() ); ?>;

					function isThemeSupportDisabled() {
						return ! supportForced && themeSupportDisabledInput.prop( 'checked' );
					}

					function updateFieldsetVisibility() {
						var allTemplatesSupported = 0 === allTemplatesSupportedInput.length || allTemplatesSupportedInput.prop( 'checked' );
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
	 * Render the caching settings section.
	 *
	 * @since 1.0
	 *
	 * @todo Change the messaging and description to be user-friendly and helpful.
	 */
	public function render_caching() {
		?>
		<fieldset <?php disabled( ! current_user_can( 'manage_options' ) ); ?>>
			<?php if ( AMP_Options_Manager::show_response_cache_disabled_notice() ) : ?>
				<div class="notice notice-info notice-alt inline">
					<p><?php esc_html_e( 'The post-processor cache was disabled due to detecting randomly generated content found on', 'amp' ); ?> <a href="<?php echo esc_url( get_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION, '' ) ); ?>"><?php esc_html_e( 'on this web page.', 'amp' ); ?></a></p>
					<p><?php esc_html_e( 'Randomly generated content was detected on this web page.  To avoid filling up the cache with unusable content, the AMP plugin\'s post-processor cache was automatically disabled.', 'amp' ); ?>
						<a href="<?php echo esc_url( 'https://github.com/ampproject/amp-wp/wiki/Post-Processor-Cache' ); ?>"><?php esc_html_e( 'Read more', 'amp' ); ?></a>.</p>
				</div>
			<?php endif; ?>
			<p>
				<label for="enable_response_caching">
					<input id="enable_response_caching" type="checkbox" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[enable_response_caching]' ); ?>" <?php checked( AMP_Options_Manager::get_option( 'enable_response_caching' ) ); ?>>
					<?php esc_html_e( 'Enable post-processor caching.', 'amp' ); ?>
				</label>
			</p>
			<p class="description"><?php esc_html_e( 'This will enable post-processor caching to speed up processing an AMP response after WordPress renders a template.', 'amp' ); ?></p>
		</fieldset>
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
	 * Display Settings.
	 *
	 * @since 0.6
	 */
	public function render_screen() {
		if ( ! empty( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			AMP_Options_Manager::check_supported_post_type_update_errors();
		}
		?>
		<?php if ( ! current_user_can( 'manage_options' ) ) : ?>
			<div class="notice notice-info">
				<p><?php esc_html_e( 'You do not have permission to modify these settings. They are shown here for your reference. Please contact your administrator to make changes.', 'amp' ); ?></p>
			</div>
		<?php endif; ?>
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
