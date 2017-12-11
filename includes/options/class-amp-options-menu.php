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
		add_action( 'admin_menu', array( $this, 'add_menu_items' ) );
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
			'post_types',
			false,
			'__return_false',
			AMP_Options_Manager::OPTION_NAME
		);
		add_settings_field(
			'supported_post_types',
			__( 'Post Type Support', 'amp' ),
			array( $this, 'render_post_types_support' ),
			AMP_Options_Manager::OPTION_NAME,
			'post_types'
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
	 * Post types support section renderer.
	 *
	 * @since 0.6
	 */
	public function render_post_types_support() {
		$builtin_support = AMP_Post_Type_Support::get_builtin_supported_post_types();
		$element_name    = AMP_Options_Manager::OPTION_NAME . '[supported_post_types][]';
		?>
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
					<?php checked( true, post_type_supports( $post_type->name, AMP_QUERY_VAR ) ); ?>
					<?php disabled( $is_builtin ); ?>
					>
				<label for="<?php echo esc_attr( $element_id ); ?>">
					<?php echo esc_html( $post_type->label ); ?>
				</label>
				<br>
			<?php endforeach; ?>
			<p class="description"><?php esc_html_e( 'Enable/disable AMP post type(s) support', 'amp' ); ?></p>
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
