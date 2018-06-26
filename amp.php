<?php
/**
 * Plugin Name: AMP
 * Description: Add AMP support to your WordPress site.
 * Plugin URI: https://github.com/automattic/amp-wp
 * Author: WordPress.com VIP, XWP, Google, and contributors
 * Author URI: https://github.com/Automattic/amp-wp/graphs/contributors
 * Version: 0.7.2
 * Text Domain: amp
 * Domain Path: /languages/
 * License: GPLv2 or later
 *
 * @package AMP
 */

/**
 * Print admin notice regarding having an old version of PHP.
 *
 * @since 0.7
 */
function _amp_print_php_version_admin_notice() {
	?>
	<div class="notice notice-error">
			<p><?php esc_html_e( 'The AMP plugin requires PHP 5.3+. Please contact your host to update your PHP version.', 'amp' ); ?></p>
		</div>
	<?php
}
if ( version_compare( phpversion(), '5.3', '<' ) ) {
	add_action( 'admin_notices', '_amp_print_php_version_admin_notice' );
	return;
}

define( 'AMP__FILE__', __FILE__ );
define( 'AMP__DIR__', dirname( __FILE__ ) );
define( 'AMP__VERSION', '0.7.2' );

require_once AMP__DIR__ . '/includes/class-amp-autoloader.php';
AMP_Autoloader::register();

require_once AMP__DIR__ . '/back-compat/back-compat.php';
require_once AMP__DIR__ . '/includes/amp-helper-functions.php';
require_once AMP__DIR__ . '/includes/admin/functions.php';

register_activation_hook( __FILE__, 'amp_activate' );
function amp_activate() {
	amp_after_setup_theme();
	if ( ! did_action( 'amp_init' ) ) {
		amp_init();
	}
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'amp_deactivate' );
function amp_deactivate() {
	// We need to manually remove the amp endpoint
	global $wp_rewrite;
	foreach ( $wp_rewrite->endpoints as $index => $endpoint ) {
		if ( amp_get_slug() === $endpoint[1] ) {
			unset( $wp_rewrite->endpoints[ $index ] );
			break;
		}
	}

	flush_rewrite_rules();
}

/*
 * Register AMP scripts regardless of whether AMP is enabled or it is the AMP endpoint
 * for the sake of being able to use AMP components on non-AMP documents ("dirty AMP").
 */
add_action( 'wp_default_scripts', 'amp_register_default_scripts' );

// Ensure async and custom-element/custom-template attributes are present on script tags.
add_filter( 'script_loader_tag', 'amp_filter_script_loader_tag', PHP_INT_MAX, 2 );

/**
 * Set up AMP.
 *
 * This function must be invoked through the 'after_setup_theme' action to allow
 * the AMP setting to declare the post types support earlier than plugins/theme.
 *
 * @since 0.6
 */
function amp_after_setup_theme() {
	amp_get_slug(); // Ensure AMP_QUERY_VAR is set.

	if ( false === apply_filters( 'amp_is_enabled', true ) ) {
		return;
	}

	add_action( 'init', 'amp_init', 0 ); // Must be 0 because widgets_init happens at init priority 1.
}
add_action( 'after_setup_theme', 'amp_after_setup_theme', 5 );

/**
 * Init AMP.
 *
 * @since 0.1
 */
function amp_init() {

	/**
	 * Triggers on init when AMP plugin is active.
	 *
	 * @since 0.3
	 */
	do_action( 'amp_init' );

	load_plugin_textdomain( 'amp', false, plugin_basename( AMP__DIR__ ) . '/languages' );

	add_rewrite_endpoint( amp_get_slug(), EP_PERMALINK );

	AMP_Theme_Support::init();
	AMP_Post_Type_Support::add_post_type_support();
	add_filter( 'request', 'amp_force_query_var_value' );
	add_action( 'admin_init', 'AMP_Options_Manager::register_settings' );
	add_action( 'wp_loaded', 'amp_post_meta_box' );
	add_action( 'wp_loaded', 'amp_add_options_menu' );
	add_action( 'parse_query', 'amp_correct_query_when_is_front_page' );

	// Redirect the old url of amp page to the updated url.
	add_filter( 'old_slug_redirect_url', 'amp_redirect_old_slug_to_new_url' );

	if ( class_exists( 'Jetpack' ) && ! ( defined( 'IS_WPCOM' ) && IS_WPCOM ) && version_compare( JETPACK__VERSION, '6.2-alpha', '<' ) ) {
		require_once AMP__DIR__ . '/jetpack-helper.php';
	}

	// Add actions for legacy post templates.
	add_action( 'wp', 'amp_maybe_add_actions' );
}

