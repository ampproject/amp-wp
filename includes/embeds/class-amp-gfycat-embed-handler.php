<?php
/**
 * Class AMP_Gfycat_Embed_Handler
 *
 * @package AMP
 * @since 1.0
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Gfycat_Embed_Handler
 */
class AMP_Gfycat_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Base URL used for identifying embeds.
	 *
	 * @var string
	 */
	const BASE_EMBED_URL = 'www.hulu.com/embed.html';

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
	 * Sanitize all gfycat <iframe> tags to <amp-gfycat>.
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$nodes = $dom->xpath->query( sprintf( '//iframe[ starts-with( @src, "%s" ) ]', self::BASE_EMBED_URL ) );

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
		return $node->parentNode && 'amp-gfycat' !== $node->parentNode->nodeName;
	}

	/**
	 * Make DailyMotion embed AMP compatible.
	 *
	 * @param DOMElement $iframe_node The node to make AMP compatible.
	 */
	private function sanitize_raw_embed( DOMElement $iframe_node ) {
		$iframe_src = $iframe_node->getAttribute( 'src' );

		if ( ! preg_match( '#/ifr/([A-Za-z0-9]+)#', $iframe_src, $matches ) ) {
			// Nothing to do if video ID could not be found.
			return;
		}

		$new_attributes = [
			'data-gfyid' => $matches[1],
			'layout'     => 'responsive',
			'height'     => $iframe_node->getAttribute( 'height' ),
			'width'      => $iframe_node->getAttribute( 'width' ),
		];

		if ( empty( $new_attributes['height'] ) ) {
			return;
		}

		if ( empty( $new_attributes['width'] ) ) {
			$new_attributes['layout'] = 'fixed-height';
			$new_attributes['width']  = 'auto';
		}

		$amp_node = AMP_DOM_Utils::create_node(
			Document::fromNode( $iframe_node ),
			'amp-gfycat',
			$new_attributes
		);

		$this->maybe_unwrap_p_element( $iframe_node );

		$iframe_node->parentNode->replaceChild( $amp_node, $iframe_node );
	}
}

