<?php
/**
 * Class AMP_Issuu_Embed_Handler
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Class AMP_Issuu_Embed_Handler
 *
 * @internal
 */
class AMP_Issuu_Embed_Handler extends AMP_Base_Embed_Handler {
	/**
	 * Regex matched to produce output amp-iframe.
	 *
	 * @const string
	 */
	const URL_PATTERN = '#https?://(www\.)?issuu\.com/.+/docs/.+#i';

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 3 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
	}

	/**
	 * Filter oEmbed HTML for Meetup to prepare it for AMP.
	 *
	 * @param mixed  $return The oEmbed HTML.
	 * @param string $url    The attempted embed URL.
	 * @param array  $attr   Attributes.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $return, $url, $attr ) {
		$parsed_url = wp_parse_url( $url );
		if ( false !== strpos( $parsed_url['host'], 'issuu.com' ) ) {
			if ( preg_match( '/width\s*:\s*(\d+)px/', $return, $matches ) ) {
				$attr['width'] = $matches[1];
			}
			if ( preg_match( '/height\s*:\s*(\d+)px/', $return, $matches ) ) {
				$attr['height'] = $matches[1];
			}

			$return = AMP_HTML_Utils::build_tag(
				'amp-iframe',
				[
					'width'   => $attr['width'],
					'height'  => $attr['height'],
					'src'     => $url,
					'sandbox' => 'allow-scripts allow-same-origin',
				]
			);
		}
		return $return;
	}
}

