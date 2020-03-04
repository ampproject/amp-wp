<?php
/**
 * Class AMP_Tiktok_Embed_Handler
 *
 * @package AMP
 */

use Amp\AmpWP\Dom\Document;

/**
 * Class AMP_Tiktok_Embed_Handler
 */
class AMP_Tiktok_Embed_Handler extends AMP_Base_Embed_Handler {

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
	 * Sanitize Tiktok embeds to be AMP compatible.
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$nodes = $dom->xpath->query( '//blockquote[ @class="tiktok-embed" ]' );

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
		return ! $node->firstChild || ( $node->firstChild && 'amp-iframe' !== $node->firstChild->nodeName );
	}

	/**
	 * Make Tiktok embed AMP compatible.
	 *
	 * @param DOMElement $node The DOMNode to make AMP compatible.
	 */
	protected function make_embed_amp_compatible( DOMElement $node ) {
		$dom       = $node->ownerDocument;
		$embed_url = $node->getAttribute( 'cite' );
		$video_id  = $node->getAttribute( 'data-video-id' );

		$this->remove_embed_script( $node );

		// Create fallback node.
		$fallback_node = AMP_DOM_Utils::create_node(
			$dom,
			'a',
			[
				'href'  => esc_url_raw( $embed_url ),
				'class' => 'amp-wp-embed-fallback',
			]
		);
		$fallback_node->appendChild( new DOMText( esc_html( $embed_url ) ) );

		// If there is no video ID, replace the blockquote with the fallback and return.
		if ( empty( $video_id ) ) {
			$node->parentNode->replaceChild( $fallback_node, $node );
			return;
		}

		// Find existing <section> node to use as the placeholder.
		$placeholder_node = null;
		foreach ( iterator_to_array( $node->childNodes ) as $child ) {
			if ( ! ( $child instanceof DOMElement ) ) {
				continue;
			}

			if ( 'section' === $child->nodeName ) {
				$placeholder_node = $node->removeChild( $child );
				break;
			}
		}

		// If the placeholder was not found, use the fallback instead.
		if ( ! $placeholder_node ) {
			$placeholder_node = $fallback_node;
		}

		$placeholder_node->setAttribute( 'placeholder', '' );

		$amp_iframe_node = AMP_DOM_Utils::create_node(
			$dom,
			'amp-iframe',
			[
				'layout'  => 'responsive',
				'width'   => $this->DEFAULT_WIDTH,
				'height'  => $this->DEFAULT_HEIGHT,

				/*
				 * A `lang` query parameter is added to the URL via JS. This can't be determined here so it is not
				 * added. Whether it alters the embed in any way or not has not been determined.
				 */
				'src'     => 'https://www.tiktok.com/embed/v2/' . $video_id,
				'sandbox' => 'allow-scripts allow-same-origin',
			]
		);
		$amp_iframe_node->appendChild( $placeholder_node );

		// On the non-amp page the embed is wrapped with a <blockquote>, so the same is done here.
		$node->appendChild( $amp_iframe_node );
	}

	/**
	 * Remove Tiktok's embed script if it exists.
	 *
	 * @param DOMElement $node The DOMNode to make AMP compatible.
	 */
	protected function remove_embed_script( DOMElement $node ) {
		$next_element_sibling = $node->nextSibling;
		while ( $next_element_sibling && ! ( $next_element_sibling instanceof DOMElement ) ) {
			$next_element_sibling = $next_element_sibling->nextSibling;
		}

		$script_src = 'tiktok.com/embed.js';

		// Handle case where script is wrapped in paragraph by wpautop.
		if ( $next_element_sibling instanceof DOMElement && 'p' === $next_element_sibling->nodeName ) {
			$children = $next_element_sibling->getElementsByTagName( '*' );
			if ( 1 === $children->length && 'script' === $children->item( 0 )->nodeName && false !== strpos( $children->item( 0 )->getAttribute( 'src' ), $script_src ) ) {
				$next_element_sibling->parentNode->removeChild( $next_element_sibling );
				return;
			}
		}

		// Handle case where script is immediately following.
		$is_embed_script = (
			$next_element_sibling
			&&
			'script' === strtolower( $next_element_sibling->nodeName )
			&&
			false !== strpos( $next_element_sibling->getAttribute( 'src' ), $script_src )
		);
		if ( $is_embed_script ) {
			$next_element_sibling->parentNode->removeChild( $next_element_sibling );
		}
	}
}
