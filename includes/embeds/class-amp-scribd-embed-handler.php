<?php
/**
 * Class AMP_Scribd_Embed_Handler
 *
 * @package AMP
 * @since 1.4
 */

/**
 * Class AMP_Scribd_Embed_Handler
 */
class AMP_Scribd_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Registers embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 2 );
	}

	/**
	 * Unregisters embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ] );
	}

	/**
	 * Filter oEmbed HTML for Scribd to be AMP compatible.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		if ( ! in_array( wp_parse_url( $url, PHP_URL_HOST ), [ 'scribd.com', 'www.scribd.com' ], true ) ) {
			return $cache;
		}

		return $this->sanitize_iframe( $cache );
	}

	/**
	 * Retrieves iframe element from HTML string and amends or appends the correct sandbox permissions.
	 *
	 * @param string $html HTML string.
	 * @return string iframe with correct sandbox permissions.
	 */
	private function sanitize_iframe( $html ) {
		return preg_replace_callback(
			'#^.*<iframe(?P<iframe_attributes>[^>]+?)></iframe>.*$#s',
			function ( $matches ) {
				$attrs = $matches['iframe_attributes'];

				// Amend the required keywords to the iframe's sandbox.
				$sandbox  = 'allow-popups allow-scripts';
				$replaced = 0;
				$attrs    = preg_replace(
					'#(?<=\ssandbox=["\'])#',
					"{$sandbox} ", // whitespace is necessary to separate prior permissions.
					$attrs,
					1,
					$replaced
				);

				// If no sandbox attribute was found, then add the attribute.
				if ( 0 === $replaced ) {
					$attrs .= sprintf( ' sandbox="%s"', $sandbox );
				}

				// The iframe sanitizer will convert this into an amp-iframe.
				return "<iframe{$attrs}></iframe>";
			},
			$html
		);
	}
}
