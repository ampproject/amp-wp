<?php
class AMPUtils {

	public static function amp_load_classes() {
		require_once(AMP__DIR__ . '/includes/templates/class-amp-post-template.php'); // this loads everything else
	}

	// Make sure the `amp` query var has an explicit value.
	// Avoids issues when filtering the deprecated `query_string` hook.
	public static function amp_force_query_var_value( $query_vars ) {
		if ( isset( $query_vars[ AMP_QUERY_VAR ] ) && '' === $query_vars[ AMP_QUERY_VAR ] ) {
			$query_vars[ AMP_QUERY_VAR ] = 1;
		}
		return $query_vars;
	}

	/**
	 * Bootstraps the AMP customizer.
	 *
	 * If the AMP customizer is enabled, initially drop the core widgets and menus panels. If the current
	 * preview page isn't flagged as an AMP template, the core panels will be re-added and the AMP panel
	 * hidden.
	 *
	 * @internal This callback must be hooked before priority 10 on 'plugins_loaded' to properly unhook
	 *           the core panels.
	 *
	 * @since 0.4
	 */
	public static function _amp_bootstrap_customizer() {
		/**
		 * Filter whether to enable the AMP template customizer functionality.
		 *
		 * @param bool $enable Whether to enable the AMP customizer. Default true.
		 */
		$amp_customizer_enabled = apply_filters( 'amp_customizer_is_enabled', true );

		if ( true === $amp_customizer_enabled ) {
			amp_init_customizer();
		}
	}

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
			$link = trailingslashit( trailingslashit( $link ) . AMP_QUERY_VAR );
		}

		return $link;
	}
}