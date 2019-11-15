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
	 * Reference to the shared XPath object to query the DOM.
	 *
	 * @var DOMXPath
	 */
	protected $xpath;

	/**
	 * The document's <head> element.
	 *
	 * @var DOMElement
	 */
	protected $head;

	/**
	 * Charset to use for AMP markup.
	 *
	 * @var string
	 */
	const AMP_CHARSET = 'utf-8';

	/**
	 * Viewport settings to use for AMP markup.
	 *
	 * @var string
	 */
	const AMP_VIEWPORT = 'width=device-width';

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		$this->xpath = new DOMXPath( $this->dom );
		$this->head  = $this->ensure_head_is_present();

		foreach ( $this->dom->getElementsByTagName( static::$tag ) as $element ) {
			$this->sanitize_element( $element );
		}

		$charset_element = $this->ensure_charset_is_present_and_first_in_head();

		if ( ! $this->is_correct_charset( $charset_element ) ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement
			// @TODO Re-encode the content into UTF-8.
			// ... sure?
		}

		$this->ensure_viewport_is_present_and_after_charset( $charset_element );
	}

	/**
	 * Ensure that the <head> element is present in the document.
	 *
	 * @return DOMElement The document's <head> element.
	 */
	protected function ensure_head_is_present() {
		$head = $this->dom->getElementsByTagName( 'head' )->item( 0 );

		if ( ! $head ) {
			$head = $this->dom->createElement( 'head' );
			$head = $this->dom->documentElement->insertBefore( $head, $this->dom->documentElement->firstChild );
		}

		return $head;
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
				$element->parentNode->appendChild( $this->create_charset_element( $charset ) );
			} else {
				// If not, check whether the charset is included with the content type, and use that.
				$content = $element->getAttribute( 'content' );
				$matches = [];
				if ( preg_match( '/;\s*charset=(?<charset>[^;]+)/', $content, $matches ) && ! empty( $matches['charset'] ) ) {
					$element->parentNode->appendChild( $this->create_charset_element( $matches['charset'] ) );
				}
			}
			// In case we haven't found a charset by now, a default utf-8 one will be added in a later step.

			// Always remove the HTML 4 http-equiv tag.
			$element->parentNode->removeChild( $element );
		}
	}

	/**
	 * Always ensure that we have an HTML 5 charset meta tag, and force it to be the first in <head>.
	 *
	 * The charset defaults to utf-8, which is also what AMP requires.
	 *
	 * @return DOMElement The charset element that was detected or added.
	 */
	protected function ensure_charset_is_present_and_first_in_head() {
		// Retrieve the charset element or create a new one.
		$charset_element = $this->xpath->query( '//meta[ @charset ]' )->item( 0 );
		if ( $charset_element ) {
			$charset_element->parentNode->removeChild( $charset_element ); // So that we can move it.
		} else {
			$charset_element = $this->create_charset_element( static::AMP_CHARSET );
		}

		// (Re)insert the charset as first element of the head.
		$charset_element = $this->head->insertBefore( $charset_element, $this->head->firstChild );

		return $charset_element;
	}

	/**
	 * Always ensure we have a viewport tag and force it to be the second in <head> (after charset).
	 *
	 * The viewport defaults to 'width=device-width', which is the bare minimum that AMP requires.
	 *
	 * @param DOMElement $charset_element The charset meta tag element to append the viewport to.
	 */
	protected function ensure_viewport_is_present_and_after_charset( DOMElement $charset_element ) {
		// Retrieve the viewport element or create a new one.
		$viewport_element = $this->xpath->query( '//meta[ @name = "viewport" ]' )->item( 0 );
		if ( $viewport_element ) {
			$viewport_element->parentNode->removeChild( $viewport_element ); // So that we can move it.
		} else {
			$viewport_element = $this->create_viewport_element( static::AMP_VIEWPORT );
		}

		// (Re)insert the viewport as first element of the head.
		$this->head->insertBefore( $viewport_element, $charset_element->nextSibling );
	}

	/**
	 * Create a new meta tag for the charset value.
	 *
	 * @param string $charset Character set to use.
	 * @return DOMElement New meta tag with requested charset.
	 */
	protected function create_charset_element( $charset ) {
		return AMP_DOM_Utils::create_node(
			$this->dom,
			'meta',
			[
				'charset' => strtolower( $charset ),
			]
		);
	}

	/**
	 * Create a new meta tag for the viewport setting.
	 *
	 * @param string $viewport Viewport setting to use.
	 * @return DOMElement New meta tag with requested viewport setting.
	 */
	protected function create_viewport_element( $viewport ) {
		return AMP_DOM_Utils::create_node(
			$this->dom,
			'meta',
			[
				'name'    => 'viewport',
				'content' => $viewport,
			]
		);
	}

	/**
	 * Check whether the charset is the correct one according to AMP requirements.
	 *
	 * @param DOMElement $charset_element Charset meta tag element.
	 * @return bool Whether the charset is the correct one.
	 */
	protected function is_correct_charset( DOMElement $charset_element ) {
		return static::AMP_CHARSET === strtolower( $charset_element->getAttribute( 'charset' ) );
	}
}
