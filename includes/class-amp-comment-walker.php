<?php
/**
 * Class AMP_Comment_Walker
 *
 * @package AMP
 */

/**
 * Class AMP_Comment_Walker
 *
 * Walker to wrap comments in mustache tags for amp-template.
 */
class AMP_Comment_Walker extends Walker_Comment {

	/**
	 * The original comments arguments.
	 *
	 * @since 0.7
	 * @var array
	 */
	public $args;

	/**
	 * Starts the element output.
	 *
	 * @since 2.7.0
	 *
	 * @see Walker::start_el()
	 * @see wp_list_comments()
	 * @global int        $comment_depth
	 * @global WP_Comment $comment
	 *
	 * @param string     $output  Used to append additional content. Passed by reference.
	 * @param WP_Comment $comment Comment data object.
	 * @param int        $depth   Optional. Depth of the current comment in reference to parents. Default 0.
	 * @param array      $args    Optional. An array of arguments. Default empty array.
	 * @param int        $id      Optional. ID of the current comment. Default 0 (unused).
	 */
	public function start_el( &$output, $comment, $depth = 0, $args = array(), $id = 0 ) {
		$output .= '{{#comment}}';
		parent::start_el( $output, $comment, $depth, $args, $id );
	}
	/**
	 * Ends the element output, if needed.
	 *
	 * @since 2.7.0
	 *
	 * @see Walker::end_el()
	 * @see wp_list_comments()
	 *
	 * @param string     $output  Used to append additional content. Passed by reference.
	 * @param WP_Comment $comment The current comment object. Default current comment.
	 * @param int        $depth   Optional. Depth of the current comment. Default 0.
	 * @param array      $args    Optional. An array of arguments. Default empty array.
	 */
	public function end_el( &$output, $comment, $depth = 0, $args = array() ) {
		if ( ! empty( $args['end-callback'] ) ) {
			ob_start();
			call_user_func( $args['end-callback'], $comment, $args, $depth );
			$output .= ob_get_clean();
		} else {
			if ( 'div' === $args['style'] ) {
				$output .= "</div><!-- #comment-## -->\n";
			} else {
				$output .= "</li><!-- #comment-## -->\n";
			}
		}
		$output .= '{{/comment}}';
	}

	/**
	 * Output amp-list template code and place holder for comments.
	 *
	 * @see Walker::paged_walk()
	 * @param array $elements List of comment Elements.
	 * @param int   $max_depth The maximum hierarchical depth.
	 * @param int   $page_num The specific page number, beginning with 1.
	 * @param int   $per_page Per page counter.
	 *
	 * @return string XHTML of the specified page of elements.
	 */
	public function paged_walk( $elements, $max_depth, $page_num, $per_page ) {
		if ( empty( $elements ) || $max_depth < - 1 ) {
			return '';
		}

		$args = array_slice( func_get_args(), 4 );
		if ( ! empty( $args[0]['comments_template_placeholder'] ) ) {
			return $args[0]['comments_template_placeholder'];
		}

		$template  = '<comments-template>';
		$template .= parent::paged_walk( $elements, $max_depth, $page_num, $per_page, $args[0] );
		$template .= '</comments-template>';

		$url = get_rest_url( get_current_blog_id(), 'amp/v1/comments/' . get_the_ID() );
		if ( strpos( $url, 'http:' ) === 0 ) {
			$url = substr( $url, 5 );
		}
		// @todo Identify arguments and make filterable/settable.
		$template .= '<amp-list src="' . esc_attr( $url ) . '" height="400" single-item="true" layout="fixed-height">';
		$template .= '<template type="amp-mustache"></template>';
		$template .= '</amp-list>';

		return $template;
	}
}
