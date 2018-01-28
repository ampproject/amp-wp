<?php
/**
 * Class AMP_Tumblr_Embed_Handler
 *
 * @package AMP
 * @since 0.7
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
		wp_embed_register_handler( 'tumblr', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'tumblr', -1 );
	}

	/**
	 * Embed found with matching URL callback.
	 *
	 * @param array $matches URL regex matches.
	 * @param array $attr    Additional parameters.
	 * @param array $url     URL.
	 * @return string Embed.
	 */
	public function oembed( $matches, $attr, $url ) {
		return $this->render( array( 'url' => $url ) );
	}

	/**
	 * Output the Tumblr iframe.
	 *
	 * @param array $args parameters used for output.
	 * @return string Rendered content.
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

