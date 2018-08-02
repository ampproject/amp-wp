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
	 * The size of the batch of URLs to validate.
	 * Grouped in a batch to avoid making loopback requests for all of a site's URLs at the same time.
	 *
	 * @var int
	 */
	const BATCH_SIZE = 100;

	/**
	 * The WP-CLI argument to validate the site.
	 * The full command is: wp amp validate-site.
	 *
	 * @var int
	 */
	const WP_CLI_ARGUMENT = 'validate-site';

	/**
	 * The WP CLI progress bar.
	 *
	 * @see https://make.wordpress.org/cli/handbook/internal-api/wp-cli-utils-make-progress-bar/
	 * @var cli\progress\Bar|WP_CLI\NoOp
	 */
	public static $wp_cli_progress;

	/**
	 * All of the site validation errors, regardless of whether they were accepted.
	 *
	 * @var int
	 */
	public static $total_errors = 0;

	/**
	 * All of the unaccepted site validation errors.
	 *
	 * If an error has been accepted in the /wp-admin validation UI,
	 * it won't count toward this.
	 *
	 * @var int
	 */
	public static $unaccepted_errors = 0;

	/**
	 * The number of URLs crawled, regardless of whether they have validation errors.
	 *
	 * @var int
	 */
	public static $number_crawled = 0;

	/**
	 * Inits the class.
	 */
	public static function init() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'amp', array( __CLASS__, 'crawl_site' ) );
		}
	}

	/**
	 * Crawls the entire site to validate it, and gets the results.
	 *
	 * @param array $args The arguments for the command.
	 */
	public function crawl_site( $args ) {
		if ( ! isset( $args[0] ) || ! self::WP_CLI_ARGUMENT === $args[0] ) {
			return;
		}

		$number_urls_to_crawl = self::count_posts_and_terms();
		WP_CLI::log( sprintf( __( 'Crawling the entire site for AMP validity.', 'amp' ) ) );

		self::$wp_cli_progress = WP_CLI\Utils\make_progress_bar(
			/* translators: %d is the number of URLs. */
			sprintf( __( 'Validating %d URLs...', 'amp' ), $number_urls_to_crawl ),
			$number_urls_to_crawl
		);
		self::validate_entire_site_urls();
		self::$wp_cli_progress->finish();

		$url_more_details = add_query_arg(
			'post_type',
			\AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
			admin_url( 'edit.php' )
		);

		WP_CLI::success(
			sprintf(
				/* translators: $1%d is the number of URls crawled, $2%d is the number of validation issues, $3%d is the number of unaccepted issues, $4%s is the link for more details */
				__( "Of the %1\$d URLs crawled, %2\$d have AMP validation issue(s), and %3\$d have unaccepted issue(s).\nFor more details, please see: \n%4\$s", 'amp' ),
				self::$number_crawled,
				self::$total_errors,
				self::$unaccepted_errors,
				$url_more_details
			)
		);
	}

	/**
	 * Finds the total number of posts and terms on the site.
	 *
	 * The access level is private,
	 * as this passes 'posts_per_page' => -1 to WP_Query().
	 * This is only intended for use with WP-CLI,
	 * and it's a performance risk otherwise.
	 *
	 * @return int The number of posts and terms.
	 */
	private static function count_posts_and_terms() {
		$total_count       = 0;
		$public_post_types = get_post_types( array( 'public' => true ), 'names' );
		$term_query        = new WP_Term_Query( array(
			'taxonomy' => get_taxonomies( array( 'public' => true ) ),
			'fields'   => 'ids',
		) );
		$total_count      += count( $term_query->terms );

		/**
		 * Because of the posts_per_page => -1 value, this is only suited for use in WP-CLI.
		 * This is necessary to pass as an argument to WP_CLI\Utils\make_progress_bar(),
		 * in order to show the progress of every post.
		 */
		$post_query   = new WP_Query( array(
			'post_type'      => $public_post_types,
			'fields'         => 'ids',
			'posts_per_page' => -1,
		) );
		$total_count += $post_query->found_posts;

		// Attachment posts usually have the post_status of 'inherit,' so they can use the status of the post they're attached to.
		if ( in_array( 'attachment', $public_post_types, true ) ) {
			$attachment_query = new WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'fields'         => 'ids',
				'posts_per_page' => -1,
			) );
			$total_count     += $attachment_query->found_posts;
		}

		return $total_count;
	}

	/**
	 * Gets the permalinks of public, published posts.
	 *
	 * @param string $post_type The post type.
	 * @param int    $number_posts The maximum amount of posts to get the permalinks for (optional).
	 * @param int    $offset The offset of the query (optional).
	 * @return string[] $permalinks The post permalinks in an array.
	 */
	public static function get_post_permalinks( $post_type, $number_posts = 200, $offset = null ) {
		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => $number_posts,
			'post_status'    => 'publish',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
		);
		if ( is_int( $offset ) ) {
			$args = array_merge( $args, compact( 'offset' ) );
		}

		// Attachment posts usually have the post_status of 'inherit,' so they can use the status of the post they're attached to.
		if ( 'attachment' === $post_type ) {
			$args['post_status'] = 'inherit';
		}
		$query = new WP_Query( $args );

		return array_map( 'get_permalink', $query->posts );
	}

	/**
	 * Gets the front-end links for public taxonomy terms, like categories and tags.
	 *
	 * For example, https://example.org/?cat=2
	 * This includes categories and tags, and any more that are registered.
	 * But it excludes post_format terms.
	 *
	 * @param string $taxonomy     The name of the taxonomy, like 'category' or 'post_tag'.
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
	 * Validates the URLs of the entire site.
	 * Includes the URLs of public, published posts, and public taxonomies.
	 */
	public static function validate_entire_site_urls() {
		// Validate the homepage.
		self::validate_urls( array( home_url( '/' ) ) );

		// Validate all public, published posts.
		$public_post_types = get_post_types( array( 'public' => true ), 'names' );
		foreach ( $public_post_types as $post_type ) {
			$permalinks = self::get_post_permalinks( $post_type, self::BATCH_SIZE );
			$offset     = 0;

			while ( ! empty( $permalinks ) ) {
				self::validate_urls( $permalinks );
				$offset    += self::BATCH_SIZE;
				$permalinks = self::get_post_permalinks( $post_type, self::BATCH_SIZE, $offset );
			}
		}

		// Validate all public taxonomies.
		$public_taxonomies = get_taxonomies( array( 'public' => true ) );
		foreach ( $public_taxonomies as $taxonomy ) {
			$taxonomy_links = self::get_taxonomy_links( $taxonomy, self::BATCH_SIZE );
			$offset         = 0;

			while ( ! empty( $taxonomy_links ) ) {
				self::validate_urls( $taxonomy_links );
				$offset        += self::BATCH_SIZE;
				$taxonomy_links = self::get_taxonomy_links( $taxonomy, self::BATCH_SIZE, $offset );
			}
		}
	}

	/**
	 * Validates a single URL, and increments the counts as needed.
	 *
	 * @param array $urls The URLs to validate.
	 * @return void
	 */
	public static function validate_urls( $urls ) {
		foreach ( $urls as $url ) {
			$validity = AMP_Validation_Manager::validate_url( $url );
			if ( ! is_wp_error( $validity ) ) {
				AMP_Invalid_URL_Post_Type::store_validation_errors( $validity['validation_errors'], $validity['url'] );
				$error_count            = count( $validity['validation_errors'] );
				$unaccepted_error_count = count( array_filter(
					$validity['validation_errors'],
					function( $error ) {
						return ! AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( $error );
					}
				) );

				if ( $error_count > 0 ) {
					self::$total_errors++;
				}
				if ( $unaccepted_error_count > 0 ) {
					self::$unaccepted_errors++;
				}
				if ( self::$wp_cli_progress ) {
					self::$wp_cli_progress->tick();
				}

				self::$number_crawled++;
			}
		}
	}
}
