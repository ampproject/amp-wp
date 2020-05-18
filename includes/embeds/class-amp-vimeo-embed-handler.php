<?php
/**
 * Class AMP_Vimeo_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Vimeo_Embed_Handler
 *
 * Much of this class is borrowed from Jetpack embeds
 */
class AMP_Vimeo_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Base URL used for identifying embeds.
	 *
	 * @var string
	 */
	const BASE_EMBED_URL = 'https://player.vimeo.com/video/';

	/**
	 * The aspect ratio.
	 *
	 * @var float
	 */
	const RATIO = 0.5625;

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
	protected $DEFAULT_HEIGHT = 338;

	/**
	 * AMP_Vimeo_Embed_Handler constructor.
	 *
	 * @param array $args Height, width and maximum width for embed.
	 */
	public function __construct( $args = [] ) {
		parent::__construct( $args );

		if ( isset( $this->args['content_max_width'] ) ) {
			$max_width            = $this->args['content_max_width'];
			$this->args['width']  = $max_width;
			$this->args['height'] = round( $max_width * self::RATIO );
		}
	}

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
	 * Sanitize all Vimeo <iframe> tags to <amp-vimeo>.
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
		return $node->parentNode && 'amp-vimeo' !== $node->parentNode->nodeName;
	}

	/**
	 * Make Vimeo embed AMP compatible.
	 *
	 * @param DOMElement $iframe_node The node to make AMP compatible.
	 */
	private function sanitize_raw_embed( DOMElement $iframe_node ) {
		$iframe_src = $iframe_node->getAttribute( 'src' );
		$video_id   = $this->get_video_id_from_url( $iframe_src );

		if ( ! $video_id ) {
			return;
		}

		$amp_node = AMP_DOM_Utils::create_node(
			Document::fromNode( $iframe_node ),
			'amp-vimeo',
			[
				'data-videoid' => $video_id,
				'layout'       => 'responsive',
				'width'        => $this->args['width'],
				'height'       => $this->args['height'],
			]
		);

		$this->maybe_unwrap_p_element( $iframe_node );

		$iframe_node->parentNode->replaceChild( $amp_node, $iframe_node );
	}

	/**
	 * Determine the video ID from the URL.
	 *
	 * @param string $url URL.
	 * @return int Video ID.
	 */
	private function get_video_id_from_url( $url ) {
		// @todo This will not get the private key for unlisted videos (which look like https://vimeo.com/123456789/abcdef0123), but amp-vimeo doesn't support them currently anyway.
		$video_id = null;
		if ( preg_match( ':/video/(\d+):', $url, $matches ) ) {
			$video_id = $matches[1];
		}

		return $video_id;
	}
}
