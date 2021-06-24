<?php
/**
 * Class AMP_Object_Sanitizer
 *
 * @package AMP
 */

use AmpProject\Attribute;
use AmpProject\Dom\Element;
use AmpProject\Extension;
use AmpProject\Layout;
use AmpProject\Tag;

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
	 * Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
	 *
	 * @return array Mapping.
	 */
	public function get_selector_conversion_mapping() {
		return [
			Tag::OBJECT => [
				Extension::GOOGLE_DOCUMENT_EMBED,
			],
		];
	}

	/**
	 * Sanitize `object` elements from the HTML contained in this instance's Dom\Document.
	 */
	public function sanitize() {
		$elements = $this->dom->getElementsByTagName( Tag::OBJECT );

		if ( 0 === $elements->length ) {
			return;
		}

		/** @var Element $element */
		foreach ( iterator_to_array( $elements ) as $element ) {
			if (
				$element->getAttribute( Attribute::TYPE ) === Attribute::TYPE_PDF
				&&
				$element->hasAttribute( Attribute::DATA )
			) {
				$this->sanitize_pdf( $element );
			}
		}
	}

	/**
	 * Sanitize PDF embeds.
	 *
	 * @see \AMP_Core_Block_Handler::ampify_file_block()
	 *
	 * @param Element $element Object element.
	 */
	public function sanitize_pdf( Element $element ) {
		$parsed_style = $this->parse_style_string( $element->getAttribute( Attribute::STYLE ) );
		$embed_height = isset( $parsed_style['height'] ) ? $parsed_style['height'] : self::DEFAULT_PDF_EMBED_HEIGHT;

		$attributes = [
			Attribute::LAYOUT => Layout::FIXED_HEIGHT,
			Attribute::HEIGHT => $embed_height,
			Attribute::SRC    => $element->getAttribute( Attribute::DATA ),
		];

		$title = $element->getAttribute( Attribute::ARIA_LABEL );
		if ( '' !== $title ) {
			$attributes[ Attribute::TITLE ] = $title;
		}

		$attributes_to_copy = [ Attribute::ID, Attribute::CLASS_ ];
		foreach ( $attributes_to_copy as $attribute_name ) {
			if ( $element->hasAttribute( $attribute_name ) ) {
				$attributes[ $attribute_name ] = $element->getAttribute( $attribute_name );
			}
		}

		$amp_element = AMP_DOM_Utils::create_node( $element->ownerDocument, Extension::GOOGLE_DOCUMENT_EMBED, $attributes );
		$element->parentNode->replaceChild( $amp_element, $element );
	}
}
