<?php
/**
 * Class AMP_Tumblr_Embed_Handler
 *
 * @package AMP
 */

/**
 * Class AMP_Tumblr_Embed_Handler
 */
class AMP_Tumblr_Embed_Handler extends AMP_Base_Embed_Handler {
	/**
	 * Regex matched to produce output amp-iframe.
	 *
	 * @const string
	 */
	const URL_PATTERN = '#https?://(.+)\.tumblr\.com/post/(.*)#i';

	/**
	 * Register embed.
	 */
	public function register_embed() {
		wp_embed_register_handler( 'tumblr', self::URL_PATTERN, array( $this, 'oembed' ) );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'tumblr' );
	}

	/**
	 * Embed found with matching URL callback.
	 *
	 * @param array $matches URL regex matches.
	 * @param array $attr    Additional parameters.
	 * @param array $url     URL.
	 */
	public function oembed( $matches, $attr, $url ) {
		return $this->render( array( 'url' => $url ) );
	}

	/**
	 * Output the Tumblr iframe.
	 *
	 * @param array $args parameters used for output.
	 */
	public function render( $args ) {
		$args = wp_parse_args( $args, array(
			'url' => false,
		) );

		if ( empty( $args['url'] ) ) {
			return '';
		}

		// Must enforce https for amp-iframe, but editors can supply either on desktop.
		$args['url'] = str_replace( 'http://', 'https://', $args['url'] );

		return AMP_HTML_Utils::build_tag(
			'amp-iframe',
			array(
				'width'  => $this->args['width'],
				'height' => $this->args['height'],
				'src'    => $args['url'],
			)
		);
	}
}

