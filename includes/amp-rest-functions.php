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

	$id     = $request->get_param( 'id' );
	$return = array(
		'items' => array(
			'comment' => amp_get_comments_recursive( $id, 0 ),
		),
	);

	return $return;
}

/**
 * Recursively get comments for the Post and Parent
 *
 * @since 0.7
 * @param int $id The post ID.
 * @param int $parent The comment parent.
 *
 * @return array The array of comments.
 */
function amp_get_comments_recursive( $id, $parent ) {

	$comments = get_comments( array(
		'post_ID' => $id,
		'parent'  => $parent,
		'order'   => 'ASC',
	) );

	$return = array();
	foreach ( $comments as $comment ) {
		$GLOBALS['comment']    = $comment; // WPCS: override ok.
		$comment->comment_date = get_comment_date();
		$comment->comment_time = get_comment_time();
		$comment->comment      = amp_get_comments_recursive( $id, $comment->comment_ID );
		$return[]              = $comment;
	}

	return $return;
}
