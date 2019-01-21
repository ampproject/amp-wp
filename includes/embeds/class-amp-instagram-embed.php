<?php
/**
 * Class AMP_Instagram_Embed_Handler
 *
 * @package AMP
 */

/**
 * Class AMP_Instagram_Embed_Handler
 *
 * Much of this class is borrowed from Jetpack embeds
 */
class AMP_Instagram_Embed_Handler extends AMP_Base_Embed_Handler {
	const SHORT_URL_HOST = 'instagr.am';
	const URL_PATTERN    = '#http(s?)://(www\.)?instagr(\.am|am\.com)/p/([^/?]+)#i';

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
	 * @var string AMP amp-facebook tag
	 */
	private $amp_tag = 'amp-instagram';

	/**
	 * Registers embed.
	 */
	public function register_embed() {
		wp_embed_register_handler( $this->amp_tag, self::URL_PATTERN, array( $this, 'oembed' ), -1 );
		add_shortcode( 'instagram', array( $this, 'shortcode' ) );
	}

	/**
	 * Unregisters embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( $this->amp_tag, -1 );
		remove_shortcode( 'instagram' );
	}

	/**
	 * WordPress shortcode rendering callback.
	 *
	 * @param array $attr Shortcode attributes.
	 * @return string HTML markup for rendered embed.
	 */
	public function shortcode( $attr ) {
		$url = false;

		$instagram_id = false;
		if ( isset( $attr['url'] ) ) {
			$url = trim( $attr['url'] );
		}

		if ( empty( $url ) ) {
			return '';
		}

		$instagram_id = $this->get_instagram_id_from_url( $url );

		return $this->render(
			array(
				'url'          => $url,
				'instagram_id' => $instagram_id,
			)
		);
	}

	/**
	 * WordPress OEmbed rendering callback.
	 *
	 * @param array  $matches URL pattern matches.
	 * @param array  $attr    Matched attributes.
	 * @param string $url     Matched URL.
	 * @param string $rawattr Raw attributes string.
	 * @return string HTML markup for rendered embed.
	 */
	public function oembed( $matches, $attr, $url, $rawattr ) {
		return $this->render(
			array(
				'url'          => $url,
				'instagram_id' => end( $matches ),
			)
		);
	}

	/**
	 * Gets the rendered embed markup.
	 *
	 * @param array $args Embed rendering arguments.
	 * @return string HTML markup for rendered embed.
	 */
	public function render( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'url'          => false,
				'instagram_id' => false,
			)
		);

		if ( empty( $args['instagram_id'] ) ) {
			return AMP_HTML_Utils::build_tag(
				'a',
				array(
					'href'  => esc_url( $args['url'] ),
					'class' => 'amp-wp-embed-fallback',
				),
				esc_html( $args['url'] )
			);
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			$this->amp_tag,
			array(
				'data-shortcode' => $args['instagram_id'],
				'data-captioned' => '',
				'layout'         => 'responsive',
				'width'          => $this->args['width'],
				'height'         => $this->args['height'],
			)
		);
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

	/**
	 * Sanitized <blockquote class="instagram-media"> tags to <amp-instagram>
	 *
	 * @param DOMDocument $dom DOM.
	 */
	public function sanitize_raw_embeds( $dom ) {
		/**
		 * Node list.
		 *
		 * @var DOMNodeList $node
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
	 * @param DOMDocument $dom The HTML Document.
	 * @param DOMElement  $node The DOMNode to adjust and replace.
	 */
	private function create_amp_instagram_and_replace_node( $dom, $node ) {
		$instagram_id = $this->get_instagram_id_from_url( $node->getAttribute( 'data-instgrm-permalink' ) );

		$node_args = array(
			'data-shortcode' => $instagram_id,
			'layout'         => 'responsive',
			'width'          => $this->DEFAULT_WIDTH,
			'height'         => $this->DEFAULT_HEIGHT,
		);

		if ( true === $node->hasAttribute( 'data-instgrm-captioned' ) ) {
			$node_args['data-captioned'] = '';
		}

		$new_node = AMP_DOM_Utils::create_node( $dom, $this->amp_tag, $node_args );

		$this->sanitize_embed_script( $node );

		$node->parentNode->replaceChild( $new_node, $node );

		$this->did_convert_elements = true;
	}

	/**
	 * Removes Instagram's embed <script> tag.
	 *
	 * @param DOMElement $node The DOMNode to whose sibling is the instagram script.
	 */
	private function sanitize_embed_script( $node ) {
		$next_element_sibling = $node->nextSibling;
		while ( $next_element_sibling && ! ( $next_element_sibling instanceof DOMElement ) ) {
			$next_element_sibling = $next_element_sibling->nextSibling;
		}

		$script_src = 'instagram.com/embed.js';

		// Handle case where script is wrapped in paragraph by wpautop.
		if ( $next_element_sibling instanceof DOMElement && 'p' === $next_element_sibling->nodeName ) {
			$children = $next_element_sibling->getElementsByTagName( '*' );
			if ( 1 === $children->length && 'script' === $children->item( 0 )->nodeName && false !== strpos( $children->item( 0 )->getAttribute( 'src' ), $script_src ) ) {
				$next_element_sibling->parentNode->removeChild( $next_element_sibling );
				return;
			}
		}

		// Handle case where script is immediately following.
		$is_embed_script = (
			$next_element_sibling
			&&
			'script' === strtolower( $next_element_sibling->nodeName )
			&&
			false !== strpos( $next_element_sibling->getAttribute( 'src' ), $script_src )
		);
		if ( $is_embed_script ) {
			$next_element_sibling->parentNode->removeChild( $next_element_sibling );
		}
	}
}
