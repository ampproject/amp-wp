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
	 * @since 0.2
	 */
	public static $tag = 'comments-template';

	/**
	 * Sanitize the <img> elements from the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.2
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

		for ( $i = 0; $i < $nodes->length; $i ++ ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			// Get the comment template parts.
			$comment_items = $node->cloneNode( true )->childNodes;
			$parent        = $node->parentNode;
			$parent->removeChild( $node );
			// Get the internal amp-list.
			$list = $parent->firstChild->cloneNode( true );
			// Get the internal template.
			$template = $list->firstChild;
			// Remove the holder template so we have an empty parent wrapper.
			$parent->removeChild( $parent->firstChild );
			$new_parent = $parent->cloneNode();

			// Add each of the template parts.
			for ( $c = 0; $c < $comment_items->length; $c ++ ) {
				$item = $comment_items->item( $c )->cloneNode( true );
				$new_parent->appendChild( $item );
			}

			// Add wrapped template to the template element.
			$template->appendChild( $new_parent );
			// Replace the old comments wrapper with the new one.
			$parent->parentNode->replaceChild( $list, $parent );

		}
	}
}
