<?php
/**
 * Class AMP_SoundCloud_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_SoundCloud_Embed_Handler
 *
 * @since 0.5
 */
class AMP_SoundCloud_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Default height.
	 *
	 * @var int
	 */
	protected $DEFAULT_HEIGHT = 200;

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
	 * Sanitize all SoundCloud <iframe> tags to <amp-soundcloud>.
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$nodes = $dom->xpath->query( '//iframe[ starts-with( @src, "https://w.soundcloud.com/player/" ) ]' );

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
		return $node->parentNode && 'amp-soundcloud' !== $node->parentNode->nodeName;
	}

	/**
	 * Make SoundCloud embed AMP compatible.
	 *
	 * @param DOMElement $iframe_node The node to make AMP compatible.
	 */
	private function sanitize_raw_embed( DOMElement $iframe_node ) {
		$src   = html_entity_decode( $iframe_node->getAttribute( 'src' ), ENT_QUOTES );
		$query = [];
		parse_str( wp_parse_url( $src, PHP_URL_QUERY ), $query );

		if ( empty( $query['url'] ) ) {
			return;
		}

		$embed_id = $this->parse_embed_id_from_url( $query['url'] );

		if ( isset( $embed_id['track_id'] ) ) {
			$attributes['data-trackid'] = $embed_id['track_id'];
		} elseif ( isset( $embed_id['playlist_id'] ) ) {
			$attributes['data-playlistid'] = $embed_id['playlist_id'];
		} else {
			// Return if the track nor playlist ID was found.
			return;
		}

		$attributes['height'] = $iframe_node->hasAttribute( 'height' )
			? $iframe_node->getAttribute( 'height' )
			: $this->args['height'];

		if ( $iframe_node->hasAttribute( 'width' ) ) {
			$attributes['width']  = $iframe_node->getAttribute( 'width' );
			$attributes['layout'] = 'responsive';
		} else {
			$attributes['layout'] = 'fixed-height';
		}

		if ( isset( $query['visual'] ) ) {
			$attributes['data-visual'] = rest_sanitize_boolean( $query['visual'] ) ? 'true' : 'false';
		}

		$amp_node = AMP_DOM_Utils::create_node(
			Document::fromNode( $iframe_node ),
			'amp-soundcloud',
			$attributes
		);

		$this->maybe_unwrap_p_element( $iframe_node );

		$iframe_node->parentNode->replaceChild( $amp_node, $iframe_node );
	}

	/**
	 * Get embed ID from Soundcloud iframe src.
	 *
	 * @param string $url URL.
	 * @return array|null Array containing ID name and value if found.
	 */
	private function parse_embed_id_from_url( $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( preg_match( '#tracks/(?P<track_id>\d+)#', $parsed_url['path'], $matches ) ) {
			return [
				'track_id' => $matches['track_id'],
			];
		}
		if ( preg_match( '#playlists/(?P<playlist_id>\d+)#', $parsed_url['path'], $matches ) ) {
			return [
				'playlist_id' => $matches['playlist_id'],
			];
		}
		return [];
	}
}
