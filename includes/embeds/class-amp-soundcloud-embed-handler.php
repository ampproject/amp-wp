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
	 * Base URL used for identifying embeds.
	 *
	 * @var string
	 */
	const BASE_EMBED_URL = 'https://w.soundcloud.com/player/';

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
		return $node->parentNode && 'amp-soundcloud' !== $node->parentNode->nodeName;
	}

	/**
	 * Make SoundCloud embed AMP compatible.
	 *
	 * @param DOMElement $iframe_node The node to make AMP compatible.
	 */
	private function sanitize_raw_embed( DOMElement $iframe_node ) {
		$src = $iframe_node->getAttribute( 'src' );

		parse_str( wp_parse_url( $src, PHP_URL_QUERY ), $query );
		if ( empty( $query['url'] ) ) {
			return;
		}

		$embed_id   = $this->parse_embed_id_from_url( $query['url'] );
		if ( null === $embed_id ) {
			return;
		}

		$attributes = [];

		if ( isset( $embed_id['track_id'] ) ) {
			$attributes['data-trackid'] = $embed_id['track_id'];
		} elseif ( isset( $embed_id['playlist_id'] ) ) {
			$attributes['data-playlistid'] = $embed_id['playlist_id'];
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
	 * @return array|null Array with key being the embed type and value being the embed ID, or null if embed type and ID could not be found.
	 */
	private function parse_embed_id_from_url( $url ) {
		if ( preg_match( '#/player/(?P<type>tracks|playlists)/(?P<id>\d+)#', $url, $matches ) ) {
			if ( 'tracks' === $matches['type'] ) {
				return [ 'track_id' => $matches['id'] ];
			}
			if ( 'playlists' === $matches['type'] ) {
				return [ 'playlist_id' => $matches['id'] ];
			}
		}

		return null;
	}
}
