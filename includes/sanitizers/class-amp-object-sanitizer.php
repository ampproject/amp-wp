<?php
/**
 * Class AMP_Object_Sanitizer
 *
 * @package AMP
 */

use AmpProject\Attribute;
use AmpProject\Layout;

/**
 * Class AMP_Object_Sanitizer
 *
 * Sanitizes `<object>` embeds.
 *
 * @since 2.1
 * @internal
 */
class AMP_Object_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Default PDF embed height.
	 *
	 * @var int
	 */
	const DEFAULT_PDF_EMBED_HEIGHT = 600;

	/**
	 * Add filters to manipulate output during output buffering before the DOM is constructed.
	 *
	 * @param array $args Args.
	 */
	public static function add_buffering_hooks( $args = [] ) {
		add_action(
			'wp_print_footer_scripts',
			static function() {
				if ( wp_script_is( 'wp-block-library-file' ) ) {
					wp_dequeue_script( 'wp-block-library-file' );
				}
			},
			0
		);
	}

	/**
	 * Sanitize `object` elements from the HTML contained in this instance's Dom\Document.
	 */
	public function sanitize() {
		$elements = $this->dom->getElementsByTagName( 'object' );

		if ( 0 === $elements->length ) {
			return;
		}

		/** @var DOMElement $element */
		foreach ( $elements as $element ) {
			if ( $element->getAttribute( Attribute::TYPE ) === 'application/pdf' ) {
				$this->sanitize_pdf( $element );
			}
		}
	}

	/**
	 * Sanitize PDF embeds.
	 *
	 * @param DOMElement $element Object element.
	 */
	public function sanitize_pdf( DOMElement $element ) {
		$parsed_style = $this->parse_style_string( $element->getAttribute( Attribute::STYLE ) );
		$embed_height = isset( $parsed_style['height'] ) ? $parsed_style['height'] : self::DEFAULT_PDF_EMBED_HEIGHT;

		$amp_element = AMP_DOM_Utils::create_node(
			$element->ownerDocument,
			'amp-google-document-embed',
			[
				Attribute::LAYOUT => Layout::FIXED_HEIGHT,
				Attribute::HEIGHT => $embed_height,
				Attribute::SRC    => $element->getAttribute( 'data' ),
			]
		);

		$element->parentNode->replaceChild( $amp_element, $element );
	}
}
