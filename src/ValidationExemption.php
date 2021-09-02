<?php
/**
 * Class ValidationExemption.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use DOMAttr;
use DOMNode;

/**
 * Helper functionality to deal with validation exemptions.
 *
 * @package AmpProject\AmpWP
 * @since 2.2
 * @internal
 * @see \AmpProject\DevMode
 */
final class ValidationExemption {

	/**
	 * HTML attribute to indicate one or more attributes have been verified for PX from AMP validation.
	 *
	 * @var string
	 */
	const PX_VERIFIED_ATTRS_ATTRIBUTE = 'data-px-verified-attrs';

	/**
	 * HTML attribute to indicate an tag/element has been verified for PX.
	 *
	 * The difference here with `data-amp-unvalidated-tag` is that the PX-verified means that the tag will work
	 * properly Bento components and CSS tree shaking.
	 *
	 * @var string
	 */
	const PX_VERIFIED_TAG_ATTRIBUTE = 'data-px-verified-tag';

	/**
	 * HTML attribute to indicate one or more attributes are exempted from AMP validation.
	 *
	 * @var string
	 */
	const AMP_UNVALIDATED_ATTRS_ATTRIBUTE = 'data-amp-unvalidated-attrs';

	/**
	 * HTML attribute to indicate an tag/element is exempted from AMP validation.
	 *
	 * @var string
	 */
	const AMP_UNVALIDATED_TAG_ATTRIBUTE = 'data-amp-unvalidated-tag';

	/**
	 * Check whether PX is verified for node.
	 *
	 * This means that it is exempted from AMP validation, but it doesn't mean the PX is negatively impacted.
	 *
	 * @param DOMNode $node Node.
	 * @return bool Whether node marked as being PX-verified.
	 */
	public static function is_px_verified_for_node( DOMNode $node ) {

		// Check element.
		if (
			$node instanceof Element
			&&
			$node->hasAttribute( self::PX_VERIFIED_TAG_ATTRIBUTE )
		) {
			return true;
		}

		// Check attribute.
		if (
			$node instanceof DOMAttr
			&&
			self::check_for_attribute_token_list_membership( $node, self::PX_VERIFIED_ATTRS_ATTRIBUTE )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Mark node as being PX-verified.
	 *
	 * @param DOMNode $node Node.
	 * @return bool Whether successful.
	 */
	public static function mark_node_as_px_verified( DOMNode $node ) {

		if ( $node instanceof Element ) {
			if ( ! $node->hasAttribute( self::PX_VERIFIED_TAG_ATTRIBUTE ) ) {
				$dom = self::get_document( $node );
				$node->setAttributeNode( $dom->createAttribute( self::PX_VERIFIED_TAG_ATTRIBUTE ) );
			}
			return true;
		}

		if ( $node instanceof DOMAttr ) {
			$element = $node->parentNode;
			if ( ! $element instanceof Element ) {
				return false;
			}

			$attr_value = $element->getAttribute( self::PX_VERIFIED_ATTRS_ATTRIBUTE );
			if ( $attr_value ) {
				$attr_value .= ' ' . $node->nodeName;
			} else {
				$attr_value = $node->nodeName;
			}

			$element->setAttribute( self::PX_VERIFIED_ATTRS_ATTRIBUTE, $attr_value );
			return true;
		}

		return false;
	}

	/**
	 * Mark node as not being AMP-validated.
	 *
	 * @param DOMNode $node Node.
	 * @return bool Whether successful.
	 */
	public static function mark_node_as_amp_unvalidated( DOMNode $node ) {

		if ( $node instanceof Element ) {
			if ( ! $node->hasAttribute( self::AMP_UNVALIDATED_TAG_ATTRIBUTE ) ) {
				$dom = self::get_document( $node );
				$node->setAttributeNode( $dom->createAttribute( self::AMP_UNVALIDATED_TAG_ATTRIBUTE ) );
			}
			return true;
		}

		if ( $node instanceof DOMAttr ) {
			$element = $node->parentNode;
			if ( ! $element instanceof Element ) {
				return false;
			}

			$attr_value = $element->getAttribute( self::AMP_UNVALIDATED_ATTRS_ATTRIBUTE );
			if ( $attr_value ) {
				$attr_value .= ' ' . $node->nodeName;
			} else {
				$attr_value = $node->nodeName;
			}

			$element->setAttribute( self::AMP_UNVALIDATED_ATTRS_ATTRIBUTE, $attr_value );
			return true;
		}

		return false;
	}

	/**
	 * Check whether AMP is unvalidated for node.
	 *
	 * This means that it is exempted from AMP validation and it may negatively impact PX.
	 *
	 * @param DOMNode $node Node.
	 * @return bool Whether node marked as being AMP-unvalidated.
	 */
	public static function is_amp_unvalidated_for_node( DOMNode $node ) {

		// Prevent removing a tag which was exempted from validation.
		if (
			$node instanceof \DOMElement
			&&
			$node->hasAttribute( self::AMP_UNVALIDATED_TAG_ATTRIBUTE )
		) {
			return true;
		}

		// Check attribute.
		if (
			$node instanceof DOMAttr
			&&
			self::check_for_attribute_token_list_membership( $node, self::AMP_UNVALIDATED_ATTRS_ATTRIBUTE )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check whether the given attribute node is mentioned among the token list of another supplied attribute.
	 *
	 * @param DOMAttr $attr                 Attribute node to check for.
	 * @param string  $token_list_attr_name Attribute name that has the token list value.
	 *
	 * @return bool Whether membership is present.
	 */
	private static function check_for_attribute_token_list_membership( DOMAttr $attr, $token_list_attr_name ) {
		$element = $attr->parentNode;
		if ( ! $element instanceof Element ) {
			return false;
		}
		return (
			$element->hasAttribute( $token_list_attr_name )
			&&
			in_array(
				$attr->nodeName,
				preg_split( '/\s+/', $element->getAttribute( $token_list_attr_name ) ),
				true
			)
		);
	}

	/**
	 * Check whether the provided document has nodes which are not AMP-validated.
	 *
	 * @param Document $document Document for which to check whether dev mode is active.
	 * @return bool Whether the document is in dev mode.
	 */
	public static function is_document_with_amp_unvalidated_nodes( Document $document ) {
		foreach ( [ self::AMP_UNVALIDATED_TAG_ATTRIBUTE, self::AMP_UNVALIDATED_ATTRS_ATTRIBUTE ] as $attr_name ) {
			if ( $document->xpath->query( "//*/@{$attr_name}" )->length > 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check whether the provided document has nodes which are PX-verified.
	 *
	 * @param Document $document Document for which to check whether dev mode is active.
	 * @return bool Whether the document is in dev mode.
	 */
	public static function is_document_with_px_verified_nodes( Document $document ) {
		foreach ( [ self::PX_VERIFIED_TAG_ATTRIBUTE, self::PX_VERIFIED_ATTRS_ATTRIBUTE ] as $attr_name ) {
			if ( $document->xpath->query( "//*/@{$attr_name}" )->length > 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the document from the specified node.
	 *
	 * @param DOMNode $node The Node from which the document should be retrieved.
	 * @return Document
	 */
	private static function get_document( DOMNode $node ) {
		$document = $node->ownerDocument;
		if ( ! $document instanceof Document ) {
			$document = Document::fromNode( $node );
		}

		return $document;
	}
}
