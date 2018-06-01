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
			'header_banner_styles'           => array(),
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

	/**
	 * Add required styles for video and image headers.
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/1af1f65a21a1a697fb5f33027497f9e5ae638453/src/wp-content/themes/twentyseventeen/style.css#L1687
	 * @link https://github.com/WordPress/wordpress-develop/blob/1af1f65a21a1a697fb5f33027497f9e5ae638453/src/wp-content/themes/twentyseventeen/style.css#L1743
	 */
	public function header_banner_styles() {

		$body = $this->dom->getElementsByTagName( 'body' )->item( 0 );
		if ( has_header_video() ) {
			$body->setAttribute(
				'class',
				$body->getAttribute( 'class' ) . ' has-header-video'
			);
		}

		$style_element = $this->dom->createElement( 'style' );
		$style_content = '
		.has-header-image .custom-header-media amp-img > img,
		.has-header-video .custom-header-media amp-video > video{
			position: fixed;
			height: auto;
			left: 50%;
			max-width: 1000%;
			min-height: 100%;
			min-width: 100%;
			min-width: 100vw; /* vw prevents 1px gap on left that 100% has */
			width: auto;
			top: 50%;
			padding-bottom: 1px; /* Prevent header from extending beyond the footer */
			-ms-transform: translateX(-50%) translateY(-50%);
			-moz-transform: translateX(-50%) translateY(-50%);
			-webkit-transform: translateX(-50%) translateY(-50%);
			transform: translateX(-50%) translateY(-50%);
		}
		.has-header-image:not(.twentyseventeen-front-page):not(.home) .custom-header-media amp-img > img {
			bottom: 0;
			position: absolute;
			top: auto;
			-ms-transform: translateX(-50%) translateY(0);
			-moz-transform: translateX(-50%) translateY(0);
			-webkit-transform: translateX(-50%) translateY(0);
			transform: translateX(-50%) translateY(0);
		}
		/* For browsers that support \'object-fit\' */
		@supports ( object-fit: cover ) {
			.has-header-image .custom-header-media amp-img > img,
			.has-header-video .custom-header-media amp-video > video,
			.has-header-image:not(.twentyseventeen-front-page):not(.home) .custom-header-media amp-img > img {
				height: 100%;
				left: 0;
				-o-object-fit: cover;
				object-fit: cover;
				top: 0;
				-ms-transform: none;
				-moz-transform: none;
				-webkit-transform: none;
				transform: none;
				width: 100%;
			}
		}
		';
		$style_element->appendChild( $this->dom->createTextNode( $style_content ) );
		$body->appendChild( $style_element );
	}
}
