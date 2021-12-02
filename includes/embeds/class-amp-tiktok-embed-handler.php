<?php
/**
 * Class AMP_TikTok_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Extension;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;
use AmpProject\Layout;

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
			sprintf( '//blockquote[ @cite and @data-video-id and contains( @class, "tiktok-embed" ) and not( parent::%s ) ]', Extension::TIKTOK )
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

		// Initial height of video (most of them anyway).
		$height = 575;

		// Add the height of the metadata card with the CTA, username, and audio source.
		$height += 118;

		// Estimate the lines of text in the paragraph description (150-character limit).
		$p = $blockquote->getElementsByTagName( Tag::P )->item( 0 );
		if ( $p instanceof Element ) {
			$height += 8; // Top margin.

			// Add height for the lines of text, where there are approx. 39 chars fit on
			// a line, and a line's height is 18px.
			$height += ceil( strlen( trim( $p->textContent ) ) / 39 ) * 18;
		}

		$amp_tiktok = AMP_DOM_Utils::create_node(
			Document::fromNode( $dom ),
			Extension::TIKTOK,
			[
				Attribute::LAYOUT   => Layout::FIXED_HEIGHT,
				Attribute::HEIGHT   => $height,
				Attribute::WIDTH    => 'auto',
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
