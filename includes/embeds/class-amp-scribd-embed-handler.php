<?php
/**
 * Class AMP_Scribd_Embed_Handler
 *
 * @package AMP
 * @since 1.4
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Scribd_Embed_Handler
 */
class AMP_Scribd_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Registers embed.
	 */
	public function register_embed() {
		// Not implemented.
	}

	/**
	 * Unregisters embed.
	 */
	public function unregister_embed() {
		// Not implemented.
	}

	/**
	 * Sanitize all gfycat <iframe> tags to <amp-gfycat>.
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$nodes = $dom->xpath->query( '//iframe[ starts-with( @src, "https://www.scribd.com/embeds/" ) ]' );

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
	 * Make Scribd embed AMP compatible.
	 *
	 * @param DOMElement $iframe_node The node to make AMP compatible.
	 */
	private function sanitize_raw_embed( DOMElement $iframe_node ) {
		$required_sandbox_permissions = 'allow-popups allow-scripts';
		$iframe_node->setAttribute(
			'sandbox',
			sprintf( '%s %s', $iframe_node->getAttribute( 'sandbox' ), $required_sandbox_permissions )
		);

		// Remove the accompanied script tag so that the iframe can be later unwrapped.
		if ( 'script' === $iframe_node->nextSibling->nodeName ) {
			$parent_element = AMP_DOM_Utils::get_parent_element( $iframe_node );
			if ( $parent_element ) {
				$parent_element->removeChild( $iframe_node->nextSibling );
			}
		}

		$iframe_node->setAttribute( 'layout', 'responsive' );

		$this->maybe_unwrap_p_element( $iframe_node );

		// The iframe sanitizer will further sanitize and convert this into an amp-iframe.
	}
}
