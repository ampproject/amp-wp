<?php
/**
 * Class AMP_Links_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Meta_Sanitizer.
 *
 * Sanitizes meta tags found in the header.
 *
 * @since 1.5.0
 */
class AMP_Meta_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @var string HTML <meta> tag to identify and replace with AMP version.
	 */
	public static $tag = 'meta';

	/**
	 * Placeholder for default arguments, to be set in child classes.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [ // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
		'use_document_element' => true, // We want to work on the header, so we need the entire document.
	];

	/**
	 * Charset to use for AMP markup.
	 *
	 * @var string
	 */
	const AMP_CHARSET = 'utf-8';

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		foreach ( $this->dom->getElementsByTagName( static::$tag ) as $element ) {
			$this->sanitize_element( $element );
		}

		$charset = $this->ensure_charset_is_present();

		if ( static::AMP_CHARSET !== $charset ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement
			// @TODO Re-encode the content into UTF-8.
			// ... sure?
		}
	}

	/**
	 * Sanitize an individual meta tag.
	 *
	 * @param DOMElement $element Meta tag to sanitize.
	 */
	protected function sanitize_element( DOMElement $element ) {
		// Handle HTML 4 http-equiv meta tags.
		if ( 'content-type' === strtolower( $element->getAttribute( 'http-equiv' ) ) ) {
			$charset = $element->getAttribute( 'charset' );
			if ( $charset ) {
				// If we have a charset attribute included, use that as a separate tag.
				$element->parentNode->appendChild( $this->create_charset_node( $charset ) );
			} else {
				// If not, check whether the charset is included with the content type, and use that.
				$content = $element->getAttribute( 'content' );
				$matches = [];
				if ( preg_match( '/;\s*charset=(?<charset>[^;]+)/', $content, $matches ) && ! empty( $matches['charset'] ) ) {
					$element->parentNode->appendChild( $this->create_charset_node( $matches['charset'] ) );
				}
			}
			// In case we haven't found a charset by now, a default utf-8 one will be added in a later step.

			// Always remove the HTML 4 http-equiv tag.
			$element->parentNode->removeChild( $element );
		}
	}

	/**
	 * Always ensure that we have an HTML 5 charset meta tag.
	 *
	 * The charset defaults to utf-8, which is also what AMP requires.
	 *
	 * @return string The charset that was detected or added.
	 */
	protected function ensure_charset_is_present() {
		$xpath = new DOMXPath( $this->dom );

		// Bail early if we already have a meta charset.
		$charset_element = $xpath->query( '//meta[ @charset ]' )->item( 0 );
		if ( $charset_element ) {
			return $charset_element->getAttribute( 'charset' );
		}

		$parent = $xpath->query( '//html/head' )->item( 0 );
		if ( ! $parent ) {
			// We did not detect the actual head node to attach the meta tag to, so we just
			// add it to the document and assume the other sanitizers will figure it out.
			$parent = $this->dom->childNodes->item( 0 );
		}

		// No charset found, so add the default one.
		$parent->appendChild( $this->create_charset_node( static::AMP_CHARSET ) );
	}

	/**
	 * Create a new meta tag for the charset value.
	 *
	 * @param string $charset Character set to use.
	 * @return DOMElement New meta tag with requested charset.
	 */
	protected function create_charset_node( $charset ) {
		$charset_node = $this->dom->createElement( 'meta' );
		$charset_node->setAttribute( 'charset', strtolower( $charset ) );
		return $charset_node;
	}
}
