<?php
/**
 * Class AMP_TikTok_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_TikTok_Embed_Handler
 */
class AMP_TikTok_Embed_Handler extends AMP_Base_Embed_Handler {

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
	 * Sanitize TikTok embeds to be AMP compatible.
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$nodes = $dom->xpath->query( '//blockquote[ contains( @class, "tiktok-embed" ) ]' );

		foreach ( $nodes as $node ) {
			if ( ! $this->is_raw_embed( $node ) ) {
				continue;
			}

			$this->make_embed_amp_compatible( $node );
		}
	}

	/**
	 * Determine if the node has already been sanitized.
	 *
	 * @param DOMElement $node The DOMNode.
	 * @return bool Whether the node is a raw embed.
	 */
	protected function is_raw_embed( DOMElement $node ) {
		return ! $node->firstChild || ( $node->firstChild && 'amp-embedly-card' !== $node->firstChild->nodeName );
	}

	/**
	 * Make TikTok embed AMP compatible.
	 *
	 * @param DOMElement $blockquote_node The <blockquote> node to make AMP compatible.
	 */
	protected function make_embed_amp_compatible( DOMElement $blockquote_node ) {
		$dom       = $blockquote_node->ownerDocument;
		$video_url = $blockquote_node->getAttribute( 'cite' );

		// If there is no video ID, stop here as its needed for the `data-url` parameter.
		if ( empty( $video_url ) ) {
			return;
		}

		$this->maybe_remove_script_sibling( $blockquote_node, 'tiktok.com/embed.js' );

		$amp_node = AMP_DOM_Utils::create_node(
			Document::fromNode( $dom ),
			'amp-embedly-card',
			[
				'layout'             => 'responsive',
				'height'             => 700,
				'width'              => 340,
				'data-card-controls' => 0,
				'data-url'           => $video_url,
			]
		);

		// Find existing <section> node to use as the placeholder.
		foreach ( iterator_to_array( $blockquote_node->childNodes ) as $child ) {
			if ( ! ( $child instanceof DOMElement ) ) {
				continue;
			}

			// Append the placeholder if it was found.
			if ( 'section' === $child->nodeName ) {
				/**
				 * Placeholder to append to the AMP component.
				 *
				 * @var DOMElement $placeholder_node
				 */
				$placeholder_node = $blockquote_node->removeChild( $child );
				$placeholder_node->setAttribute( 'placeholder', '' );
				$amp_node->appendChild( $placeholder_node );
				break;
			}
		}

		$blockquote_node->parentNode->replaceChild( $amp_node, $blockquote_node );
	}
}
