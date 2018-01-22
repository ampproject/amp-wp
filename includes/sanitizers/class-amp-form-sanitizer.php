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

		for ( $i = $num_nodes - 1; $i >= 0; $i -- ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			$method = 'get';
			if ( $node->hasAttribute( 'method' ) ) {
				$method = strtolower( $node->getAttribute( 'method' ) );
			}

			// Get the action URL.
			if ( ! $node->hasAttribute( 'action' ) ) {
				$action_url = esc_url_raw( '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); // WPCS: ignore. input var okay, sanitization ok.
			} else {
				$action_url = $node->getAttribute( 'action' );
			}

			if ( strpos( $action_url, 'http:' ) === 0 ) {
				$action_url = substr( $action_url, 5 );
			}

			$node->setAttribute( 'action', $action_url );
			if ( 'post' === $method ) {
				$node->setAttribute( 'action-xhr', $action_url );
				$node->removeAttribute( 'action' );
			} else {
				$node->setAttribute( 'action-xhr', $action_url );
			}

			// Set a target if needed.
			if ( ! $node->hasAttribute( 'target' ) ) {
				$node->setAttribute( 'target', '_top' );
			}
		}
	}
}
