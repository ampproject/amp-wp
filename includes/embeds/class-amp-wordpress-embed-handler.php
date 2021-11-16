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
	 * @var string embed HTML iframe tag to identify and replace with AMP version.
	 */
	protected $sanitize_tag = 'iframe';

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
		// Not implemented.
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		// Not implemented.
	}

	/**
	 * Sanitize WordPress embed raw embeds.
	 *
	 * @param Document $dom Document.
	 *
	 * @return void
	 */
	public function sanitize_raw_embeds( Document $dom ) {
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

			if ( $this->is_raw_embed( $node ) ) {
				// If embeds in the DOM are wrapped by `wpautop()`, unwrap them.
				$embed_nodes = $dom->xpath->query( '//p/iframe[ @class = "wp-embedded-content" ]' );
				if ( $embed_nodes->length ) {
					foreach ( $embed_nodes as $embed_node ) {
						$this->unwrap_p_element( $embed_node );
					}
				}

				$this->create_amp_wordpress_embed_and_replace_node( $dom, $node );
			}
		}
	}

	/**
	 * Checks whether it's a WordPress embed blockquote or not.
	 *
	 * @param DOMElement $node The DOMNode to adjust and replace.
	 * @return bool Whether node is for raw embed.
	 */
	private function is_raw_embed( $node ) {
		$class_attr = $node->getAttribute( 'class' );
		return null !== $class_attr && false !== strpos( $class_attr, 'wp-embedded-content' );
	}

	/**
	 * Make final modifications to DOMNode
	 *
	 * @param Document   $dom  The HTML Document.
	 * @param DOMElement $node The DOMNode to adjust and replace.
	 */
	private function create_amp_wordpress_embed_and_replace_node( Document $dom, DOMElement $node ) {

		$node_html  = $dom->saveHTML( $node );
		$attributes = [
			Attribute::HEIGHT => $this->args['height'],
			Attribute::TITLE  => '',
		];

		if ( preg_match( '#<iframe[^>]*?title="(?P<title>[^"]+?)"#s', $node_html, $matches ) ) {
			$attributes[ Attribute::TITLE ] = $matches['title'];
		}

		if ( preg_match( '#<iframe[^>]*?src="(?P<src>[^"]+?)"#s', $node_html, $matches ) ) {
			$attributes['data-url'] = $matches['src'];
		}

		$new_node = AMP_DOM_Utils::create_node(
			$dom,
			$this->amp_tag,
			$attributes
		);

		$new_node->appendChild( $this->create_overflow_button_element( $dom, __( 'Expand', 'amp' ) ) );
		$node->parentNode->replaceChild( $new_node, $node );

		$this->did_convert_elements = true;
	}
}
