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
	 * Gets the post IDs of all public post types with the status 'publish,' to check for AMP validity.
	 *
	 * This excludes 'attachment' post types, as it only searches for posts with the status 'publish.'
	 * Attachments have a default status of 'inherit,' so they can depend on the status of their parent like a post.
	 *
	 * @todo: consider whether this should also return 'attachment' IDs.
	 * @param int $number_posts The maximum amount of posts to get the IDs for (optional).
	 * @return int[] $post_ids The post IDs in an array.
	 */
	public static function get_post_ids( $number_posts = 200 ) {
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		$query      = new WP_Query( array(
			'posts_per_page' => $number_posts,
			'post_type'      => array_values( $post_types ),
			'post_status'    => 'publish',
		) );

		return wp_list_pluck( $query->posts, 'ID' );
	}

	/**
	 * Validates the URLs on the site.
	 *
	 * @todo: Consider wrapping this function with another, as different use cases will probably require a different return value or display.
	 * For example, the <button> in /wp-admin that makes an AJAX request for this will need a different response than a WP-CLI command.
	 * @return array $validation_result The post ID as the index, and the result of validation as the value.
	 */
	public static function validate_site_urls() {
		$site_post_ids = self::get_post_ids();
		foreach ( $site_post_ids as $id ) {
			AMP_Validation_Manager::$posts_pending_frontend_validation[ $id ] = true;
		}
		return AMP_Validation_Manager::validate_queued_posts_on_frontend();
	}
}
