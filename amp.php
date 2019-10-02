<?php
/**
 * Plugin Name: AMP
 * Description: Enable AMP on your WordPress site, the WordPress way.
 * Plugin URI: https://amp-wp.org
 * Author: WordPress.com VIP, XWP, Google, and contributors
 * Author URI: https://github.com/ampproject/amp-wp/graphs/contributors
 * Version: 1.0.2
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
if ( version_compare( phpversion(), '5.3.6', '<' ) ) {
	add_action( 'admin_notices', '_amp_print_php_version_admin_notice' );
	return;
}

/**
 * Print admin notice regarding DOM extension is not installed.
 *
 * @since 1.0
 */
function _amp_print_php_dom_document_notice() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'The AMP plugin requires DOM extension in PHP. Please contact your host to install this extension.', 'amp' ); ?></p>
	</div>
	<?php
}
if ( ! class_exists( 'DOMDocument' ) ) {
	add_action( 'admin_notices', '_amp_print_php_dom_document_notice' );
	return;
}

/**
 * Print admin notice regarding DOM extension is not installed.
 *
 * @since 1.0.1
 */
function _amp_print_php_missing_iconv_notice() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'The AMP plugin requires iconv extension in PHP. Please contact your host to install this extension.', 'amp' ); ?></p>
	</div>
	<?php
}
if ( ! function_exists( 'iconv' ) ) {
	add_action( 'admin_notices', '_amp_print_php_missing_iconv_notice' );
	return;
}

/**
 * Print admin notice when composer install has not been performed.
 *
 * @since 1.0
 */
function _amp_print_build_needed_notice() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'You appear to be running the AMP plugin from source. Please do `composer install && npm install && npm run build` to finish installation.', 'amp' ); ?></p>
	</div>
	<?php
}
if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) || ! file_exists( __DIR__ . '/vendor/sabberworm/php-css-parser' ) || ! file_exists( __DIR__ . '/assets/js/amp-block-editor-toggle-compiled.js' ) ) {
	add_action( 'admin_notices', '_amp_print_build_needed_notice' );
	return;
}

define( 'AMP__FILE__', __FILE__ );
define( 'AMP__DIR__', dirname( __FILE__ ) );
define( 'AMP__VERSION', '1.0.2' );

/**
 * Print admin notice if plugin installed with incorrect slug (which impacts WordPress's auto-update system).
 *
 * @since 1.0
 */
function _amp_incorrect_plugin_slug_admin_notice() {
	$actual_slug = basename( AMP__DIR__ );
	?>
	<div class="notice notice-warning">
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %1$s is the current directory name, and %2$s is the required directory name */
					__( 'You appear to have installed the AMP plugin incorrectly. It is currently installed in the <code>%1$s</code> directory, but it needs to be placed in a directory named <code>%2$s</code>. Please rename the directory. This is important for WordPress plugin auto-updates.', 'amp' ),
					$actual_slug,
					'amp'
				)
			);
			?>
		</p>
	</div>
	<?php
}
if ( 'amp' !== basename( AMP__DIR__ ) ) {
	add_action( 'admin_notices', '_amp_incorrect_plugin_slug_admin_notice' );
}

require_once AMP__DIR__ . '/includes/class-amp-autoloader.php';
AMP_Autoloader::register();

require_once AMP__DIR__ . '/back-compat/back-compat.php';
require_once AMP__DIR__ . '/includes/amp-helper-functions.php';
require_once AMP__DIR__ . '/includes/admin/functions.php';

register_activation_hook( __FILE__, 'amp_activate' );

/**
 * Handle activation of plugin.
 *
 * @since 0.2
 */
function amp_activate() {
	amp_after_setup_theme();
	if ( ! did_action( 'amp_init' ) ) {
		amp_init();
	}
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'amp_deactivate' );

/**
 * Handle deactivation of plugin.
 *
 * @since 0.2
 */
