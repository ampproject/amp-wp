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
	 * Gets the permalinks of all public post types with the status 'publish,' to check for AMP validity.
	 *
	 * This excludes 'attachment' post types, as it only searches for posts with the status 'publish.'
	 * Attachments have a default status of 'inherit,' so they can depend on the status of their parent like a post.
	 *
	 * @todo: consider whether this should also return 'attachment' IDs.
	 * @param int $number_posts The maximum amount of posts to get the permalinks for (optional).
	 * @return string[] $permalinks The post permalinks in an array.
	 */
	public static function get_post_permalinks( $number_posts = 200 ) {
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		$query      = new WP_Query( array(
			'posts_per_page' => $number_posts,
			'post_type'      => array_values( $post_types ),
			'post_status'    => 'publish',
		) );

		$post_ids = wp_list_pluck( $query->posts, 'ID' );
		return array_map( 'get_permalink', $post_ids );
	}

	/**
	 * Gets the front-end links for public taxonomy terms, like categories and tags.
	 *
	 * For example, https://example.org/?cat=2
	 * This includes categories and tags, and any more that are registered.
	 * But it excludes post_format terms.
	 *
	 * @param string $taxonomy     The name of the taxonomy.
	 * @param int    $number_links The maximum amount of links to get (optional).
	 * @param int    $offset       The number at which to offset the query (optional).
	 * @return string[] $links The term links in an array.
	 */
	public static function get_taxonomy_links( $taxonomy, $number_links = 200, $offset = null ) {
		$args = array(
			'taxonomy' => $taxonomy,
			'orderby'  => 'id',
			'number'   => $number_links,
		);
		if ( is_int( $offset ) ) {
			$args = array_merge( $args, compact( 'offset' ) );
		}

		return array_map( 'get_term_link', get_terms( $args ) );
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
