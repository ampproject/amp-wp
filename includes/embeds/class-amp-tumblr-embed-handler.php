<?php
/**
 * Class AMP_Tumblr_Embed_Handler
 *
 * @package AMP
 * @since 0.7
 */

use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Tag;

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
	 *
	 * @link https://tumblr.zendesk.com/hc/en-us/articles/226261028-Embed-pro-tips
	 * @var int
	 */
	protected $DEFAULT_WIDTH = 540;

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
		$placeholders = $dom->xpath->query(
			'//div[ @class and @data-href and contains( concat( " ", normalize-space( @class ), " " ), " tumblr-post " ) and starts-with( @data-href, "https://embed.tumblr.com/embed/post/" ) ]/a[ @href ]'
		);

		if ( 0 === $placeholders->length ) {
			return;
		}

		foreach ( $placeholders as $placeholder ) {
			$div = $placeholder->parentNode;
			if ( ! $div instanceof DOMElement ) {
				continue; // @codeCoverageIgnore
			}

			$attributes = [
				'src'       => $div->getAttribute( 'data-href' ),
				'layout'    => 'responsive',
				'width'     => $this->args['width'],
				'height'    => $this->args['height'],
				'resizable' => '',
				'sandbox'   => 'allow-scripts allow-popups allow-same-origin',
			];

			$amp_element = AMP_DOM_Utils::create_node(
				$dom,
				'amp-iframe',
				$attributes
			);

			// Add an overflow element to allow the amp-iframe to be manually resized.
			$overflow_element              = AMP_DOM_Utils::create_node(
				$dom,
				'button',
				[
					'overflow' => '',
					'type'     => 'button',
				]
			);
			$overflow_element->textContent = __( 'See more', 'amp' );
			$amp_element->appendChild( $overflow_element );
			$placeholder->setAttribute( Attribute::PLACEHOLDER, '' );
			$amp_element->appendChild( $placeholder );

			$this->maybe_remove_script_sibling(
				$div,
				static function ( DOMElement $script ) {
					if ( ! $script->hasAttribute( Attribute::SRC ) ) {
						return false;
					}

					$parsed_url = wp_parse_url( $script->getAttribute( Attribute::SRC ) );

					return (
						isset( $parsed_url['host'], $parsed_url['path'] )
						&&
						'assets.tumblr.com' === $parsed_url['host']
						&&
						'/post.js' === $parsed_url['path']
					);
				}
			);

			$div->parentNode->replaceChild( $amp_element, $div );
		}
	}
}
