<?php
/**
 * Class AMP_Hulu_Embed_Handler
 *
 * @package AMP
 * @since 1.0
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Hulu_Embed_Handler
 */
class AMP_Hulu_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Base URL used for identifying embeds.
	 *
	 * @var string
	 */
	const BASE_EMBED_URL = 'www.hulu.com/embed.html';

	/**
	 * Default height.
	 *
	 * @var int
	 */
	protected $DEFAULT_HEIGHT = 600;

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
	 * Sanitize all Hulu <iframe> tags to <amp-hulu>.
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
		return $node->parentNode && 'amp-hulu' !== $node->parentNode->nodeName;
	}

	/**
	 * Make Hulu embed AMP compatible.
	 *
	 * @param DOMElement $iframe_node The node to make AMP compatible.
	 */
	private function sanitize_raw_embed( DOMElement $iframe_node ) {
		$iframe_src = $iframe_node->getAttribute( 'src' );
		$video_id   = $this->get_video_id( $iframe_src );

		if ( ! $video_id ) {
			return;
		}

		$attributes = [
			'data-eid' => $video_id,
			'layout'   => 'responsive',
			'width'    => $this->DEFAULT_WIDTH,
			'height'   => $this->DEFAULT_HEIGHT,
		];

		if ( $iframe_node->hasAttribute( 'width' ) ) {
			$attributes['width'] = $iframe_node->getAttribute( 'width' );
		}

		if ( $iframe_node->hasAttribute( 'height' ) ) {
			$attributes['height'] = $iframe_node->getAttribute( 'height' );
		}

		$amp_node = AMP_DOM_Utils::create_node(
			Document::fromNode( $iframe_node ),
			'amp-hulu',
			$attributes
		);

		$this->maybe_unwrap_p_element( $iframe_node );

		$iframe_node->parentNode->replaceChild( $amp_node, $iframe_node );
	}

	/**
	 * Get video from URL.
	 *
	 * @param string $url Video URL.
	 * @return string|null Video ID, or null if it was not found.
	 */
	private function get_video_id( $url ) {
		if ( preg_match( '#hulu\.com/embed\.html\?eid=(?P<id>[A-Za-z0-9_-]+)#', $url, $matches ) ) {
			return $matches['id'];
		}

		return null;
	}
}
