<?php
/**
 * Class AMP_Site_Validation
 *
 * @package AMP
 */

/**
 * Class AMP_Site_Validation
 *
 * Registers a WP-CLI command to crawl the entire site to check for AMP validity.
 * To use this, run: wp amp validate-site.
 *
 * @since 1.0
 */
class AMP_Site_Validation {

	/**
	 * The size of the batch of URLs to query and validate.
	 * Grouped in a batch to avoid passing 'posts_per_page' => -1 to WP_Query.
	 *
	 * @var int
	 */
	const BATCH_SIZE = 100;

	/**
	 * The WP-CLI argument to validate the site.
	 * The full command is: wp amp validate-site.
	 *
	 * @var string
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
	 * The total number of validation errors, regardless of whether they were accepted.
	 *
	 * @var int
	 */
	public static $total_errors = 0;

	/**
	 * The total number of unaccepted validation errors.
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
	public static function crawl_site( $args ) {
		if ( ! isset( $args[0] ) || ! self::WP_CLI_ARGUMENT === $args[0] ) {
			return;
		}

		$number_urls_to_crawl = self::count_posts_and_terms();
		WP_CLI::log( __( 'Crawling the entire site for AMP validity.', 'amp' ) );

		self::$wp_cli_progress = WP_CLI\Utils\make_progress_bar(
			/* translators: %d is the number of URLs */
			sprintf( __( 'Validating %d URLs...', 'amp' ), $number_urls_to_crawl ),
			$number_urls_to_crawl
		);
		self::validate_entire_site_urls();
		self::$wp_cli_progress->finish();

		$url_more_details = add_query_arg(
			'post_type',
			AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
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
	 * Gets the total number of posts and terms on the site.
	 *
	 * @return int The number of posts and terms.
	 */
	public static function count_posts_and_terms() {
		/*
		 * If the homepage is set to 'Your latest posts,' start the $total_count at 1.
		 * Otherwise, the homepage wouldn't be counted here, even though validate_entire_site_urls() visits it.
		 * If it's not set to that, it will probably be counted in the query for pages.
		 */
		$total_count       = 'posts' === get_option( 'show_on_front' ) ? 1 : 0;
		$public_post_types = get_post_types( array( 'public' => true ), 'names' );

		// Count all public taxonomy terms.
		$term_query   = new WP_Term_Query( array(
			'taxonomy' => get_taxonomies( array( 'public' => true ) ),
			'fields'   => 'ids',
		) );
		$total_count += count( $term_query->terms );

		/**
		 * Count all public posts.
		 * This batches the queries, to avoid passing posts_per_page => -1 to WP_Query.
		 * After the first iteration, it passes an 'offset' value to WP_Query.
		 * And by passing 'orderby' => 'ID' to WP_Query, it ensures there isn't an issue if a post is added while counting them.
		 */
		$offset = 0;
		$posts  = self::get_posts_by_type( $public_post_types );
		while ( ! empty( $posts ) ) {
			$offset      += self::BATCH_SIZE;
			$total_count += count( $posts );
			$posts        = self::get_posts_by_type( $public_post_types, $offset );
		}

		/**
		 * Count all attachments.
		 * Attachment posts usually have the post_status of 'inherit,' so they can use the status of the post they're attached to.
		 */
		if ( in_array( 'attachment', $public_post_types, true ) ) {
			$offset      = 0;
			$attachments = self::get_posts_by_type( 'attachment' );
			while ( ! empty( $attachments ) ) {
				$offset      += self::BATCH_SIZE;
				$total_count += count( $attachments );
				$attachments  = self::get_posts_by_type( 'attachment', $offset );
			}
		}

		return $total_count;
	}

	/**
	 * Gets the permalinks of public, published posts.
	 *
	 * @param string $post_type The post type.
	 * @param int    $offset The offset of the query (optional).
	 * @return string[] $permalinks The post permalinks in an array.
	 */
	public static function get_posts_by_type( $post_type, $offset = null ) {
		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => self::BATCH_SIZE,
			'post_status'    => 'publish',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
		);
		if ( is_int( $offset ) ) {
			$args['offset'] = $offset;
		}

		// Attachment posts usually have the post_status of 'inherit,' so they can use the status of the post they're attached to.
		if ( 'attachment' === $post_type ) {
			$args['post_status'] = 'inherit';
		}
		$query = new WP_Query( $args );

		return $query->posts;
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
			$permalinks = array_map( 'get_permalink', self::get_posts_by_type( $post_type ) );
			$offset     = 0;

			while ( ! empty( $permalinks ) ) {
				self::validate_urls( $permalinks );
				$offset    += self::BATCH_SIZE;
				$permalinks = array_map( 'get_permalink', self::get_posts_by_type( $post_type, $offset ) );
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

			if ( self::$wp_cli_progress ) {
				self::$wp_cli_progress->tick();
			}
			if ( is_wp_error( $validity ) ) {
				continue;
			}

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

			self::$number_crawled++;
		}
	}
}
