<?php
/**
 * Class AMP_Comments_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Comments_Sanitizer
 *
 * Strips and corrects attributes in forms.
 */
class AMP_Comments_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @var string HTML <form> tag to identify and process.
	 *
	 * @since 0.7
	 */
	public static $tag = 'amp-live-list';

	/**
	 * Sanitize the comments list from the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.7
	 */
	public function sanitize() {

		/**
		 * Node list.
		 *
		 * @var DOMNodeList $node
		 */
		$nodes     = $this->dom->getElementsByTagName( self::$tag );
		$num_nodes = $nodes->length;

		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = 0; $i < $nodes->length; $i++ ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			$wrapper = $node->parentNode->cloneNode();
			$items   = $node->firstChild->childNodes;
			$wrapper->setAttribute( 'items', '' );
			if ( $items->length ) {
				for ( $c = 0; $c < $items->length; ) {
					$child = $items->item( $c );
					if ( $child instanceof DOMElement ) {
						$time = $child->lastChild->getAttributeNode( 'data-sort-time' );
						$child->setAttributeNode( $time );
					}
					$wrapper->appendChild( $child );
				}
			}
			$node->replaceChild( $wrapper, $node->firstChild );
			$node->parentNode->parentNode->replaceChild( $node, $node->parentNode );
		}
	}
}