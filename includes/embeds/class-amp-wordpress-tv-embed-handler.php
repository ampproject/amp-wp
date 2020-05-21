<?php
/**
 * Class AMP_WordPress_TV_Embed_Handler
 *
 * @package AMP
 * @since 1.4
 */

/**
 * Class AMP_WordPress_TV_Embed_Handler
 *
 * This sanitizes embeds both for WordPress.tv and for VideoPress (which use the same underlying infrastructure).
 *
 * @since 1.4
 */
class AMP_WordPress_TV_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', [ $this, 'filter_oembed_html' ], 10, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_oembed_html' ], 10 );
	}

	/**
	 * Filters the oembed HTML to make it valid AMP.
	 *
	 * @param mixed  $cache The cached rendered markup.
	 * @param string $url   The embed URL.
	 * @return string The filtered embed markup.
	 */
	public function filter_oembed_html( $cache, $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! in_array( $host, [ 'wordpress.tv', 'videopress.com' ], true ) ) {
			return $cache;
		}

		$modified_block_content = preg_replace( '#<script(?:\s.*?)?>.*?</script>#s', '', $cache );
		return null !== $modified_block_content ? $modified_block_content : $cache;
	}
}
