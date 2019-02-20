<?php
/**
 * Class AMP_Reddit_Embed_Handler
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Class AMP_Reddit_Embed_Handler
 */
class AMP_Reddit_Embed_Handler extends AMP_Base_Embed_Handler {
	/**
	 * Regex matched to produce output amp-reddit.
	 *
	 * @const string
	 */
	const URL_PATTERN = '#https?://(www\.)?reddit\.com/r/[^/]+/comments/.*#i';

	/**
	 * Register embed.
	 */
	public function register_embed() {
		wp_embed_register_handler( 'amp-reddit', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-reddit', -1 );
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
	 * Output the Reddit amp element.
	 *
	 * @param array $args parameters used for output.
	 * @return string Rendered content.
	 */
	public function render( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'url' => false,
			)
		);

		if ( empty( $args['url'] ) ) {
			return '';
		}

		// @todo Sizing is not yet correct. See <https://github.com/ampproject/amphtml/issues/11869>.
		return AMP_HTML_Utils::build_tag(
			'amp-reddit',
			array(
				'layout'         => 'responsive',
				'data-embedtype' => 'post',
				'width'          => '100',
				'height'         => '100',
				'data-src'       => $args['url'],
			)
		);
	}
}

