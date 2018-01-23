<?php
/**
 * AMP Rest Functions
 *
 * @package AMP
 */

/**
 * Register the comments REST endpoint.
 *
 * @since 0.7
 */
function amp_register_endpoints() {

	register_rest_route( 'amp/v1', '/comments/(?P<id>\d+)', array(
		'methods'  => 'GET',
		'callback' => 'amp_get_comments',
	) );

}

/**
 * Get comments for the Post and Parent
 *
 * @since 0.7
 * @param WP_REST_Request $request The REST request.
 *
 * @return array The array of comments.
 */
function amp_get_comments( $request ) {

	$id       = $request->get_param( 'id' );
	$comments = get_comments( array(
		'post_ID' => $id,
		'order'   => 'ASC',
	) );
	$return   = array(
		'items' => array(
			'comment' => amp_get_comments_recursive( 0, $comments ),
		),
	);

	return $return;
}

/**
 * Recursively get comments for the Post and Parent
 *
 * @since 0.7
 * @param int   $parent The comment parent.
 * @param array $comments The list of comments.
 *
 * @return array The array of comments.
 */
function amp_get_comments_recursive( $parent, $comments ) {

	$return = array();
	foreach ( $comments as $comment ) {
		if ( (int) $parent !== (int) $comment->comment_parent ) {
			continue;
		}
		$GLOBALS['comment']    = $comment; // WPCS: override ok.
		$comment->comment_date = get_comment_date();
		$comment->comment_time = get_comment_time();
		$comment->comment      = amp_get_comments_recursive( $comment->comment_ID, $comments );
		$return[]              = $comment;
	}

	return $return;
}
