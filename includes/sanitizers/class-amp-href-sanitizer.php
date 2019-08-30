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
	 * Array of attribute names that should be removed for invalid hrefs.
	 *
	 * @var array<string>
	 */
	private static $attributes_tied_to_href = [
		'target',
		'download',
		'rel',
		'rev',
		'hreflang',
		'type',
	];

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

			$url = esc_url_raw( $node->getAttribute( 'href' ) );

			if ( ! empty( $url ) && false !== wp_parse_url( $url ) ) {
				continue;
			}

			// Href URL is invalid, so we remove the entire attribute.
			$node->removeAttribute( 'href' );

			/*
			 * "The target, download, rel, rev, hreflang, and type attributes must be omitted
			 * if the href attribute is not present."
			 * See: https://www.w3.org/TR/2016/REC-html51-20161101/textlevel-semantics.html#the-a-element
			 */
			foreach ( self::$attributes_tied_to_href as $attribute ) {
				if ( $node->hasAttribute( $attribute ) ) {
					$node->removeAttribute( $attribute );
				}
			}
		}
	}
}
