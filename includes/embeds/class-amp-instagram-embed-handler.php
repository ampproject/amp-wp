<?php
/**
 * Class AMP_Instagram_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Instagram_Embed_Handler
 *
 * Much of this class is borrowed from Jetpack embeds
 */
class AMP_Instagram_Embed_Handler extends AMP_Base_Embed_Handler {

	const URL_PATTERN = '#https?:\/\/(www\.)?instagr(\.am|am\.com)\/(p|tv)\/([A-Za-z0-9-_]+)#i';

	/**
	 * Default width.
	 *
	 * @var int
	 */
	protected $DEFAULT_WIDTH = 600;

	/**
	 * Default height.
	 *
	 * @var int
	 */
	protected $DEFAULT_HEIGHT = 600;

	/**
	 * Tag.
	 *
	 * @var string embed HTML blockquote tag to identify and replace with AMP version.
	 */
	protected $sanitize_tag = 'blockquote';

	/**
	 * Tag.
	 *
	 * @var string AMP amp-instagram tag
	 */
	private $amp_tag = 'amp-instagram';

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
	 * Sanitized <blockquote class="instagram-media"> tags to <amp-instagram>
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		/**
		 * Node list.
		 *
		 * @var DOMNodeList $nodes
		 */
		$nodes     = $dom->getElementsByTagName( $this->sanitize_tag );
		$num_nodes = $nodes->length;

		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			if ( $node->hasAttribute( 'data-instgrm-permalink' ) ) {
				$this->create_amp_instagram_and_replace_node( $dom, $node );
			}
		}
	}

	/**
	 * Make final modifications to DOMNode
	 *
	 * @param Document   $dom The HTML Document.
	 * @param DOMElement $node The DOMNode to adjust and replace.
	 */
	private function create_amp_instagram_and_replace_node( $dom, $node ) {
		$instagram_id = $this->get_instagram_id_from_url( $node->getAttribute( 'data-instgrm-permalink' ) );

		$node_args = [
			'data-shortcode' => $instagram_id,
			'layout'         => 'responsive',
			'width'          => $this->DEFAULT_WIDTH,
			'height'         => $this->DEFAULT_HEIGHT,
		];

		if ( true === $node->hasAttribute( 'data-instgrm-captioned' ) ) {
			$node_args['data-captioned'] = '';
		}

		$new_node = AMP_DOM_Utils::create_node( $dom, $this->amp_tag, $node_args );

		$this->maybe_remove_script_sibling( $node, 'instagram.com/embed.js' );

		$node->parentNode->replaceChild( $new_node, $node );

		$this->did_convert_elements = true;
	}

	/**
	 * Get Instagram ID from URL.
	 *
	 * @param string $url URL.
	 * @return string|false The ID parsed from the URL or false if not found.
	 */
	private function get_instagram_id_from_url( $url ) {
		$found = preg_match( self::URL_PATTERN, $url, $matches );

		if ( ! $found ) {
			return false;
		}

		return end( $matches );
	}
}
