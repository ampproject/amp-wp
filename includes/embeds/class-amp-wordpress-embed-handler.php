<?php
/**
 * Class AMP_WordPress_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Dom\Document;
use AmpProject\Attribute;
use AmpProject\Extension;

/**
 * Class AMP_WordPress_Embed_Handler
 *
 * @since 2.2
 */
class AMP_WordPress_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Default height.
	 *
	 * Note that 200px is the minimum that WordPress allows for a post embed. This minimum height is enforced by
	 * WordPress in the wp.receiveEmbedMessage() function, and the <amp-wordpress-embed> also enforces that same
	 * minimum height. It is important for the minimum height to be initially used because if the actual post embed
	 * window is _less_ than the initial, then no overflow button will be presented to resize the iframe to be
	 * _smaller_. So this ensures that the iframe will only ever overflow to grow in height.
	 *
	 * @var int
	 */
	protected $DEFAULT_HEIGHT = 200;

	/**
	 * Tag.
	 *
	 * @var string AMP amp-twitter tag
	 */
	private $amp_tag = Extension::WORDPRESS_EMBED;

	/**
	 * Register embed.
	 */
	public function register_embed() {
		// This is not needed when post embeds are embedded via <amp-wordpress-embed>. See <https://github.com/ampproject/amp-wp/issues/809>.
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		add_action( 'wp_head', 'wp_oembed_add_host_js' );
	}

	/**
	 * Sanitize WordPress embed raw embeds.
	 *
	 * @param Document $dom Document.
	 *
	 * @return void
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$pairs = $this->find_blockquote_iframe_pairs( $dom );

		foreach ( $pairs as $pair ) {
			if ( ! $pair['blockquote'] instanceof DOMElement || ! $pair['iframe'] instanceof DOMElement ) {
				continue;
			}

			$this->create_amp_wordpress_embed_and_replace_node( $dom, $pair['blockquote'], $pair['iframe'] );
		}
	}

	/**
	 * Find <blockquote> & <iframe> pairs of WordPress embeds
	 *
	 * @param Document $dom Document.
	 * 
	 * @return array
	 */
	private function find_blockquote_iframe_pairs( $dom ) {

		$pairs = [];

		// If iframes with ".wp-embedded-content" class name in the DOM are wrapped by `wpautop()`, unwrap them.
		$iframe_nodes = $dom->xpath->query( '//p/iframe[ @class = "wp-embedded-content" ]' );
		if ( $iframe_nodes->length ) {
			foreach ( $iframe_nodes as $iframe_node ) {
				$this->unwrap_p_element( $iframe_node );
			}
		}

		$blockquote_nodes = $dom->xpath->query( '//blockquote[ @class = "wp-embedded-content" ]' );
		if ( $blockquote_nodes->length ) {
			foreach ( $blockquote_nodes as $index => $blockquote_node ) {
				$blockquote_node->setAttribute( 'data-tmp-id', $index );
				$closest_iframe = $dom->xpath->query( '//blockquote[ @data-tmp-id = "' . $index . '" ]/following-sibling::iframe[ @class = "wp-embedded-content" ]' );
				$blockquote_node->removeAttribute( 'data-tmp-id' );

				if ( 1 === $closest_iframe->length ) {
					$pairs[] = [
						'blockquote' => $blockquote_node,
						'iframe'     => $closest_iframe->item( 0 ),
					];
				}
			}
		}

		return $pairs;
	}

	/**
	 * Make final modifications to DOMNode
	 *
	 * @param Document   $dom        The HTML Document.
	 * @param DOMElement $blockquote The blockquote DOMNode to be moved inside <amp-wordpress-embed>.
	 * @param DOMElement $iframe     The iframe DOMNode to be replaced with <amp-wordpress-embed>.
	 */
	private function create_amp_wordpress_embed_and_replace_node( Document $dom, DOMElement $blockquote, DOMElement $iframe ) {

		$iframe_html = $dom->saveHTML( $iframe );
		$attributes  = [
			Attribute::HEIGHT => $this->args['height'],
			Attribute::TITLE  => '',
		];

		if ( preg_match( '#<iframe[^>]*?title="(?P<title>[^"]+?)"#s', $iframe_html, $matches ) ) {
			$attributes[ Attribute::TITLE ] = $matches['title'];
		}

		if ( preg_match( '#<iframe[^>]*?src="(?P<src>[^"]+?)"#s', $iframe_html, $matches ) ) {
			$data_url     = $matches['src'];
			$valid_secret = $blockquote->getAttribute( 'data-secret' );
			if ( null !== $valid_secret && preg_match_all( '/secret=([^#&?]+)/', $matches['src'], $secrets ) ) {
				foreach ( $secrets[1] as $secret ) {
					if ( $secret !== $valid_secret ) {
						$data_url = str_replace( "#?secret=$secret", '', $data_url );
					}
				}
			}
			$attributes['data-url'] = $data_url;
		}

		$amp_wordpress_embed_node = AMP_DOM_Utils::create_node(
			$dom,
			$this->amp_tag,
			$attributes
		);

		$blockquote->setAttribute( 'placeholder', null );
		$amp_wordpress_embed_node->appendChild( $blockquote );
		$amp_wordpress_embed_node->appendChild( $this->create_overflow_button_element( $dom, __( 'Expand', 'amp' ) ) );

		$iframe->parentNode->replaceChild( $amp_wordpress_embed_node, $iframe );

		$this->did_convert_elements = true;
	}
}
