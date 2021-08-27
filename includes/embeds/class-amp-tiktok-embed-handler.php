<?php
/**
 * Class AMP_TikTok_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Layout;
use AmpProject\Tag;

/**
 * Class AMP_TikTok_Embed_Handler
 *
 * @internal
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
		$nodes = $dom->xpath->query(
			sprintf( '//blockquote[ @cite and @data-video-id and contains( @class, "tiktok-embed" ) and not( parent::%s ) ]', 'amp-tiktok' )
		);

		foreach ( $nodes as $node ) {
			$this->make_embed_amp_compatible( $node );
		}
	}

	/**
	 * Make TikTok embed AMP compatible.
	 *
	 * @param Element $blockquote The <blockquote> node to make AMP compatible.
	 */
	protected function make_embed_amp_compatible( Element $blockquote ) {
		$dom       = $blockquote->ownerDocument;
		$video_url = $blockquote->getAttribute( Attribute::CITE );

		// If there is no video ID, stop here as its needed for the `data-src` parameter.
		if ( empty( $video_url ) ) {
			return;
		}

		$this->remove_embed_script( $blockquote );

		$amp_tiktok = AMP_DOM_Utils::create_node(
			Document::fromNode( $dom ),
			'amp-tiktok',
			[
				Attribute::LAYOUT   => Layout::RESPONSIVE,
				Attribute::HEIGHT   => 575,
				Attribute::WIDTH    => 325,
				Attribute::DATA_SRC => $video_url,
			]
		);

		$blockquote->parentNode->replaceChild( $amp_tiktok, $blockquote );
		$amp_tiktok->appendChild( $blockquote );
		$blockquote->setAttributeNode( $dom->createAttribute( Attribute::PLACEHOLDER ) );
		$blockquote->removeAttribute( Attribute::STYLE );
	}

	/**
	 * Remove the TikTok embed script if it exists.
	 *
	 * @param Element $blockquote The blockquote element being made AMP-compatible.
	 */
	protected function remove_embed_script( Element $blockquote ) {
		$next_element_sibling = $blockquote->nextSibling;
		while ( $next_element_sibling && ! ( $next_element_sibling instanceof Element ) ) {
			$next_element_sibling = $next_element_sibling->nextSibling;
		}

		$script_src = 'tiktok.com/embed.js';

		// Handle case where script is wrapped in paragraph by wpautop.
		if ( $next_element_sibling instanceof Element && Tag::P === $next_element_sibling->nodeName ) {
			$script = $next_element_sibling->getElementsByTagName( Tag::SCRIPT )->item( 0 );
			if (
				$script instanceof Element
				&&
				false !== strpos( $script->getAttribute( Attribute::SRC ), $script_src )
			) {
				$next_element_sibling->parentNode->removeChild( $next_element_sibling );
				return;
			}
		}

		// Handle case where script is immediately following.
		$is_embed_script = (
			$next_element_sibling instanceof Element
			&&
			Tag::SCRIPT === strtolower( $next_element_sibling->nodeName )
			&&
			false !== strpos( $next_element_sibling->getAttribute( Attribute::SRC ), $script_src )
		);
		if ( $is_embed_script ) {
			$next_element_sibling->parentNode->removeChild( $next_element_sibling );
		}
	}
}
