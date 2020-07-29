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
 * @since 1.4
 */
class AMP_WordPress_TV_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * The URL pattern to determine if an embed URL is for this type, copied from WP_oEmbed.
	 *
	 * @see https://github.com/WordPress/wordpress-develop/blob/e13480/src/wp-includes/class-wp-oembed.php#L64
	 */
	const URL_PATTERN = '#https?://wordpress\.tv/.*#i';

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
		if ( ! preg_match( self::URL_PATTERN, $url ) ) {
			return $cache;
		}

		$modified_block_content = preg_replace( '#<script(?:\s.*?)?>.*?</script>#s', '', $cache );
		return null !== $modified_block_content ? $modified_block_content : $cache;
	}
}
