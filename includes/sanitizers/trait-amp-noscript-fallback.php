<?php
/**
 * Trait AMP_Noscript_Fallback.
 *
 * @package AMP
 */

/**
 * Trait AMP_Noscript_Fallback
 *
 * @since 1.1
 *
 * Used for sanitizers that place <noscript> tags with the original nodes on error.
 */
trait AMP_Noscript_Fallback {

	/**
	 * Attributes allowed on noscript fallback elements.
	 *
	 * This is used to prevent duplicated validation errors.
	 *
	 * @since 1.1
	 * @var array
	 */
	private $noscript_fallback_allowed_attributes = array();

	/**
	 * Initializes the internal allowed attributes array.
	 *
	 * @since 1.1
	 *
	 * @param string $tag Tag name to get allowed attributes for.
	 */
	protected function initialize_noscript_allowed_attributes( $tag ) {
		$this->noscript_fallback_allowed_attributes = array_fill_keys(
			array_merge(
				array_keys( current( AMP_Allowed_Tags_Generated::get_allowed_tag( $tag ) )['attr_spec_list'] ),
				array_keys( AMP_Allowed_Tags_Generated::get_allowed_attributes() )
			),
			true
		);
	}

	/**
	 * Checks whether the given node is within an AMP-specific <noscript> element.
	 *
	 * @since 1.1
	 *
	 * @param \DOMNode $node DOM node to check.
	 * @return bool True if in an AMP noscript element, false otherwise.
	 */
	protected function is_inside_amp_noscript( \DOMNode $node ) {
		return 'noscript' === $node->parentNode->nodeName && $node->parentNode->parentNode && 'amp-' === substr( $node->parentNode->parentNode->nodeName, 0, 4 );
	}

	/**
	 * Appends the given old node in a <noscript> element to the new node.
	 *
	 * @since 1.1
	 *
	 * @param \DOMNode     $new_node New node to append a noscript with the old node to.
	 * @param \DOMNode     $old_node Old node to append in a noscript.
	 * @param \DOMDocument $dom      DOM document instance.
	 */
	protected function append_old_node_noscript( \DOMNode $new_node, \DOMNode $old_node, \DOMDocument $dom ) {
		$noscript = $dom->createElement( 'noscript' );
		$noscript->appendChild( $old_node );
		$new_node->appendChild( $noscript );

		// Remove all non-allowed attributes preemptively to prevent doubled validation errors.
		for ( $i = $old_node->attributes->length - 1; $i >= 0; $i-- ) {
			$attribute = $old_node->attributes->item( $i );
			if ( isset( $this->noscript_fallback_allowed_attributes[ $attribute->nodeName ] ) ) {
				continue;
			}
			$old_node->removeAttribute( $attribute->nodeName );
		}
	}
}
