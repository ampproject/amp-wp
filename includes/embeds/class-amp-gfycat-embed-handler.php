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

		$gfycat_id = strtok( substr( $iframe_src, strlen( self::BASE_EMBED_URL ) ), '/?#' );
		if ( empty( $gfycat_id ) ) {
			// Nothing to do if the ID could not be found.
			return;
		}

		$attributes = [
			'data-gfyid' => $gfycat_id,
			'layout'     => 'responsive',
			'width'        => $this->args['width'],
			'height'       => $this->args['height'],
		];

		if ( $iframe_node->hasAttribute( 'width' ) ) {
			$attributes['width'] = $iframe_node->getAttribute( 'width' );
		}

		if ( $iframe_node->hasAttribute( 'height' ) ) {
			$attributes['height'] = $iframe_node->getAttribute( 'height' );
		}

		$amp_node = AMP_DOM_Utils::create_node(
			Document::fromNode( $iframe_node ),
			'amp-gfycat',
			$attributes
		);

		$this->maybe_unwrap_p_element( $iframe_node );

		$iframe_node->parentNode->replaceChild( $amp_node, $iframe_node );
	}
}

