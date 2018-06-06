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
	}

	/**
	 * Add menu.
	 */
	public function add_menu_items() {

		add_menu_page(
			__( 'AMP Options', 'amp' ),
			__( 'AMP', 'amp' ),
			'manage_options',
			AMP_Options_Manager::OPTION_NAME,
			array( $this, 'render_screen' ),
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
			'theme_support',
			__( 'Theme Support', 'amp' ),
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
			'supported_post_types',
			__( 'Post Type Support', 'amp' ),
			array( $this, 'render_post_types_support' ),
			AMP_Options_Manager::OPTION_NAME,
			'general',
			array(
				'class' => 'amp-post-type-support-field',
			)
		);

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

		$support_args = get_theme_support( 'amp' );

		$theme_support_mutable = (
			empty( $support_args )
			||
			! empty( $support_args[0]['__added_via_option'] )
		);
		if ( ! $theme_support_mutable ) {
			if ( amp_is_canonical() ) {
				$theme_support = 'native';
			} else {
				$theme_support = 'paired';
			}
		}

		$should_have_theme_support = in_array( get_template(), array( 'twentyfifteen', 'twentysixteen', 'twentyseventeen' ), true );
		?>
		<fieldset>
			<?php if ( current_theme_supports( 'amp' ) && ! $theme_support_mutable ) : ?>
				<div class="notice notice-info notice-alt inline">
					<p><?php esc_html_e( 'Your active theme has built-in AMP support.', 'amp' ); ?></p>
				</div>
			<?php elseif ( $should_have_theme_support ) : ?>
				<div class="notice notice-success notice-alt inline">
					<p><?php esc_html_e( 'Your active theme is known to work well in paired or native mode.', 'amp' ); ?></p>
				</div>
			<?php endif; ?>
			<dl>
				<dt>
					<input type="radio" id="theme_support_disabled" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[theme_support]' ); ?>" value="disabled" <?php checked( $theme_support, 'disabled' ); ?> <?php disabled( ! $theme_support_mutable ); ?>>
					<label for="theme_support_disabled">
						<strong><?php esc_html_e( 'Disabled', 'amp' ); ?></strong>
					</label>
				</dt>
				<dd>
					<?php esc_html_e( 'Display AMP responses in classic (legacy) post templates in a basic design that does not match your theme\'s templates.', 'amp' ); ?>
				</dd>
				<dt>
					<input type="radio" id="theme_support_paired" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[theme_support]' ); ?>" value="paired" <?php checked( $theme_support, 'paired' ); ?> <?php disabled( ! $theme_support_mutable ); ?>>
					<label for="theme_support_paired">
						<strong><?php esc_html_e( 'Paired', 'amp' ); ?></strong>
					</label>
				</dt>
				<dd>
					<?php esc_html_e( 'Reuse active theme\'s templates to display AMP responses, but use separate URLs for AMP. The canonical URLs for your site will not have AMP. If there are AMP validation errors encountered in the AMP response and the validation errors are not accepted for sanitization, then the AMP version will redirect to the non-AMP version.', 'amp' ); ?>
				</dd>
				<dt>
					<input type="radio" id="theme_support_native" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[theme_support]' ); ?>" value="native" <?php checked( $theme_support, 'native' ); ?> <?php disabled( ! $theme_support_mutable ); ?>>
					<label for="theme_support_native">
						<strong><?php esc_html_e( 'Native', 'amp' ); ?></strong>
					</label>
				</dt>
				<dd>
					<?php esc_html_e( 'Reuse active theme\'s templates to display AMP responses but do not use separate URLs for AMP. Your canonical URLs are AMP. Select this if you want to use AMP-specific blocks in your content. Any AMP validation errors will be automatically sanitized.', 'amp' ); ?>
				</dd>
			</dl>
		</fieldset>
		<?php
	}

	/**
	 * Post types support section renderer.
	 *
	 * @todo If dirty AMP is ever allowed, then automatically forcing sanitization in native should be able to be turned off.
	 *
	 * @since 1.0
	 */
	public function render_validation_handling() {
		?>
		<fieldset>
			<?php
			$forced_sanitization = AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( array(
				'code' => 'non_existent',
			) );
			$forced_tree_shaking = $forced_sanitization || AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( array(
				'code' => AMP_Style_Sanitizer::TREE_SHAKING_ERROR_CODE,
			) );
			?>

			<?php if ( $forced_sanitization ) : ?>
				<div class="notice notice-info notice-alt inline">
					<p>
						<?php esc_html_e( 'Your install is configured via a theme or plugin to automatically sanitize any AMP validation error that is encountered.', 'amp' ); ?>
					</p>
				</div>
				<input type="hidden" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[force_sanitization]' ); ?>" value="<?php echo AMP_Options_Manager::get_option( 'force_sanitization' ) ? 'on' : ''; ?>">
			<?php else : ?>
				<div class="amp-force-sanitize">
					<p>
						<label for="force_sanitization">
							<input id="force_sanitization" type="checkbox" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[force_sanitization]' ); ?>" <?php checked( AMP_Options_Manager::get_option( 'force_sanitization' ) ); ?>>
							<?php esc_html_e( 'Automatically accept sanitization for any AMP validation error that is encountered.', 'amp' ); ?>
						</label>
					</p>
					<p class="description">
						<?php esc_html_e( 'This will ensure your responses are always valid AMP but some important content may get stripped out (e.g. scripts).', 'amp' ); ?>
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
						<?php esc_html_e( 'AMP limits the total amount of CSS to no more than 50KB; if you have more, than it is a validation error. The need to tree shake the CSS is not done by default because in some situations (in particular for dynamic content) it can result in CSS rules being removed that are needed.', 'amp' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<script>
				jQuery( 'input[type=radio][name="amp-options[theme_support]"]' ).change( function() {
					jQuery( '.amp-validation-field' ).toggleClass( 'hidden', 'paired' !== this.value );
				} ).filter( ':checked' ).trigger( 'change' );
				jQuery( '#force_sanitization' ).change( function() {
					jQuery( '.amp-tree-shaking' ).toggleClass( 'hidden', this.checked );
				} ).trigger( 'change' );
			</script>
		</fieldset>
		<?php
	}

	/**
	 * Post types support section renderer.
	 *
	 * @since 0.6
	 */
	public function render_post_types_support() {
		$builtin_support = AMP_Post_Type_Support::get_builtin_supported_post_types();
		$element_name    = AMP_Options_Manager::OPTION_NAME . '[supported_post_types][]';
		?>
		<script>
			jQuery( 'input[type=radio][name="amp-options[theme_support]"]' ).change( function() {
				jQuery( '.amp-post-type-support-field' ).toggleClass( 'hidden', 'paired' !== this.value && 'disabled' !== this.value );
			} ).filter( ':checked' ).trigger( 'change' );
		</script>
		<fieldset>
			<?php foreach ( array_map( 'get_post_type_object', AMP_Post_Type_Support::get_eligible_post_types() ) as $post_type ) : ?>
				<?php
				$element_id = AMP_Options_Manager::OPTION_NAME . "-supported_post_types-{$post_type->name}";
				$is_builtin = in_array( $post_type->name, $builtin_support, true );
				?>
				<?php if ( $is_builtin ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $element_name ); ?>" value="<?php echo esc_attr( $post_type->name ); ?>">
				<?php endif; ?>
				<input
					type="checkbox"
					id="<?php echo esc_attr( $element_id ); ?>"
					name="<?php echo esc_attr( $element_name ); ?>"
					value="<?php echo esc_attr( $post_type->name ); ?>"
					<?php checked( true, amp_is_canonical() || post_type_supports( $post_type->name, amp_get_slug() ) ); ?>
					<?php disabled( $is_builtin ); ?>
					>
				<label for="<?php echo esc_attr( $element_id ); ?>">
					<?php echo esc_html( $post_type->label ); ?>
				</label>
				<br>
			<?php endforeach; ?>
			<p class="description">
				<?php esc_html_e( 'Select the content types that you would like to be made available in AMP.', 'amp' ); ?>
			</p>
		</fieldset>
		<?php
	}

	/**
	 * Display Settings.
	 *
	 * @since 0.6
	 */
	public function render_screen() {
		if ( ! empty( $_GET['settings-updated'] ) ) { // WPCS: CSRF ok.
			AMP_Options_Manager::check_supported_post_type_update_errors();
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( AMP_Options_Manager::OPTION_NAME );
				do_settings_sections( AMP_Options_Manager::OPTION_NAME );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
