<?php
/**
 * Class AMP_Canonical_Mode_Actions
 *
 * @package AMP
 */

/**
 * Class AMP_Canonical_Mode_Actions
 *
 * Callbacks for adding AMP-related things to the theme when in canonical mode.
 */
class AMP_Canonical_Mode_Actions {

	/**
	 * Register hooks.
	 */
	public static function register_hooks() {

		// Remove core actions which are invalid AMP.
		remove_action( 'wp_head', 'locale_stylesheet' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_print_styles', 8 );
		remove_action( 'wp_head', 'wp_print_head_scripts', 9 );
		remove_action( 'wp_head', 'wp_custom_css_cb', 101 );
		remove_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		// Replace core's canonical link functionality with one that outputs links for non-singular queries as well. See WP Core #18660.
		remove_action( 'wp_head', 'rel_canonical' );
		add_action( 'wp_head', array( __CLASS__, 'rel_canonical' ), 1 );

		// Add additional markup required by AMP <https://www.ampproject.org/docs/reference/spec#required-markup>.
		add_action( 'wp_head', array( __CLASS__, 'print_meta_charset' ), 0 );
		add_action( 'wp_head', array( __CLASS__, 'print_meta_viewport' ), 2 );
		add_action( 'wp_head', array( __CLASS__, 'print_amp_boilerplate_code' ), 3 );
		add_action( 'wp_head', array( __CLASS__, 'print_amp_scripts' ), 4 );
		add_action( 'wp_head', array( __CLASS__, 'print_amp_custom_style' ), 5 );

		add_action( 'admin_bar_init', array( __CLASS__, 'admin_bar_init' ) );
		add_theme_support( 'admin-bar', array( 'callback' => '__return_false' ) );

		// @todo Add output buffering.
		// @todo Add character conversion.
	}

	/**
	 * Print meta charset tag.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#chrs
	 */
	public static function print_meta_charset() {
		echo '<meta charset="utf-8">';
	}

	/**
	 * Print meta charset tag.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#vprt
	 */
	public static function print_meta_viewport() {
		echo '<meta name="viewport" content="width=device-width,minimum-scale=1">';
	}

	/**
	 * Print AMP boilerplate code.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#boilerplate
	 */
	public static function print_amp_boilerplate_code() {
		echo '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>';
		echo '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';
	}

	/**
	 * Print AMP script and placeholder for others.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#scrpt
	 */
	public static function print_amp_scripts() {
		echo '<script async src="https://cdn.ampproject.org/v0.js"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		echo '<!--AMP_PLUGIN_SCRIPTS_PLACEHOLDER-->'; // Replaced after output buffering with all AMP component scripts.
	}

	/**
	 * Add canonical link.
	 *
	 * Replaces `rel_canonical()` which only outputs canonical URLs for singular posts and pages.
	 * This can be removed once WP Core #18660 lands.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#canon.
	 * @link https://core.trac.wordpress.org/ticket/18660
	 *
	 * @see rel_canonical()
	 * @global WP $wp
	 * @global WP_Rewrite $wp_rewrite
	 */
	public static function rel_canonical() {
		global $wp, $wp_rewrite;

		$url = null;
		if ( is_singular() ) {
			$url = wp_get_canonical_url();
		}

		// For non-singular queries, make use of the request URI and public query vars to determine canonical URL.
		if ( empty( $url ) ) {
			$added_query_vars = $wp->query_vars;
			if ( ! $wp_rewrite->permalink_structure || empty( $wp->request ) ) {
				$url = home_url( '/' );
			} else {
				$url = home_url( user_trailingslashit( $wp->request ) );
				parse_str( $wp->matched_query, $matched_query_vars );
				foreach ( $wp->query_vars as $key => $value ) {

					// Remove query vars that were matched in the rewrite rules for the request.
					if ( isset( $matched_query_vars[ $key ] ) ) {
						unset( $added_query_vars[ $key ] );
					}
				}
			}
		}

		if ( ! empty( $added_query_vars ) ) {
			$url = add_query_arg( $added_query_vars, $url );
		}

		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	}

	/**
	 * Print Custom AMP styles.
	 *
	 * @see wp_custom_css_cb()
	 */
	public static function print_amp_custom_style() {
		echo '<style amp-custom>';

		// @todo Grab source of all enqueued styles and concatenate here?
		// @todo Print contents of get_locale_stylesheet_uri()?
		// @todo Allow this to be filtered after output buffering is complete so additional styles can be added by widgets and other components just-in-time?
		$path = get_template_directory() . '/style.css'; // @todo Honor filter in get_stylesheet_directory_uri()? Style must be local.
		$css  = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- It's not a remote file.
		echo wp_strip_all_tags( $css ); // WPCS: XSS OK.

		// Implement AMP version of wp_custom_css_cb().
		$custom_css = trim( wp_get_custom_css() );
		if ( ! empty( $custom_css ) ) {
			echo '/* start:wp_get_custom_css */';
			echo wp_strip_all_tags( wp_get_custom_css() ); // WPCS: XSS OK.
			echo '/* end:wp_get_custom_css */';
		}
		echo '</style>';
	}

	/**
	 * Fix up admin bar.
	 */
	public static function admin_bar_init() {
		remove_action( 'wp_head', 'wp_admin_bar_header' );
		add_action( 'admin_bar_menu', array( __CLASS__, 'remove_customize_support_script' ), 100 ); // See WP_Admin_Bar::add_menus().
		add_filter( 'body_class', array( __CLASS__, array( __CLASS__, 'filter_body_class_to_force_customize_support' ) ) );
	}

	/**
	 * Let the body class include customize-support by default since support script won't be able to dynamically add it.
	 *
	 * @see wp_customize_support_script()
	 *
	 * @param array $classes Body classes.
	 * @return array Classes.
	 */
	public static function filter_body_class_to_force_customize_support( $classes ) {
		$i = array_search( 'no-customize-support', $classes, true );
		if ( false !== $i ) {
			array_splice( $classes, $i, 1 );
		}
		$classes[] = 'customize-support';
		return $classes;
	}

	/**
	 * Remove Customizer support script.
	 *
	 * @see WP_Admin_Bar::add_menus()
	 * @see wp_customize_support_script()
	 */
	public static function remove_customize_support_script() {
		remove_action( 'wp_before_admin_bar_render', 'wp_customize_support_script' );
	}
}
