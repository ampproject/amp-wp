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
	 * Base URL used for identifying embeds.
	 *
	 * @var string
	 */
	const BASE_EMBED_URL = 'https://video.wordpress.com/embed/';

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
		$nodes = $dom->xpath->query( sprintf( '//iframe[ contains( @src, "%s" ) ]', self::BASE_EMBED_URL ) );

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

		if (
			$next_sibling &&
			'script' === $next_sibling->nodeName &&
			false !== strpos( $next_sibling->getAttribute( 'src' ), 'v0.wordpress.com/js/next/videopress-iframe.js' )
		) {
			$iframe_node->parentNode->removeChild( $next_sibling );
		}

		$iframe_node->setAttribute( 'layout', 'responsive' );
		$this->maybe_unwrap_p_element( $iframe_node );
	}
}
