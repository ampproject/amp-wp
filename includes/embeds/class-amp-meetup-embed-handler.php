<?php
/**
 * Class AMP_Meetup_Embed_Handler
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Class AMP_Meetup_Embed_Handler
 *
 * @internal
 */
class AMP_Meetup_Embed_Handler extends AMP_Base_Embed_Handler {

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
	 * Filter oEmbed HTML for Meetup to prepare it for AMP.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( false !== strpos( $parsed_url['host'], 'meetup.com' ) ) {

			// Supply the width/height so that we don't have to make requests to look them up later.
			$cache = str_replace( '<img ', '<img width="50" height="50" ', $cache );
		}
		return $cache;
	}
}

