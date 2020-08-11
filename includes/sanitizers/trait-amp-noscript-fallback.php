<?php
/**
 * Trait AMP_Noscript_Fallback.
 *
 * @package AMP
 */

use AmpProject\Dom\Document;

/**
 * Trait AMP_Noscript_Fallback
 *
 * Used for sanitizers that place <noscript> tags with the original nodes on error.
 *
 * @since 1.1
 * @internal
 */
trait AMP_Noscript_Fallback {

	/**
	 * Attributes allowed on noscript fallback elements.
	 *
	 * This is used to prevent duplicated validation errors.
	 *
	 * @since 1.1
	 *
	 * @var array
	 */
	private $noscript_fallback_allowed_attributes = [];

	/**
	 * Initializes the internal allowed attributes array.
	 *
	 * @since 1.1
	 *
	 * @param string $tag Tag name to get allowed attributes for.
	 */
	protected function initialize_noscript_allowed_attributes( $tag ) {
		$this->noscript_fallback_allowed_attributes = array_fill_keys(
			array_keys( AMP_Allowed_Tags_Generated::get_allowed_attributes() ),
			true
		);

		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( $tag ) as $tag_spec ) { // Normally 1 iteration.
			foreach ( $tag_spec['attr_spec_list'] as $attr_name => $attr_spec ) {
				$this->noscript_fallback_allowed_attributes[ $attr_name ] = true;
				if ( isset( $attr_spec['alternative_names'] ) ) {
					$this->noscript_fallback_allowed_attributes = array_merge(
						$this->noscript_fallback_allowed_attributes,
						array_fill_keys( $attr_spec['alternative_names'], true )
					);
				}
			}
		}
	}

	/**
	 * Checks whether the given node is within an AMP-specific <noscript> element.
	 *
	 * @since 1.1
	 *
	 * @param DOMNode $node DOM node to check.
	 *
	 * @return bool True if in an AMP noscript element, false otherwise.
	 */
	protected function is_inside_amp_noscript( DOMNode $node ) {
		return 'noscript' === $node->parentNode->nodeName && $node->parentNode->parentNode && 'amp-' === substr( $node->parentNode->parentNode->nodeName, 0, 4 );
	}

	/**
	 * Appends the given old node in a <noscript> element to the new node.
	 *
	 * @since 1.1
	 *
	 * @param DOMElement $new_element New element to append a noscript with the old element to.
	 * @param DOMElement $old_element Old element to append in a noscript.
	 * @param Document   $dom         DOM document instance.
	 */
	protected function append_old_node_noscript( DOMElement $new_element, DOMElement $old_element, Document $dom ) {
		$noscript = $dom->createElement( 'noscript' );
		$noscript->appendChild( $old_element );
		$new_element->appendChild( $noscript );

		// Remove all non-allowed attributes preemptively to prevent doubled validation errors.
		for ( $i = $old_element->attributes->length - 1; $i >= 0; $i-- ) {
			$attribute = $old_element->attributes->item( $i );
			if ( isset( $this->noscript_fallback_allowed_attributes[ $attribute->nodeName ] ) ) {
				continue;
			}
			$old_element->removeAttribute( $attribute->nodeName );
		}
	}
}
