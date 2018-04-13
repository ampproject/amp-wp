<?php
/**
 * Class AMP_Core_Theme_Sanitizer.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Class AMP_Core_Theme_Sanitizer
 *
 * Fixes up common issues in core themes and others.
 *
 * @since 1.0
 */
class AMP_Core_Theme_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Config for features needed by themes.
	 *
	 * @var array
	 */
	public $theme_features = array(
		'twentyseventeen' => array(
			'force_svg_support'              => array(),
			'force_fixed_background_support' => array(),
			// @todo Header video probably needs special support in the plugin generally.
			// @todo Header image is not styled properly. Needs layout=responsive and other things?
			// @todo Dequeue scripts and replace with AMP functionality where possible.
		),
	);

	/**
	 * Fix up core themes to do things in the AMP way.
	 *
	 * @since 1.0
	 */
	public function sanitize() {
		$theme_features = array();

		// Find theme features for core theme.
		$theme_candidates = wp_array_slice_assoc( $this->args, array( 'stylesheet', 'template' ) );
		foreach ( $theme_candidates as $theme_candidate ) {
			if ( isset( $this->theme_features[ $theme_candidate ] ) ) {
				$theme_features = $this->theme_features[ $theme_candidate ];
				break;
			}
		}

		// Allow specific theme features to be requested even if the theme is not in core.
		if ( isset( $this->args['theme_features'] ) ) {
			$theme_features = array_merge( $this->args['theme_features'], $theme_features );
		}

		foreach ( $theme_features as $theme_feature => $feature_args ) {
			if ( method_exists( $this, $theme_feature ) ) {
				call_user_func( array( $this, $theme_feature ), $feature_args );
			}
		}
	}

	/**
	 * Force SVG support, replacing no-svg class name with svg class name.
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/1af1f65a21a1a697fb5f33027497f9e5ae638453/src/wp-content/themes/twentyseventeen/assets/js/global.js#L211-L213
	 * @link https://caniuse.com/#feat=svg
	 */
	public function force_svg_support() {
		$this->dom->documentElement->setAttribute(
			'class',
			preg_replace(
				'/(^|\s)no-svg(\s|$)/',
				' svg ',
				$this->dom->documentElement->getAttribute( 'class' )
			)
		);
	}

	/**
	 * Force support for fixed background-attachment.
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/1af1f65a21a1a697fb5f33027497f9e5ae638453/src/wp-content/themes/twentyseventeen/assets/js/global.js#L215-L217
	 * @link https://caniuse.com/#feat=background-attachment
	 */
	public function force_fixed_background_support() {
		$this->dom->documentElement->setAttribute(
			'class',
			$this->dom->documentElement->getAttribute( 'class' ) . ' background-fixed'
		);
	}
}
