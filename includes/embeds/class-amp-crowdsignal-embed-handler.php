<?php
/**
 * Class AMP_Crowdsignal_Embed_Handler
 *
 * Handle making polls and surveys from Crowdsignal (formerly Polldaddy) AMP-compatible.
 *
 * @package AMP
 * @since 1.2
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Crowdsignal_Embed_Handler
 */
class AMP_Crowdsignal_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Get all raw embeds from the DOM.
	 *
	 * @param Document $dom Document.
	 * @return DOMNodeList A list of DOMElement nodes.
	 */
	protected function get_raw_embed_nodes( Document $dom ) {
		$queries = [
			// For poll embeds.
			'//iframe[ @class="cs-iframe-embed" and starts-with( @src, "https://poll.fm/" ) ]',
			// For survey embeds.
			'//div[ @class="pd-embed" and @data-settings ]',
		];

		return $dom->xpath->query( implode( ' | ', $queries ) );
	}

	/**
	 * Make embed AMP compatible.
	 *
	 * @param DOMElement $node DOM element.
	 */
	protected function sanitize_raw_embed( DOMElement $node ) {
		$is_poll = 'cs-iframe-embed' === $node->getAttribute( 'class' );

		if ( $is_poll ) {
			$this->sanitize_poll_embed( $node );
		} else {
			$this->sanitize_survey_embed( $node );
		}
	}

	/**
	 * Sanitize poll embed.
	 *
	 * @param DOMElement $node Poll embed.
	 */
	private function sanitize_poll_embed( DOMElement $node ) {
		// Replace the `noscript` parent element with the iframe.
		$node->parentNode->parentNode->replaceChild( $node, $node->parentNode );

		$this->unwrap_p_element( $node );
		$this->remove_script_sibling( $node, 'https://secure.polldaddy.com', '', false );
	}

	/**
	 * Sanitize survey embed.
	 *
	 * @param DOMElement $node Survey embed.
	 */
	private function sanitize_survey_embed( DOMElement $node ) {
		$settings = json_decode( $node->getAttribute( 'data-settings' ), false );

		// We can't form the iframe URL without a domain and survey ID.
		if ( ! ( property_exists( $settings, 'domain' ) || property_exists( $settings, 'id' ) ) ) {
			return;
		}

		// Logic for building the iframe `src` can be found in https://polldaddy.com/survey.js.
		$iframe_src = sprintf(
			'https://%s/%s?%s',
			$settings->domain,
			$settings->id,
			property_exists( $settings, 'auto' ) && $settings->auto ? 'ft=1&iframe=' . amp_get_current_url() : 'iframe=1'
		);

		$iframe_node = AMP_DOM_Utils::create_node(
			Document::fromNode( $node ),
			'iframe',
			[
				'src'               => $iframe_src,
				'layout'            => 'responsive',
				'width'             => 600,
				'height'            => 600,
				'frameborder'       => 0,
				'scrolling'         => 'no',
				'allowtransparency' => 'true',
				'sandbox'           => 'allow-scripts allow-same-origin',
			]
		);

		$this->remove_script_sibling( $node, null, 'https://polldaddy.com/survey.js' );

		$node->parentNode->replaceChild( $iframe_node, $node );
	}
}
