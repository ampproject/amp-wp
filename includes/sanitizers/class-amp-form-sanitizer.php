<?php
/**
 * Class AMP_Form_Sanitizer.
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Class AMP_Form_Sanitizer
 *
 * Strips and corrects attributes in forms.
 *
 * @since 0.7
 */
class AMP_Form_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @var string HTML <form> tag to identify and process.
	 *
	 * @since 0.7
	 */
	public static $tag = 'form';

	/**
	 * Sanitize the <form> elements from the HTML contained in this instance's DOMDocument.
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
				$action_url = esc_url_raw( '//' . $_SERVER['HTTP_HOST'] . wp_unslash( $_SERVER['REQUEST_URI'] ) ); // WPCS: ignore. input var okay, sanitization ok.
			} else {
				$action_url = $node->getAttribute( 'action' );
			}

			if ( strpos( $action_url, 'http:' ) === 0 ) {
				$action_url = substr( $action_url, 5 );
			}

			if ( 'post' === $method ) {
				$node->setAttribute( 'action-xhr', $action_url );
				$node->removeAttribute( 'action' );
			} else {
				$node->setAttribute( 'action', $action_url );
			}

			// Set a target if needed.
			if ( ! $node->hasAttribute( 'target' ) ) {
				$node->setAttribute( 'target', '_top' );
			}
		}
	}
}
