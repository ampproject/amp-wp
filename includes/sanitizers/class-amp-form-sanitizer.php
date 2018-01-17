<?php
/**
 * Class AMP_Form_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Form_Sanitizer
 *
 * Strips and corrects attributes in forms.
 */
class AMP_Form_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @var string HTML <form> tag to identify and process.
	 *
	 * @since 0.2
	 */
	public static $tag = 'form';

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

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			if ( ! $node->hasAttribute( 'action' ) || '' === $node->getAttribute( 'action' ) ) {
				$node->parentNode->removeChild( $node );
				continue;
			}

			// Correct action.
			if ( $node->hasAttribute( 'action' ) && ! $node->hasAttribute( 'action-xhr' ) ) {
				$action_url = $node->getAttribute( 'action' );
				$action_url = str_replace( 'http:', '', $action_url );
				$node->setAttribute( 'action', $action_url );

				if ( 'post' === $node->getAttribute( 'method' ) ) {
					$node->setAttribute( 'action-xhr', $action_url );
					$node->removeAttribute( 'action' );
				}
			}

			// Set a target if needed.
			if ( ! $node->hasAttribute( 'target' ) ) {
				$node->setAttribute( 'target', '_blank' );
			}
		}

	}

}
