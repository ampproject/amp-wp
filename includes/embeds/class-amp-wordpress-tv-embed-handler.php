<?php
/**
 * Class AMP_WordPress_TV_Embed_Handler
 *
 * @package AMP
 * @since 1.4
 */

use AmpProject\Dom\Document;

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
		// Not implemented.
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		// Not implemented.
	}

	/**
	 * Sanitize all WordPress TV <iframe> tags to <amp-iframe>.
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$nodes = $dom->xpath->query( '//iframe[ starts-with( @src, "https://video.wordpress.com/embed/" ) ]' );

		foreach ( $nodes as $node ) {
			if ( ! $this->is_raw_embed( $node ) ) {
				continue;
			}
			$this->sanitize_raw_embed( $node );
		}
	}

	/**
	 * Determine if the node has already been sanitized.
	 *
	 * @param DOMElement $node The DOMNode.
	 * @return bool Whether the node is a raw embed.
	 */
	protected function is_raw_embed( DOMElement $node ) {
		return $node->parentNode && 'amp-iframe' !== $node->parentNode->nodeName;
	}

	/**
	 * Make WordPress TV embed AMP compatible.
	 *
	 * @param DOMElement $iframe_node The node to make AMP compatible.
	 */
	private function sanitize_raw_embed( DOMElement $iframe_node ) {
		$next_sibling = $iframe_node->nextSibling;

		if ( $next_sibling && 'script' === $next_sibling->nodeName ) {
			$iframe_node->parentNode->removeChild( $next_sibling );
		}

		$iframe_node->setAttribute( 'layout', 'responsive' );
		$this->maybe_unwrap_p_element( $iframe_node );
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
