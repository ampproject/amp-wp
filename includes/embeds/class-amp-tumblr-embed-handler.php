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
	 * Register embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
	}

	/**
	 * Filter oEmbed HTML for Tumblr to prepare it for AMP.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( false === strpos( $parsed_url['host'], 'tumblr.com' ) ) {
			return $cache;
		}

		// @todo The iframe will not get sized properly.
		if ( preg_match( '#data-href="(?P<href>https://embed.tumblr.com/embed/post/\w+/\w+)"#', $cache, $matches ) ) {
			$cache = AMP_HTML_Utils::build_tag(
				'amp-iframe',
				[
					'width'   => $this->args['width'],
					'height'  => $this->args['height'],
					'layout'  => 'responsive',
					'sandbox' => 'allow-scripts allow-popups', // The allow-scripts is needed to allow the iframe to render; allow-popups needed to allow clicking.
					'src'     => $matches['href'],
				],
				sprintf( '<a placeholder href="%s">Tumblr</a>', $url )
			);
		}

		return $cache;
	}
}

