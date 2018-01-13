<?php
/**
 * Class AMP_Theme_Support
 *
 * @package AMP
 */

/**
 * Class AMP_Theme_Support
 *
 * Callbacks for adding AMP-related things when theme support is added.
 */
class AMP_Theme_Support {

	const COMPONENT_SCRIPTS_PLACEHOLDER = '<!--AMP_COMPONENT_SCRIPTS_PLACEHOLDER-->';

	/**
	 * Template types.
	 *
	 * @var array
	 */
	protected static $template_types = array(
		'paged', // Deprecated.
		'index',
		'404',
		'archive',
		'author',
		'category',
		'tag',
		'taxonomy',
		'date',
		'home',
		'front_page',
		'page',
		'search',
		'single',
		'embed',
		'singular',
		'attachment',
	);

	/**
	 * Initialize.
	 */
	public static function init() {
		require_once AMP__DIR__ . '/includes/amp-post-template-actions.php';
		if ( amp_is_canonical() ) {
			$is_amp_endpoint = ( false !== get_query_var( AMP_QUERY_VAR, false ) ); // Because is_amp_endpoint() now returns true if amp_is_canonical().

			// Permanently redirect to canonical URL if the AMP URL was loaded, since canonical is now AMP.
			if ( $is_amp_endpoint ) {
				wp_safe_redirect( self::get_current_canonical_url(), 301 );
				exit;
			}
		} else {
			self::register_paired_hooks();
		}
		self::register_hooks();
	}

	/**
	 * Determines whether paired mode is available.
	 *
	 * When 'amp' theme support has not been added or canonical mode is enabled, then this returns false.
	 * Returns true when there is a template_path defined in theme support, and if a defined active_callback
	 * returns true.
	 *
	 * @return bool Whether available.
	 */
	public static function is_paired_available() {
		$support = get_theme_support( 'amp' );
		if ( empty( $support ) || amp_is_canonical() ) {
			return false;
		}

		$args = array_shift( $support );

		// @todo We might want to rename active_callback to available_callback..
		if ( isset( $args['active_callback'] ) && is_callable( $args['active_callback'] ) ) {
			return $args['active_callback']();
		}
		return true;
	}

	/**
	 * Register hooks for paired mode.
	 */
	public static function register_paired_hooks() {
		foreach ( self::$template_types as $template_type ) {
			add_filter( "{$template_type}_template_hierarchy", array( __CLASS__, 'filter_paired_template_hierarchy' ) );
		}
		add_filter( 'template_include', array( __CLASS__, 'filter_paired_template_include' ), 100 );
	}

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
		add_action( 'wp_head', array( __CLASS__, 'add_canonical_link' ), 1 );

		// @todo Add add_schemaorg_metadata(), add_analytics_data(), etc.
		// Add additional markup required by AMP <https://www.ampproject.org/docs/reference/spec#required-markup>.
		add_action( 'wp_head', array( __CLASS__, 'add_meta_charset' ), 0 );
		add_action( 'wp_head', array( __CLASS__, 'add_meta_viewport' ), 2 );
		add_action( 'wp_head', 'amp_print_boilerplate_code', 3 );
		add_action( 'wp_head', array( __CLASS__, 'add_scripts' ), 4 );
		add_action( 'wp_head', array( __CLASS__, 'add_styles' ), 5 );
		add_action( 'wp_head', 'amp_add_generator_metadata', 6 );

		/*
		 * Disable admin bar because admin-bar.css (28K) and Dashicons (48K) alone
		 * combine to surpass the 50K limit imposed for the amp-custom style.
		 */
		add_filter( 'show_admin_bar', '__return_false', 100 );

		// Start output buffering at very low priority for sake of plugins and themes that use template_redirect instead of template_include.
		add_action( 'template_redirect', array( __CLASS__, 'start_output_buffering' ), 0 );

