<?php
/**
 * Class AMP_WordPress_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Extension;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;
use AmpProject\Layout;

/**
 * Class AMP_WordPress_Embed_Handler
 *
 * @since 2.2.2
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
	 * @var string AMP amp-wordpress-embed tag
	 */
	private $amp_tag = Extension::WORDPRESS_EMBED;

	/**
	 * Register embed.
	 */
	public function register_embed() {
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

		$embed_iframes = $dom->xpath->query( '//iframe[ @src and contains( concat( " ", normalize-space( @class ), " " ), " wp-embedded-content " ) ]', $dom->body );
		foreach ( $embed_iframes as $embed_iframe ) {
			/** @var Element $embed_iframe */

			// Remove embed script included when user copies HTML Embed code, per get_post_embed_html().
			$embed_script = $dom->xpath->query( './following-sibling::script[ contains( text(), "wp.receiveEmbedMessage" ) ]', $embed_iframe )->item( 0 );
			if ( $embed_script instanceof Element ) {
				$embed_script->parentNode->removeChild( $embed_script );
			}

			// If the post embed iframe got wrapped in a paragraph by `wpautop()`, unwrap it. This happens not with
			// the Embed block but it does with the [embed] shortcode.
			$is_wrapped_in_paragraph = (
				$embed_iframe->parentNode instanceof Element
				&&
				Tag::P === $embed_iframe->parentNode->tagName
			);

			// If the iframe is wrapped in a paragraph, but it's not the only node, then abort.
			if ( $is_wrapped_in_paragraph && 1 !== $embed_iframe->parentNode->childNodes->length ) {
				continue;
			}

			$embed_blockquote = $dom->xpath->query(
				'./preceding-sibling::blockquote[ contains( concat( " ", normalize-space( @class ), " " ), " wp-embedded-content " ) ]',
				$is_wrapped_in_paragraph ? $embed_iframe->parentNode : $embed_iframe
			)->item( 0 );
			if ( $embed_blockquote instanceof Element ) {

				// Note that unwrap_p_element() is not being used here because it will do nothing if the paragraph
				// happens to have an attribute on it, which is possible with the_content filters.
				if ( $is_wrapped_in_paragraph && $embed_iframe->parentNode->parentNode instanceof Element ) {
					$embed_iframe->parentNode->parentNode->replaceChild( $embed_iframe, $embed_iframe->parentNode );
				}

				$this->create_amp_wordpress_embed_and_replace_node( $dom, $embed_blockquote, $embed_iframe );
			}
		}
	}

	/**
	 * Make final modifications to DOMNode
	 *
	 * @param Document $dom        The HTML Document.
	 * @param Element  $blockquote The blockquote to be moved inside <amp-wordpress-embed>.
	 * @param Element  $iframe     The iframe to be replaced with <amp-wordpress-embed>.
	 */
	private function create_amp_wordpress_embed_and_replace_node( Document $dom, Element $blockquote, Element $iframe ) {

		$attributes = [
			Attribute::HEIGHT => $this->args['height'],
			Attribute::LAYOUT => Layout::FIXED_HEIGHT,
		];
		if ( $iframe->hasAttribute( Attribute::TITLE ) ) {
			$attributes[ Attribute::TITLE ] = $iframe->getAttribute( Attribute::TITLE );
		}

		$src = $iframe->getAttribute( Attribute::SRC );

		// Remove the secret which will be handled by amp-wordpress-embed.
		$src = preg_replace( '/#\?secret=.+/', '', $src );
		$blockquote->removeAttribute( 'data-secret' );

		$attributes[ Attribute::DATA_URL ] = $src;

		$amp_wordpress_embed_node = AMP_DOM_Utils::create_node(
			$dom,
			$this->amp_tag,
			$attributes
		);

		$blockquote->setAttributeNode( $dom->createAttribute( Attribute::PLACEHOLDER ) );
		$amp_wordpress_embed_node->appendChild( $blockquote );
		$amp_wordpress_embed_node->appendChild( $this->create_overflow_button_element( $dom ) );

		$iframe->parentNode->replaceChild( $amp_wordpress_embed_node, $iframe );

		$this->did_convert_elements = true;
	}
}
