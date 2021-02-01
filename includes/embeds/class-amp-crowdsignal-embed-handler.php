<?php
/**
 * Class AMP_Crowdsignal_Embed_Handler
 *
 * Handle making polls and surveys from Crowdsignal (formerly Polldaddy) AMP-compatible.
 *
 * @package AMP
 * @since 1.2
 */

/**
 * Class AMP_Crowdsignal_Embed_Handler
 *
 * @internal
 */
class AMP_Crowdsignal_Embed_Handler extends AMP_Base_Embed_Handler {

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
	 * Filter oEmbed HTML for Crowdsignal for AMP output.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @param array  $attr  Shortcode attributes.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url, $attr ) {
		$parsed_url = wp_parse_url( $url );
		if ( empty( $parsed_url['host'] ) || empty( $parsed_url['path'] ) || ! preg_match( '#(^|\.)(?P<host>polldaddy\.com|crowdsignal\.com|survey\.fm|poll\.fm)#', $parsed_url['host'], $matches ) ) {
			return $cache;
		}

		$parsed_url['host'] = $matches['host'];

		$output = '';

		// Poll oEmbed responses include noscript which can be used as the AMP response.
		if ( preg_match( '#<noscript>(.+?)</noscript>#s', $cache, $matches ) ) {
			$output = $matches[1];
		}

		if ( empty( $output ) ) {
			if ( ! empty( $attr['title'] ) ) {
				$name = $attr['title'];
			} elseif ( 'survey.fm' === $parsed_url['host'] || preg_match( '#^/s/#', $parsed_url['path'] ) ) {
				$name = __( 'View Survey', 'amp' );
			} else {
				$name = __( 'View Poll', 'amp' );
			}
			$output = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $url ), esc_html( $name ) );
		}

		return $output;
	}
}
