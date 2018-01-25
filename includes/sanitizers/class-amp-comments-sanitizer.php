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
	public static $tag = 'comment-template';

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

		for ( $i = 0; $i < $nodes->length; $i++ ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			// Get the comment template layer parts. (wrapper, amp-list, template, comments-template).
			$ol           = $node->parentNode;
			$new_wrap     = $node->parentNode->cloneNode();
			$amp_list     = $ol->getElementsByTagName( 'amp-list' )->item( 0 );
			$amp_template = $ol->getElementsByTagName( 'template' )->item( 0 );
			if ( ! $amp_list instanceof DOMElement || ! $amp_template instanceof DOMElement ) {
				continue;
			}

			// Move Dom parts to an AMP structure.
			$new_wrap->appendChild( $node );
			$amp_template->appendChild( $new_wrap );
			$ol->parentNode->replaceChild( $amp_list, $ol );

			// Convert Links for templating.
			$links = $amp_template->getElementsByTagName( 'a' );
			$this->convert_urls( $links, 'href' );

			// Convert image src for templating.
			$imgs = $amp_template->getElementsByTagName( 'amp-img' );
			if ( $imgs->length ) {
				$this->convert_urls( $imgs, 'src' );
				$this->convert_urls( $imgs, 'srcset' );
			}
		}

		// cleanup comments form.
		$comment_form = $this->dom->getElementById( 'commentform' );
		if ( $comment_form instanceof DOMElement ) {
			$comment_form->setAttribute( 'on', 'submit-success:amp-comment-form-fields.hide' );
		}
	}

	/**
	 * Convert comment_[field]_url to mustache template strings.
	 *
	 * @since 0.2
	 * @param DOMNodeList $node_list The list of nodes to convert.
	 * @param string      $type The type of attribute to convert.
	 */
	private function convert_urls( $node_list, $type = 'href' ) {

		for ( $a = 0; $a < $node_list->length; $a++ ) {
			$node = $node_list->item( $a );
			if ( ! $node->hasAttribute( $type ) ) {
				continue;
			}
			$url = preg_replace( '/http[s]?:\/\/(comment_[a-z_]+_url)/', '{{$1}}', $node->getAttribute( $type ) );
			$node->setAttribute( $type, $url );
		}
	}
}
