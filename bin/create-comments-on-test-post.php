<?php
/**
 * Create comments on test post.
 *
 * @codeCoverageIgnore
 * @package AMP
 */

/**
 * Create comments test post.
 *
 * @throws Exception But when post can't be created or content can't be pulled or updated.
 * @return int Post ID.
 */
function amp_create_comments_test_post() {
	$q = new WP_Query( array( 'name' => 'amp-test-comments' ) );
	if ( $q->have_posts() ) {
		$post    = $q->next_post();
		$post_id = $post->ID;
	} else {
		$post_id = wp_insert_post( array(
			'post_name'    => 'amp-test-comments',
			'post_title'   => 'AMP Test Comments',
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_content' => amp_get_test_random_content( 200 ),
		) );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			throw new Exception( 'The test post could not be added, please try again.' );
		}
	}

	// Add comments.
	if ( 0 <= get_comments_number( $post_id ) ) {
		amp_add_comments( $post_id );
	}

	return $post_id;
}

/**
 * Add Comments to the post.
 *
 * @throws Exception If a comment can't be added.
 * @param int $post_id The post ID to add comments to.
 */
function amp_add_comments( $post_id ) {
	$data = amp_get_test_comment_entries();
	$ids  = array();
	foreach ( $data as $comment ) {
		$comment['comment_post_ID'] = $post_id;
		$yes_no                     = rand( 0, 1 );
		// 50% chance of this comment being a reply :)
		if ( 1 === $yes_no && ! empty( $ids ) ) {
			$comment['comment_parent'] = $ids[ rand( 0, count( $ids ) - 1 ) ];
		}
		$comment_id = wp_insert_comment( $comment );
		if ( ! $comment_id || is_wp_error( $comment_id ) ) {
			throw new Exception( 'The test comment could not be added, please try again.' );
		}
		$ids[] = $comment_id;
	}
}

/**
 * Get test comment entries.
 *
 * @return array Data entries.
 */
function amp_get_test_comment_entries() {
	$comments = array();
	$time     = strtotime( 'now' );
	for ( $i = 0; $i < 30; $i++ ) {
		$author = preg_replace( '/[^\w ]+/', '', amp_get_test_random_content( 2, '' ) );
		$time   = strtotime( '+3 minutes', $time );

		// Add comment.
		$comments[] = array(
			'comment_author'       => ucwords( $author ),
			'comment_author_email' => strtolower( implode( '@', explode( ' ', $author ) ) ) . '.com',
			'comment_author_url'   => strtolower( implode( '.', explode( ' ', $author ) ) ) . '.com',
			'comment_content'      => amp_get_test_random_content( rand( 10, 40 ) ),
			'comment_type'         => 'comment',
			'comment_parent'       => '0',
			'comment_author_IP'    => '127.0.0.1',
			'comment_agent'        => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
			'comment_date'         => date( 'Y-m-d H:i:s', $time ),
			'comment_approved'     => '1',
		);
	}

	return $comments;
}

/**
 * Get random text.
 *
 * @param int    $num_words Number of words. Default 55.
 * @param string $more      Optional. What to append if $text needs to be trimmed. Default '.'.
 *
 * @return string Random text.
 */
function amp_get_test_random_content( $num_words = 55, $more = '.' ) {
	$text = explode( ' ', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit Nullam in pharetra nisl Mauris dictum fermentum malesuada Donec tincidunt, lectus nec tempor eleifend, sem enim rhoncus nibh, nec ultricies ante lectus sit amet mi Proin cursus dolor in nisl varius elementum Aliquam blandit lobortis adipiscing Proin euismod est non feugiat. Venenatis Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aliquam ac neque consectetur, ullamcorper neque quis, commodo ligula In ornare tempus feugiat Suspendisse ut pretium dui, at egestas velit Etiam ultricies, nisl quis gravida condimentum, dui nisl congue ligula, porta consectetur est sapien viverra est Donec quis ipsum quis metus luctus porttitor sed id justo Proin ultricies adipiscing dolor, luctus interdum urna ullamcorper sit amet Phasellus. Mollis erat egestas urna tincidunt viverra Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas Nam est ante, blandit vitae porta sed, consectetur vitae libero Sed sit amet auctor diam, eu ullamcorper nulla Aenean interdum, augue nec ullamcorper aliquam, libero justo pellentesque libero, vitae venenatis velit justo pellentesque enim Nam lobortis tortor non sagittis mollis Integer commodo eget nulla vel tincidunt Cras vitae vestibulum ipsum. A sollicitudin erat Cras feugiat vehicula magna, nec vehicula massa lacinia in Nam cursus arcu cursus felis feugiat, vel tincidunt eros aliquet Sed magna est.' );
	shuffle( $text );

	return wp_trim_words( ucfirst( implode( ' ', $text ) ), $num_words, $more );
}

// Bootstrap.
if ( defined( 'WP_CLI' ) ) {
	try {
		$post_id = amp_create_comments_test_post();
		WP_CLI::success( sprintf( 'Please take a look at: %s', amp_get_permalink( $post_id ) . '#development=1' ) );
	} catch ( Exception $e ) {
		WP_CLI::error( $e->getMessage() );
	}
} else {
	echo "Must be run in WP-CLI via: wp eval-file bin/create-comments-on-test-post.php\n";
	exit( 1 );
}
