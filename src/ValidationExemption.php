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
 * @todo This could be made into a Service that contains a collection of nodes which were PX-verified or AMP-unvalidated. In this way, there would be no need to add attributes to DOM nodes, and non-element/attribute nodes could be marked, and it would be faster to see if there are any such exempted nodes.
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
		if ( $node instanceof Element ) {
			return $node->hasAttribute( self::PX_VERIFIED_TAG_ATTRIBUTE );
		} elseif ( $node instanceof DOMAttr ) {
			return self::check_for_attribute_token_list_membership( $node, self::PX_VERIFIED_ATTRS_ATTRIBUTE );
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
		return self::mark_node_with_exemption_attribute(
			$node,
			self::PX_VERIFIED_TAG_ATTRIBUTE,
			self::PX_VERIFIED_ATTRS_ATTRIBUTE
		);
	}

	/**
	 * Mark node as not being AMP-validated.
	 *
	 * @param DOMNode $node Node.
	 * @return bool Whether successful.
	 */
	public static function mark_node_as_amp_unvalidated( DOMNode $node ) {
		return self::mark_node_with_exemption_attribute(
			$node,
			self::AMP_UNVALIDATED_TAG_ATTRIBUTE,
			self::AMP_UNVALIDATED_ATTRS_ATTRIBUTE
		);
	}

	/**
	 * Mark node with exemption attribute.
	 *
	 * @param DOMNode $node                 Node.
	 * @param string  $tag_attribute_name   Tag attribute name.
	 * @param string  $attrs_attribute_name Attributes attribute name.
	 * @return bool
	 */
	private static function mark_node_with_exemption_attribute( DOMNode $node, $tag_attribute_name, $attrs_attribute_name ) {
		if ( $node instanceof Element ) {
			if ( ! $node->hasAttribute( $tag_attribute_name ) ) {
				if ( null === $node->ownerDocument ) {
					return false; // @codeCoverageIgnore
				}
				$node->setAttributeNode( $node->ownerDocument->createAttribute( $tag_attribute_name ) );
			}
			return true;
		} elseif ( $node instanceof DOMAttr ) {
			$element = $node->parentNode;
			if ( ! $element instanceof Element ) {
				return false; // @codeCoverageIgnore
			}

			$attr_value = $element->getAttribute( $attrs_attribute_name );
			if ( $attr_value ) {
				$attr_value .= ' ' . $node->nodeName;
			} else {
				$attr_value = $node->nodeName;
			}

			$element->setAttribute( $attrs_attribute_name, $attr_value );
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
		if ( $node instanceof Element ) {
			return $node->hasAttribute( self::AMP_UNVALIDATED_TAG_ATTRIBUTE );
		} elseif ( $node instanceof DOMAttr ) {
			return self::check_for_attribute_token_list_membership( $node, self::AMP_UNVALIDATED_ATTRS_ATTRIBUTE );
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
			return false; // @codeCoverageIgnore
		}
		if ( ! $element->hasAttribute( $token_list_attr_name ) ) {
			return false;
		}
		return in_array(
			$attr->nodeName,
			preg_split( '/\s+/', $element->getAttribute( $token_list_attr_name ) ),
			true
		);
	}

	/**
	 * Check whether the provided document has nodes which are not AMP-validated.
	 *
	 * @param Document $document Document for which to check whether dev mode is active.
	 * @return bool Whether the document is in dev mode.
	 */
	public static function is_document_with_amp_unvalidated_nodes( Document $document ) {
		return self::is_document_containing_attributes( $document, [ self::AMP_UNVALIDATED_TAG_ATTRIBUTE, self::AMP_UNVALIDATED_ATTRS_ATTRIBUTE ] );
	}

	/**
	 * Check whether the provided document has nodes which are PX-verified.
	 *
	 * @param Document $document Document for which to check whether dev mode is active.
	 * @return bool Whether the document is in dev mode.
	 */
	public static function is_document_with_px_verified_nodes( Document $document ) {
		return self::is_document_containing_attributes( $document, [ self::PX_VERIFIED_TAG_ATTRIBUTE, self::PX_VERIFIED_ATTRS_ATTRIBUTE ] );
	}

	/**
	 * Check whether a document contains the given attribute names.
	 *
	 * @param Document $document        Document.
	 * @param string[] $attribute_names Attribute names.
	 * @return bool Whether attributes exist in the document.
	 */
	private static function is_document_containing_attributes( Document $document, $attribute_names ) {
		return $document->xpath->query(
			sprintf(
				'//*[%s]',
				join(
					' or ',
					array_map(
						static function ( $attr_name ) {
							return "@{$attr_name}";
						},
						$attribute_names
					)
				)
			)
		)->length > 0;
	}
}
