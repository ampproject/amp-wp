<?php
/**
 * Callbacks for adding AMP-related things to the admin.
 *
 * @package AMP
 */

/**
 * Obsolete constant for flagging when Customizer is opened for AMP.
 *
 * @deprecated
 * @var string
 */
define( 'AMP_CUSTOMIZER_QUERY_VAR', 'customize_amp' );

/**
 * Sets up the AMP template editor for the Customizer.
 *
 * If this is in AMP canonical mode, exit.
 * There's no need for the 'AMP' Customizer panel,
 * And this does not need to toggle between the AMP and normal display.
 */
function amp_init_customizer() {
	if ( amp_is_canonical() ) {
		return;
	}

	// Fire up the AMP Customizer.
	add_action( 'customize_register', array( 'AMP_Template_Customizer', 'init' ), 500 );

	// Add some basic design settings + controls to the Customizer.
	add_action( 'amp_init', array( 'AMP_Customizer_Design_Settings', 'init' ) );

	// Add a link to the Customizer.
	add_action( 'admin_menu', 'amp_add_customizer_link' );
}

/**
 * Get permalink for the first AMP-eligible post.
 *
 * @return string|null URL on success, null if none found.
 */
function amp_admin_get_preview_permalink() {
	/**
	 * Filter the post type to retrieve the latest for use in the AMP template customizer.
	 *
	 * @param string $post_type Post type slug. Default 'post'.
	 */
	$post_type = (string) apply_filters( 'amp_customizer_post_type', 'post' );

	// Make sure the desired post type is actually supported, and if so, prefer it.
	$supported_post_types = get_post_types_by_support( AMP_Post_Type_Support::SLUG );
	if ( in_array( $post_type, $supported_post_types, true ) ) {
		$supported_post_types = array_unique( array_merge( array( $post_type ), $supported_post_types ) );
	}

	// Bail if there are no supported post types.
	if ( empty( $supported_post_types ) ) {
		return null;
	}

	// If theme support is present, then bail if the singular template is not supported.
	if ( current_theme_supports( AMP_Theme_Support::SLUG ) ) {
		$supported_templates = AMP_Theme_Support::get_supportable_templates();
		if ( empty( $supported_templates['is_singular']['supported'] ) ) {
			return null;
		}
	}

	$post_ids = get_posts(
		array(
			'no_found_rows'    => true,
			'suppress_filters' => false,
			'post_status'      => 'publish',
			'post_password'    => '',
			'post_type'        => $supported_post_types,
			'posts_per_page'   => 1,
			'fields'           => 'ids',
		// @todo This should eventually do a meta_query to make sure there are none that have AMP_Post_Meta_Box::STATUS_POST_META_KEY = DISABLED_STATUS.
		)
	);

	if ( empty( $post_ids ) ) {
		return false;
	}

	$post_id = $post_ids[0];

	return amp_get_permalink( $post_id );
}

/**
 * Registers a submenu page to access the AMP template editor panel in the Customizer.
 */
function amp_add_customizer_link() {
	/** This filter is documented in includes/settings/class-amp-customizer-design-settings.php */
	if ( ! apply_filters( 'amp_customizer_is_enabled', true ) || current_theme_supports( AMP_Theme_Support::SLUG ) ) {
		return;
	}

	$menu_slug = add_query_arg(
		array(
			'autofocus[panel]' => AMP_Template_Customizer::PANEL_ID,
			'url'              => rawurlencode( amp_admin_get_preview_permalink() ),
			'return'           => rawurlencode( admin_url() ),
		),
		'customize.php'
	);

	// Add the theme page.
	add_theme_page(
		__( 'AMP', 'amp' ),
		__( 'AMP', 'amp' ),
		'edit_theme_options',
		$menu_slug
	);
}

/**
 * Registers AMP settings.
 */
function amp_add_options_menu() {
	if ( ! is_admin() ) {
		return;
	}

	/**
	 * Filter whether to enable the AMP settings.
	 *
	 * @since 0.5
	 * @param bool $enable Whether to enable the AMP settings. Default true.
	 */
	$short_circuit = apply_filters( 'amp_options_menu_is_enabled', true );

	if ( true !== $short_circuit ) {
		return;
	}

	$amp_options = new AMP_Options_Menu();
	$amp_options->init();
}

/**
 * Add custom analytics.
 *
 * This is currently only used for legacy AMP post templates.
 *
 * @since 0.5
 * @see amp_get_analytics()
 *
 * @param array $analytics Analytics.
 * @return array Analytics.
 */
function amp_add_custom_analytics( $analytics = array() ) {
	$analytics = amp_get_analytics( $analytics );

	/**
	 * Add amp-analytics tags.
	 *
	 * This filter allows you to easily insert any amp-analytics tags without needing much heavy lifting.
	 * This filter should be used to alter entries for legacy AMP templates.
	 *
	 * @since 0.4
	 *
	 * @param array   $analytics An associative array of the analytics entries we want to output. Each array entry must have a unique key, and the value should be an array with the following keys: `type`, `attributes`, `script_data`. See readme for more details.
	 * @param WP_Post $post      The current post.
	 */
	$analytics = apply_filters( 'amp_post_template_analytics', $analytics, get_queried_object() );

	return $analytics;
}

/**
 * Bootstrap AMP post meta box.
 *
 * This function must be invoked only once through the 'wp_loaded' action.
 *
 * @since 0.6
 */
function amp_post_meta_box() {
	$post_meta_box = new AMP_Post_Meta_Box();
	$post_meta_box->init();
}

/**
 * Bootstrap AMP Editor core blocks.
 */
function amp_editor_core_blocks() {
	$editor_blocks = new AMP_Editor_Blocks();
	$editor_blocks->init();
}

/**
 * Bootstrap the AMP admin pointer class.
 *
 * @since 1.0
 */
function amp_admin_pointer() {
	$admin_pointer = new AMP_Admin_Pointer();
	$admin_pointer->init();
}
