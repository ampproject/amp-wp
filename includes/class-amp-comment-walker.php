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
	 * Ends the element output, if needed.
	 *
	 * @since 0.7
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
		$output .= '<amp-comment data-sort-time="' . strtotime( $comment->comment_date ) . '"  />';
		parent::end_el( $output, $comment, $depth, $args );
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

		$output  = '<amp-live-list layout="container" data-poll-interval="15000" data-max-items-per-page="20" id="amp-live-comments-list">';
		$output .= '<div items>';
		$output .= parent::paged_walk( $elements, $max_depth, $page_num, $per_page, $args[0] );
		$output .= '</div>';
		$output .= '<button update on="tap:amp-live-comments-list.update" class="ampstart-btn ml1 caps">' . esc_html__( 'You have updates', 'amp' ) . '</button>';
		$output .= '<div pagination></div>';
		$output .= '</amp-live-list>';

		return $output;
	}
}