		// @todo Add output buffering.
		// @todo Add character conversion.
	}

	/**
	 * Prepends template hierarchy with template_path for AMP paired mode templates.
	 *
	 * @see get_query_template()
	 *
	 * @param array $templates Template hierarchy.
	 * @returns array Templates.
	 */
	public static function filter_paired_template_hierarchy( $templates ) {
		$support = get_theme_support( 'amp' );
		$args    = array_shift( $support );
		if ( isset( $args['template_path'] ) ) {
			$amp_templates = array();
			foreach ( $templates as $template ) {
				$amp_templates[] = $args['template_path'] . '/' . $template;
			}
			$templates = $amp_templates;
		}
		return $templates;
	}

	/**
	 * Redirect to the non-canonical URL when the template to include is empty.
	 *
	 * This is a failsafe in case an index.php is not located in the AMP template_path,
	 * and the active_callback fails to omit a given request from being available in AMP.
	 *
	 * @param string $template Template to include.
	 * @return string Template to include.
	 */
	public static function filter_paired_template_include( $template ) {
		if ( empty( $template ) || ! self::is_paired_available() ) {
			wp_safe_redirect( self::get_current_canonical_url() );
			exit;
		}
		return $template;
	}

	/**
	 * Print meta charset tag.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#chrs
	 */
	public static function add_meta_charset() {
		echo '<meta charset="utf-8">';
	}

	/**
	 * Print meta charset tag.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#vprt
	 */
	public static function add_meta_viewport() {
		echo '<meta name="viewport" content="width=device-width,minimum-scale=1">';
	}

	/**
	 * Print AMP script and placeholder for others.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#scrpt
	 */
	public static function add_scripts() {
		echo '<script async src="https://cdn.ampproject.org/v0.js"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

		// Replaced after output buffering with all AMP component scripts.
		echo self::COMPONENT_SCRIPTS_PLACEHOLDER; // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.XSS.EscapeOutput
	}

	/**
	 * Get canonical URL for current request.
	 *
	 * @see rel_canonical()
	 * @global WP $wp
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @return string Canonical non-AMP URL.
	 */
	public static function get_current_canonical_url() {
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

		if ( ! amp_is_canonical() ) {

			// Strip endpoint.
			$url = preg_replace( ':/' . preg_quote( AMP_QUERY_VAR, ':' ) . '(?=/?(\?|#|$)):', '', $url );

			// Strip query var.
			$url = remove_query_arg( AMP_QUERY_VAR, $url );
		}

		return $url;
	}

	/**
	 * Add canonical link.
	 *
	 * Replaces `rel_canonical()` which only outputs canonical URLs for singular posts and pages.
	 * This can be removed once WP Core #18660 lands.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#canon.
	 * @link https://core.trac.wordpress.org/ticket/18660
	 */
	public static function add_canonical_link() {
		$url = self::get_current_canonical_url();
		if ( ! empty( $url ) ) {
			printf( '<link rel="canonical" href="%s">', esc_url( $url ) );
		}
	}

	/**
	 * Print Custom AMP styles.
	 *
	 * @see wp_custom_css_cb()
	 */
	public static function add_styles() {
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
	 * Determine required AMP scripts.
	 *
	 * @param string $html Output HTML.
	 * @return string Scripts to inject into the HEAD.
	 */
	public static function get_required_amp_scripts( $html ) {

		// @todo This should be integrated with the existing Sanitizer classes so that duplication is not done here.
		$amp_scripts = array(
			'amp-form' => array(
				'pattern' => '#<(form|input)\b#i',
				'source'  => 'https://cdn.ampproject.org/v0/amp-form-0.1.js',
			),
			// @todo Add more.
		);

		$scripts = '';
		foreach ( $amp_scripts as $component => $props ) {
			if ( preg_match( '#<(form|input)\b#i', $html ) ) {
				$scripts .= sprintf(
					'<script async custom-element="%s" src="%s"></script>', // phpcs:ignore WordPress.WP.EnqueuedResources, WordPress.XSS.EscapeOutput.OutputNotEscaped
					$component,
					$props['source']
				);
			}
		}

		return $scripts;
	}

	/**
	 * Start output buffering.
	 */
	public static function start_output_buffering() {
		ob_start( array( __CLASS__, 'finish_output_buffering' ) );
	}

	/**
	 * Finish output buffering.
	 *
	 * @param string $output Buffered output.
	 * @return string Finalized output.
	 */
	public static function finish_output_buffering( $output ) {
		$output = preg_replace(
			'#' . preg_quote( self::COMPONENT_SCRIPTS_PLACEHOLDER, '#' ) . '#',
			self::get_required_amp_scripts( $output ),
			$output,
			1
		);

		// @todo Add more validation checking and potentially the whitelist sanitizer.
		return $output;
	}
}
