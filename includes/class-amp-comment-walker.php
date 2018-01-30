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
	 * Holds the timestamp of the most reacent comment in a thread.
	 *
	 * @since 0.7
	 * @var array
	 */
	private $comment_thread_age = array();

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
	 * @param string     $output Used to append additional content. Passed by reference.
	 * @param WP_Comment $comment Comment data object.
	 * @param int        $depth Optional. Depth of the current comment in reference to parents. Default 0.
	 * @param array      $args Optional. An array of arguments. Default empty array.
	 * @param int        $id Optional. ID of the current comment. Default 0 (unused).
	 */
	public function start_el( &$output, $comment, $depth = 0, $args = array(), $id = 0 ) {

		$new_out = '';
		parent::start_el( $new_out, $comment, $depth, $args, $id );

		if ( 'div' === $args['style'] ) {
			$tag = 'div';
		} else {
			$tag = 'li';
		}
		$new_tag = '<' . $tag . ' data-sort-time="' . strtotime( $comment->comment_date ) . '" ';

		if ( ! empty( $this->comment_thread_age[ $comment->comment_ID ] ) ) {
			$new_tag .= 'data-update-time="' . $this->comment_thread_age[ $comment->comment_ID ] . '" ';
		}

		$output .= $new_tag . substr( ltrim( $new_out ), strlen( $tag ) + 1 );

	}

	/**
	 * Output amp-list template code and place holder for comments.
	 *
	 * @see Walker::paged_walk()
	 * @param WP_Comment[] $elements List of comment Elements.
	 * @param int          $max_depth The maximum hierarchical depth.
	 * @param int          $page_num The specific page number, beginning with 1.
	 * @param int          $per_page Per page counter.
	 *
	 * @return string XHTML of the specified page of elements.
	 */
	public function paged_walk( $elements, $max_depth, $page_num, $per_page ) {
		if ( empty( $elements ) || $max_depth < -1 ) {
			return '';
		}

		$this->build_thread_latest_date( $elements );

		$args = array_slice( func_get_args(), 4 );

		$wrapper_id = 'amp-live-comments-list-' . get_queried_object_id();
		$output     = '<amp-live-list layout="container" data-poll-interval="15000" data-max-items-per-page="20" id="' . esc_attr( $wrapper_id ) . '">';
		$output    .= '<' . esc_attr( $args[0]['style'] ) . ' items>';
		$output    .= parent::paged_walk( $elements, $max_depth, $page_num, $per_page, $args[0] );
		$output    .= '</' . esc_attr( $args[0]['style'] ) . '>';
		$output    .= '<button update on="tap:' . esc_attr( $wrapper_id ) . '.update" class="ampstart-btn ml1 caps">' . esc_html__( 'New comments posted.', 'amp' ) . '</button>';
		$output    .= '<div pagination>' . get_the_comments_navigation() . '</div>';
		$output    .= '</amp-live-list>';

		return $output;
	}

	/**
	 * Find the timestamp of the latest child comment of a thread to set the updated time.
	 *
	 * @since 0.7
	 * @param WP_Comment[] $elements The list of comments to get thread times for.
	 * @param int          $time $the timestamp to check against.
	 * @param bool         $is_child Flag used to set the the value or return the time.
	 * @return false|int
	 */
	public function build_thread_latest_date( $elements, $time = 0, $is_child = false ) {

		foreach ( $elements as $element ) {

			$children  = $element->get_children();
			$this_time = strtotime( $element->comment_date );
			if ( ! empty( $children ) ) {
				$this_time = $this->build_thread_latest_date( $children, $this_time, true );
			}
			if ( $this_time > $time ) {
				$time = $this_time;
			}
			if ( false === $is_child ) {
				$this->comment_thread_age[ $element->comment_ID ] = $time;
			}
		}

		return $time;
	}
}
