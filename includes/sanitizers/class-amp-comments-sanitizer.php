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

			$comment_items = $node->cloneNode( true )->childNodes;
			$parent        = $node->parentNode;
			$parent->removeChild( $node );

			for ( $c = 0; $c < $comment_items->length; $c ++ ) {
				$item = $comment_items->item( $c )->cloneNode( true );
				$parent->appendChild( $item );
			}

			$amp_list = $this->dom->createElement( 'amp-list' );

			$amp_list->setAttribute( 'src', str_replace( 'http:', '', get_rest_url( get_current_blog_id(), 'amp/v1/comments/' . get_the_ID() ) ) );
			$amp_list->setAttribute( 'height', '800' );
			$amp_list->setAttribute( 'single-item', 'true' );
			$amp_list->setAttribute( 'layout', 'fixed-height' );
			$template = $this->dom->createElement( 'template' );
			$template->setAttribute( 'type', 'amp-mustache' );

			$template_wrap = $parent->cloneNode( true );
			$template->appendChild( $template_wrap );
			$amp_list->appendChild( $template );

			$overflow = $this->dom->createElement( 'div' );
			$overflow->setAttribute( 'overflow', '' );
			$overflow->setAttribute( 'role', 'button' );
			$overflow->setAttribute( 'aria-label', 'Show more' );
			$overflow->setAttribute( 'class', 'list-overflow ampstart-btn caps' );
			$overflow->textContent = esc_html__( 'Show More', 'amp' );
			$amp_list->appendChild( $overflow );

			$parent->parentNode->replaceChild( $amp_list, $parent );

		}
	}
}
