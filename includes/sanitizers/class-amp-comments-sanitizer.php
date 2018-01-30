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

		$node = $nodes->item( 0 );
		if ( ! $node instanceof DOMElement ) {
			return;
		}

		$form = $this->dom->getElementById( 'commentform' );
		if ( $form instanceof DOMElement ) {
			$list_id = $node->getAttribute( 'id' ) . '-fields';
			$form->setAttribute( 'on', 'submit-success:' . $list_id . '.hide' );
		}
	}
}