function amp_deactivate() {
	// We need to manually remove the amp endpoint.
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

// Ensure crossorigin=anonymous is added to font links.
add_filter( 'style_loader_tag', 'amp_filter_font_style_loader_tag_with_crossorigin_anonymous', 10, 4 );

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

	/**
	 * Filters whether AMP is enabled on the current site.
	 *
	 * Useful if the plugin is network activated and you want to turn it off on select sites.
	 *
	 * @since 0.2
	 */
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

	add_rewrite_endpoint( amp_get_slug(), EP_PERMALINK );

	add_filter( 'allowed_redirect_hosts', array( 'AMP_HTTP', 'filter_allowed_redirect_hosts' ) );
	AMP_HTTP::purge_amp_query_vars();
	AMP_HTTP::send_cors_headers();
	AMP_HTTP::handle_xhr_request();
	AMP_Theme_Support::init();
	AMP_Validation_Manager::init();
	add_action( 'init', array( 'AMP_Post_Type_Support', 'add_post_type_support' ), 1000 ); // After post types have been defined.

	if ( defined( 'WP_CLI' ) ) {
		WP_CLI::add_command( 'amp', new AMP_CLI() );
	}

	add_filter( 'request', 'amp_force_query_var_value' );
	add_action( 'admin_init', 'AMP_Options_Manager::register_settings' );
	add_action( 'wp_loaded', 'amp_editor_core_blocks' );
	add_action( 'wp_loaded', 'amp_post_meta_box' );
	add_action( 'wp_loaded', 'amp_editor_core_blocks' );
	add_action( 'wp_loaded', 'amp_add_options_menu' );
	add_action( 'wp_loaded', 'amp_admin_pointer' );
	add_action( 'parse_query', 'amp_correct_query_when_is_front_page' );

	// Redirect the old url of amp page to the updated url.
	add_filter( 'old_slug_redirect_url', 'amp_redirect_old_slug_to_new_url' );

	if ( class_exists( 'Jetpack' ) && ! ( defined( 'IS_WPCOM' ) && IS_WPCOM ) && version_compare( JETPACK__VERSION, '6.2-alpha', '<' ) ) {
		require_once AMP__DIR__ . '/jetpack-helper.php';
	}

	// Add actions for legacy post templates.
	add_action( 'wp', 'amp_maybe_add_actions' );

	/*
	 * Broadcast plugin updates.
	 * Note that AMP_Options_Manager::get_option( 'version', '0.0' ) cannot be used because
	 * version was new option added, and in that case default would never be used for a site
	 * upgrading from a version prior to 1.0. So this is why get_option() is currently used.
	 */
	$options     = get_option( AMP_Options_Manager::OPTION_NAME, array() );
	$old_version = isset( $options['version'] ) ? $options['version'] : '0.0';
	if ( AMP__VERSION !== $old_version ) {
		/**
		 * Triggers when after amp_init when the plugin version has updated.
		 *
		 * @param string $old_version Old version.
		 */
		do_action( 'amp_plugin_update', $old_version );
		AMP_Options_Manager::update_option( 'version', AMP__VERSION );
	}
}

/**
 * Make sure the `amp` query var has an explicit value.
 *
 * This avoids issues when filtering the deprecated `query_string` hook.
 *
 * @since 0.3.3
 *
 * @param array $query_vars Query vars.
 * @return array Query vars.
 */
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
 * @deprecated This function is not used when 'amp' theme support is added.
 * @global WP_Query $wp_query
 * @since 0.2
 * @return void
 */
