<?php
/**
 * Class AMP_Site_Validation
 *
 * @package AMP
 */

/**
 * Class AMP_Site_Validation
 *
 * @since 1.0
 */
class AMP_Site_Validation {

	/**
	 * Get the post permalinks to check for AMP validity.
	 *
	 * This excludes 'attachment' post types, as it only searches for posts with the status 'publish.'
	 * Attachments have a default status of 'inherit,' so they can depend on the status of their parent like a post.
	 *
	 * @todo: consider whether this should also return 'attachment' permalinks.
	 * @param int|null $number_posts The maximum amount of posts to get the permalinks for.
	 * @return string[] $permalinks The permalinks, as an array of strings.
	 */
	public static function get_post_permalinks( $number_posts ) {
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		$query      = new WP_Query( array(
			'posts_per_page' => $number_posts,
			'post_type'      => array_values( $post_types ),
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'post_status'    => array( 'publish' ),
		) );

		$permalinks = array();
		foreach ( $query->posts as $post ) {
			$permalinks[] = amp_get_permalink( $post->ID );
		}

		return $permalinks;
	}
}
