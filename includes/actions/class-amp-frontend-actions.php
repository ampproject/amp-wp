<?php
/**
 * Class AMP_Frontend_Actions
 *
 * @package AMP
 */

/**
 * Class AMP_Frontend_Actions
 *
 * Callbacks for adding AMP-related things to the main theme in non-canonical mode theme.
 */
class AMP_Frontend_Actions {

	/**
	 * Register hooks.
	 */
	public static function register_hooks() {
		add_action( 'wp_head', 'AMP_Frontend_Actions::add_canonical' );
	}

	/**
	 * Add canonical link.
	 */
	public static function add_canonical() {
		if ( false === apply_filters( 'add_canonical_link', true ) ) {
			return;
		}
		$amp_url = self::get_current_amphtml_url();
		if ( ! empty( $amp_url ) ) {
			printf( '<link rel="amphtml" href="%s" />', esc_url( $amp_url ) );
		}
	}

	/**
	 * Get the amphtml URL for the current request.
	 *
	 * @todo Put this function in includes/amp-helper-functions.php?
	 * @return string|null URL or null if AMP version is not available.
	 */
	public static function get_current_amphtml_url() {
		if ( is_singular() ) {
			return amp_get_permalink( get_queried_object_id() );
		}

		// @todo Get callback from get_theme_support( 'amp' ) to determine whether AMP is allowed for current request. See <https://github.com/Automattic/amp-wp/issues/849>.
		if ( ! current_theme_supports( 'amp' ) ) {
			return null;
		}

		$amp_url  = '';
		$home_url = wp_parse_url( home_url() );
		if ( isset( $home_url['scheme'] ) ) {
			$amp_url .= $home_url['scheme'] . ':';
		}
		$amp_url .= '//' . $home_url['host'];
		if ( ! empty( $home_url['port'] ) ) {
			$amp_url .= ':' . $home_url['port'];
		}
		$amp_url .= esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		return add_query_arg( AMP_QUERY_VAR, '', $amp_url );
	}
}
