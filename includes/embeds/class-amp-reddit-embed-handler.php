<?php
/**
 * Class AMP_Reddit_Embed_Handler
 *
 * @package AMP
 * @since 0.7
 */

use AmpProject\Extension;
use AmpProject\Dom\Document;

/**
 * Class AMP_Reddit_Embed_Handler
 *
 * @internal
 */
class AMP_Reddit_Embed_Handler extends AMP_Base_Embed_Handler {
	/**
	 * Regex matched to produce output amp-reddit.
	 *
	 * @const string
	 */
	const URL_PATTERN = '#https?://(www\.)?reddit\.com/r/[^/]+/comments/.*#i';

	/**
	 * Register embed.
	 */
	public function register_embed() {
		wp_embed_register_handler( 'amp-reddit', self::URL_PATTERN, [ $this, 'oembed' ], -1 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-reddit', -1 );
	}

	/**
	 * Embed found with matching URL callback.
	 *
	 * @param array $matches URL regex matches.
	 * @param array $attr    Additional parameters.
	 * @param array $url     URL.
	 * @return string Embed.
	 */
	public function oembed( $matches, $attr, $url ) {
		return $this->render( [ 'url' => $url ] );
	}

	/**
	 * Output the Reddit amp element.
	 *
	 * @param array $args parameters used for output.
	 * @return string Rendered content.
	 */
	public function render( $args ) {
		$args = wp_parse_args(
			$args,
			[
				'url' => false,
			]
		);

		if ( empty( $args['url'] ) ) {
			return '';
		}

		return AMP_HTML_Utils::build_tag(
			'amp-embedly-card',
			[
				'layout'   => 'responsive',
				'width'    => '100',
				'height'   => '100',
				'data-url' => $args['url'],
			]
		);
	}

	/**
	 * Sanitize raw embeds.
	 *
	 * @param Document $dom Document.
	 *
	 * @return void
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		// If there were any previous embeds in the DOM that were wrapped by `wpautop()`, unwrap them.
		$this->unwrap_p_element_by_child_tag_name( $dom, Extension::EMBEDLY_CARD );
	}
}