function amp_maybe_add_actions() {

	// Short-circuit when theme supports AMP, as everything is handled by AMP_Theme_Support.
	if ( current_theme_supports( AMP_Theme_Support::SLUG ) ) {
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
 * Whether this is in 'canonical mode'.
 *
 * Themes can register support for this with `add_theme_support( AMP_Theme_Support::SLUG )`:
 *
 *      add_theme_support( AMP_Theme_Support::SLUG );
 *
 * This will serve templates in native AMP, allowing you to use AMP components in your theme templates.
 * If you want to make available in paired mode, where templates are served in AMP or non-AMP documents, do:
 *
 *      add_theme_support( AMP_Theme_Support::SLUG, array(
 *          'paired' => true,
 *      ) );
 *
 * Paired mode is also implied if you define a template_dir:
 *
 *      add_theme_support( AMP_Theme_Support::SLUG, array(
 *          'template_dir' => 'amp',
 *      ) );
 *
 * If you want to have AMP-specific templates in addition to serving native AMP, do:
 *
 *      add_theme_support( AMP_Theme_Support::SLUG, array(
 *          'paired'       => false,
 *          'template_dir' => 'amp',
 *      ) );
 *
 * If you want to force AMP to always be served on a given template, you can use the templates_supported arg,
 * for example to always serve the Category template in AMP:
 *
 *      add_theme_support( AMP_Theme_Support::SLUG, array(
 *          'templates_supported' => array(
 *              'is_category' => true,
 *          ),
 *      ) );
 *
 * Or if you want to force AMP to be used on all templates:
 *
 *      add_theme_support( AMP_Theme_Support::SLUG, array(
 *          'templates_supported' => 'all',
 *      ) );
 *
 * @see AMP_Theme_Support::read_theme_support()
 * @return boolean Whether this is in AMP 'canonical' mode, that is whether it is native and there is not separate AMP URL current URL.
 */
function amp_is_canonical() {
	if ( ! current_theme_supports( AMP_Theme_Support::SLUG ) ) {
		return false;
	}

	$args = AMP_Theme_Support::get_theme_support_args();
	if ( isset( $args['paired'] ) ) {
		return empty( $args['paired'] );
	}

	// If there is a template_dir, then paired mode is implied.
	return empty( $args['template_dir'] );
}

/**
 * Load classes.
 *
 * @since 0.2
 * @deprecated As of 0.6 since autoloading is now employed.
 */
function amp_load_classes() {
	_deprecated_function( __FUNCTION__, '0.6' );
}

/**
 * Add frontend actions.
 *
 * @since 0.2
 */
function amp_add_frontend_actions() {
	add_action( 'wp_head', 'amp_add_amphtml_link' );
}

/**
 * Add post template actions.
 *
 * @since 0.2
 * @deprecated This function is not used when 'amp' theme support is added.
 */
function amp_add_post_template_actions() {
	require_once AMP__DIR__ . '/includes/amp-post-template-actions.php';
	require_once AMP__DIR__ . '/includes/amp-post-template-functions.php';
	amp_post_template_init_hooks();
}

/**
 * Add action to do post template rendering at template_redirect action.
 *
 * @since 0.2
 * @since 1.0 The amp_render() function is called at template_redirect action priority 11 instead of priority 10.
 * @deprecated This function is not used when 'amp' theme support is added.
 */
function amp_prepare_render() {
	add_action( 'template_redirect', 'amp_render', 11 );
}

/**
 * Render AMP for queried post.
 *
 * @since 0.1
 * @deprecated This function is not used when 'amp' theme support is added.
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
 * @deprecated This function is not used when 'amp' theme support is added.
 *
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
	 * This action is not triggered when 'amp' theme support is present. Instead, you should use 'template_redirect' action and check if `is_amp_endpoint()`.
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
 * So this enables themes to `add_theme_support( AMP_Theme_Support::SLUG )`.
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
 *
 * If post slug is updated the amp page with old post slug will be redirected to the updated url.
 *
 * @since 0.5
 * @deprecated This function is irrelevant when 'amp' theme support is added.
 *
 * @param string $link New URL of the post.
 * @return string URL to be redirected.
 */
function amp_redirect_old_slug_to_new_url( $link ) {

	if ( is_amp_endpoint() && ! amp_is_canonical() ) {
		if ( current_theme_supports( AMP_Theme_Support::SLUG ) ) {
			$link = add_query_arg( amp_get_slug(), '', $link );
		} else {
			$link = trailingslashit( trailingslashit( $link ) . amp_get_slug() );
		}
	}

	return $link;
}