// Make sure the `amp` query var has an explicit value.
// Avoids issues when filtering the deprecated `query_string` hook.
function amp_force_query_var_value( $query_vars ) {
	if ( isset( $query_vars[ amp_get_slug() ] ) && '' === $query_vars[ amp_get_slug() ] ) {
		$query_vars[ amp_get_slug() ] = 1;
	}
	return $query_vars;
}

/**
 * Conditionally add AMP actions or render the 'paired mode' template(s).
 *
 * If the request is for an AMP page and this is in 'canonical mode,' redirect to the non-AMP page.
 * It won't need this plugin's template system, nor the frontend actions like the 'rel' link.
 *
 * @global WP_Query $wp_query
 * @since 0.2
 * @return void
 */
function amp_maybe_add_actions() {

	// Short-circuit when theme supports AMP, as everything is handled by AMP_Theme_Support.
	if ( current_theme_supports( 'amp' ) ) {
		return;
	}

	// The remaining logic here is for paired mode running in themes that don't support AMP, the template system in AMP<=0.6.
	global $wp_query;
	if ( ! ( is_singular() || $wp_query->is_posts_page ) || is_feed() ) {
		return;
	}

	$is_amp_endpoint = is_amp_endpoint();

	/**
	 * Queried post object.
	 *
	 * @var WP_Post $post
	 */
	$post = get_queried_object();
	if ( ! post_supports_amp( $post ) ) {
		if ( $is_amp_endpoint ) {
			wp_safe_redirect( get_permalink( $post->ID ), 302 ); // Temporary redirect because AMP may be supported in future.
			exit;
		}
		return;
	}

	if ( $is_amp_endpoint ) {
		amp_prepare_render();
	} else {
		amp_add_frontend_actions();
	}
}

/**
 * Fix up WP_Query for front page when amp query var is present.
 *
 * Normally the front page would not get served if a query var is present other than preview, page, paged, and cpage.
 *
 * @since 0.6
 * @see WP_Query::parse_query()
 * @link https://github.com/WordPress/wordpress-develop/blob/0baa8ae85c670d338e78e408f8d6e301c6410c86/src/wp-includes/class-wp-query.php#L951-L971
 *
 * @param WP_Query $query Query.
 */
function amp_correct_query_when_is_front_page( WP_Query $query ) {
	$is_front_page_query = (
		$query->is_main_query()
		&&
		$query->is_home()
		&&
		// Is AMP endpoint.
		false !== $query->get( amp_get_slug(), false )
		&&
		// Is query not yet fixed uo up to be front page.
		! $query->is_front_page()
		&&
		// Is showing pages on front.
		'page' === get_option( 'show_on_front' )
		&&
		// Has page on front set.
		get_option( 'page_on_front' )
		&&
		// See line in WP_Query::parse_query() at <https://github.com/WordPress/wordpress-develop/blob/0baa8ae/src/wp-includes/class-wp-query.php#L961>.
		0 === count( array_diff( array_keys( wp_parse_args( $query->query ) ), array( amp_get_slug(), 'preview', 'page', 'paged', 'cpage' ) ) )
	);
	if ( $is_front_page_query ) {
		$query->is_home     = false;
		$query->is_page     = true;
		$query->is_singular = true;
		$query->set( 'page_id', get_option( 'page_on_front' ) );
	}
}

