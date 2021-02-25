<?php
/**
 * Class AMP_Tumblr_Embed_Handler
 *
 * @package AMP
 * @since 0.7
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Tumblr_Embed_Handler
 *
 * @internal
 */
class AMP_Tumblr_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Default width.
	 *
	 * Tumblr embeds for web have a fixed width of 540px.
	 * See <https://tumblr.zendesk.com/hc/en-us/articles/226261028-Embed-pro-tips>.
	 *
	 * @var int
	 */
	protected $DEFAULT_WIDTH = 540;

	/**
	 * Base URL used for identifying embeds.
	 *
	 * @var string
	 */
	protected $base_embed_url = 'https://embed.tumblr.com/embed/post/';

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
	 * Sanitizes Tumblr raw embeds to make them AMP compatible.
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$nodes = $dom->xpath->query( sprintf( '//div[ @class = "tumblr-post" and starts-with( @data-href, "%s" ) ]', $this->base_embed_url ) );

		if ( $nodes->length === 0 ) {
			return;
		}

		foreach ( $nodes as $node ) {
			$iframe_src = $node->getAttribute( 'data-href' );

			$attributes = [
				'src'       => $iframe_src,
				'layout'    => 'responsive',
				'width'     => $this->args['width'],
				'height'    => $this->args['height'],
				'resizable' => '',
				'sandbox'   => 'allow-scripts allow-popups allow-same-origin'
			];

			$amp_node = AMP_DOM_Utils::create_node(
				$dom,
				'amp-iframe',
				$attributes
			);

			// Add an overflow node to allow the amp-iframe to resize.
			$overflow_node = AMP_DOM_Utils::create_node(
				$dom,
				'div',
				[
					'overflow' => '',
					'tabindex' => 0,
					'role'     => 'button',
					'aria-label' => esc_attr__( 'See more', 'amp' ),
				]
			);
			$overflow_node->textContent = esc_html__( 'See more', 'amp' );
			$amp_node->appendChild( $overflow_node );

			// Append the original link as a placeholder node.
			if ( $node->firstChild instanceof DOMElement && 'a' === $node->firstChild->nodeName ) {
				$placeholder_node = $node->firstChild;
				$placeholder_node->setAttribute( 'placeholder', '' );
				$amp_node->appendChild( $placeholder_node );
			}

			$this->maybe_remove_script_sibling( $node, 'assets.tumblr.com/post.js' );

			$node->parentNode->replaceChild( $amp_node, $node );
		}
	}
}

