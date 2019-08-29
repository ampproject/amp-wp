<?php
/**
 * Class AMP_Href_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Href_Sanitizer.
 *
 * Crawls all href attributes to ensure their URLs are valid.
 */
final class AMP_Href_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Xpath query selector to target all elements with a href attribute.
	 *
	 * @var string
	 */
	const XPATH_SELECTOR = '//*[@href]';

	/**
	 * Sanitize the HTML contained in the DOMDocument received by the
	 * constructor
	 *
	 * @throws LogicException If the xpath query produced invalid results.
	 */
	public function sanitize() {
		$xpath = new DOMXPath( $this->dom );

		foreach ( $xpath->query( self::XPATH_SELECTOR ) as $node ) {
			// This should not happen, something is wrong with our query.
			if ( ! $node instanceof DOMElement
				|| ! $node->hasAttribute( 'href' ) ) {
				throw new LogicException(
					'Href sanitizer xpath query returned invalid node'
				);
			}

			$url = esc_url( $node->getAttribute( 'href' ) );
			if ( empty( $url ) || false === wp_parse_url( $url ) ) {
				$node->removeAttribute( 'href' );
			}
		}
	}
}