/**
 * Whether this is in 'canonical mode.'
 *
 * Themes can register support for this with `add_theme_support( 'amp' )`.
 * Then, this will change the plugin from 'paired mode,' and it won't use its own templates.
 * Nor output frontend markup like the 'rel' link. If the theme registers support for AMP with:
 * `add_theme_support( 'amp', array( 'template_dir' => 'my-amp-templates' ) )`
 * it will retain 'paired mode.
 *
 * @return boolean Whether this is in AMP 'canonical mode'.
 */
function amp_is_canonical() {
	$support = get_theme_support( 'amp' );
	if ( true === $support ) {
		return true;
	}
	if ( is_array( $support ) ) {
		$args = array_shift( $support );
		if ( empty( $args['template_dir'] ) ) {
			return true;
		}
	}
	return false;
}

function amp_load_classes() {
	_deprecated_function( __FUNCTION__, '0.6' );
}

function amp_add_frontend_actions() {
	require_once AMP__DIR__ . '/includes/amp-frontend-actions.php';
}

function amp_add_post_template_actions() {
	require_once AMP__DIR__ . '/includes/amp-post-template-actions.php';
	require_once AMP__DIR__ . '/includes/amp-post-template-functions.php';
	amp_post_template_init_hooks();
}

function amp_prepare_render() {
	add_action( 'template_redirect', 'amp_render' );
}

/**
 * Render AMP for queried post.
 *
 * @since 0.1
 */
function amp_render() {
	// Note that queried object is used instead of the ID so that the_preview for the queried post can apply.
	$post = get_queried_object();
	if ( $post instanceof WP_Post ) {
		amp_render_post( $post );
		exit;
	}
}

/**
 * Render AMP post template.
 *
 * @since 0.5
 * @param WP_Post|int $post Post.
 * @global WP_Query $wp_query
 */
function amp_render_post( $post ) {
	global $wp_query;

	if ( ! ( $post instanceof WP_Post ) ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}
	}
	$post_id = $post->ID;

	/*
	 * If amp_render_post is called directly outside of the standard endpoint, is_amp_endpoint() will return false,
	 * which is not ideal for any code that expects to run in an AMP context.
	 * Let's force the value to be true while we render AMP.
	 */
	$was_set = isset( $wp_query->query_vars[ amp_get_slug() ] );
	if ( ! $was_set ) {
		$wp_query->query_vars[ amp_get_slug() ] = true;
	}

	// Prevent New Relic from causing invalid AMP responses due the NREUM script it injects after the meta charset.
	if ( extension_loaded( 'newrelic' ) ) {
		newrelic_disable_autorum();
	}

	/**
	 * Fires before rendering a post in AMP.
	 *
	 * @since 0.2
	 *
	 * @param int $post_id Post ID.
	 */
	do_action( 'pre_amp_render_post', $post_id );

	amp_add_post_template_actions();
	$template = new AMP_Post_Template( $post );
	$template->load();

	if ( ! $was_set ) {
		unset( $wp_query->query_vars[ amp_get_slug() ] );
	}
}

/**
 * Bootstraps the AMP customizer.
 *
 * Uses the priority of 12 for the 'after_setup_theme' action.
 * Many themes run `add_theme_support()` on the 'after_setup_theme' hook, at the default priority of 10.
 * And that function's documentation suggests adding it to that action.
 * So this enables themes to `add_theme_support( 'amp' )`.
 * And `amp_init_customizer()` will be able to recognize theme support by calling `amp_is_canonical()`.
 *
 * @since 0.4
 */
function _amp_bootstrap_customizer() {
	add_action( 'after_setup_theme', 'amp_init_customizer', 12 );
}
add_action( 'plugins_loaded', '_amp_bootstrap_customizer', 9 ); // Should be hooked before priority 10 on 'plugins_loaded' to properly unhook core panels.

/**
 * Redirects the old AMP URL to the new AMP URL.
 * If post slug is updated the amp page with old post slug will be redirected to the updated url.
 *
 * @param  string $link New URL of the post.
 *
 * @return string $link URL to be redirected.
 */
function amp_redirect_old_slug_to_new_url( $link ) {

	if ( is_amp_endpoint() ) {
		$link = trailingslashit( trailingslashit( $link ) . amp_get_slug() );
	}

	return $link;
}
