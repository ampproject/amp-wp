<?php
/**
 * Load the reader mode template.
 *
 * @package AMP
 */

$current_theme = AMP_Theme_Support::get_current_reader_theme();
$reader_themes = AMP_Theme_Support::get_reader_themes();

if ( empty( $current_theme ) || ! array_key_exists( $current_theme, $reader_themes ) ) {

	/**
	 * Queried post.
	 *
	 * @global WP_Post $post
	 */
	global $post;

	// Populate the $post without calling the_post() to prevent entering The Loop. This ensures that templates which
	// contain The Loop will still loop over the posts. Otherwise, if a template contains The Loop then calling the_post()
	// here will advance the WP_Query::$current_post to the next_post. See WP_Query::the_post().
	$post = get_queried_object(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );

	/**
	 * Fires before rendering a post in AMP.
	 *
	 * This action is not triggered when 'amp' theme support is present. Instead, you should use 'template_redirect' action and check if `is_amp_endpoint()`.
	 *
	 * @since 0.2
	 * @param int $post_id Post ID.
	 */
	do_action( 'pre_amp_render_post', get_queried_object_id() );

	require_once AMP__DIR__ . '/includes/amp-post-template-functions.php';
	amp_post_template_init_hooks();

	$amp_post_template = new AMP_Post_Template( get_queried_object_id() );
	$amp_post_template->load();
} else {

	// @todo We need to unhook all of the actions and filters that have been added by the active theme?
	// @todo Then we need to also selectively remove hooks for plugins that cause AMP validation errors.

	remove_all_actions( 'init' );
	remove_all_actions( 'after_setup_theme' );
	remove_all_actions( 'wp_enqueue_scripts' );
	remove_all_actions( 'widgets_init' );
	remove_all_actions( 'customize_register' ); // ???

	add_filter(
		'template_directory',
		function() use ( $current_theme ) {
			return AMP__DIR__ . '/themes/' . $current_theme;
		},
		PHP_INT_MAX
	);
	add_filter( 'stylesheet_directory', 'get_template_directory', PHP_INT_MAX );

	require_once get_template_directory() . '/functions.php';

	do_action( 'after_setup_theme' );

	//print_r($reader_themes[ $current_theme ]);

	echo "HELLO $current_theme!";
}
