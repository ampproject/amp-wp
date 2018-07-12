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
	 *
	 * @var int
	 */
	const BATCH_SIZE = 100;

	/**
	 * The argument to validate the site.
	 *
	 * @var int
	 */
	const WP_CLI_ARGUMENT = 'validate-site';

	/**
	 * The WP CLI progress bar.
	 *
	 * @var cli\progress\Bar|WP_CLI\NoOp
	 */
	public static $wp_cli_progress;

	/**
	 * All of the site validation URLs that have at least one unaccepted error.
	 *
	 * @var array
	 */
	public static $site_invalid_urls = array();

	/**
	 * All of the site validation errors.
	 *
	 * @var array
	 */
	public static $total_errors = 0;

	/**
	 * The number of URLs crawled.
	 *
	 * @var int
	 */
	public static $number_crawled = 0;

	/**
	 * Inits the class.
	 */
	public static function init() {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}
		WP_CLI::add_command( 'amp', array( __CLASS__, 'crawl_site' ) );
	}

	/**
	 * Crawls the entire site to validate it, and gets the results.
	 *
	 * @param array $args The arguments for the command.
	 */
	public function crawl_site( $args ) {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! isset( $args[0] ) && self::WP_CLI_ARGUMENT !== $args[0] ) {
			return;
		}

		WP_CLI::log( __( 'Crawling the entire site to test for AMP validity.', 'amp' ) );
		self::$wp_cli_progress = WP_CLI\Utils\make_progress_bar( 'Validating URLs...', self::count_posts_and_terms() );
		self::validate_entire_site_urls();
		self::$wp_cli_progress->finish();

		$url_more_details = add_query_arg(
			'post_type',
			\AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
			admin_url( 'edit.php' )
		);

		WP_CLI::success(
			sprintf(
				/* Translators: $1%d: the number of URls crawled, $2%d: the number of validation issues, $3%s: link for more details */
				__( "%1\$d URLs were crawled, and %2\$d have AMP validation issue(s).\nFor more details, please see: \n%3\$s", 'amp' ),
				self::$number_crawled,
				self::$total_errors,
				$url_more_details
			)
		);
	}

	/**
	 * Finds the total number of posts and terms on the site.
	 *
	 * @return int The number of posts and terms.
	 */
	public static function count_posts_and_terms() {

		/**
		 * Because of the posts_per_page => -1 value, this is only suited for use in WP-CLI.
		 * This is necessary to pass as an argument to WP_CLI\Utils\make_progress_bar(),
		 * in order to show the progress of every post.
		 */
		$post_query = new WP_Query( array(
			'post_types'     => get_post_types( array( 'public' => true ), 'names' ),
			'fields'         => 'ids',
			'posts_per_page' => -1,
		) );

		$term_query = new WP_Term_Query( array(
			'taxonomy' => get_taxonomies( array( 'public' => true ) ),
			'fields'   => 'ids',
		) );

		return $post_query->found_posts + count( $term_query->terms );
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
	 *
	 * Accepts an optional parameter of a WP-CLI progress bar object.
	 * Calling its tick() method updates the display in WP-CLI to show the percentage of the site crawl that's complete.
	 *
	 * @todo: Consider wrapping this function with another, as different use cases will probably require a different return value or display.
	 * For example, the <button> in /wp-admin that makes an AJAX request for this will need a different response than a WP-CLI command.
	 */
	public static function validate_entire_site_urls() {
		// Validate the homepage.
		self::validate_urls( home_url( '/' ) );

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
	 * Validates a single URL, and stores the URL in a property.
	 *
	 * @todo: Maybe storing the URLS in the property isn't needed, as AMP_Validation_Manager stores the actual errors.
	 * This could be an extremely large array.
	 * For now, this is helpful in unit testing.
	 * @param array|string $urls An array of URLs to validate, or a single string of a URL.
	 * @return void
	 */
	public static function validate_urls( $urls ) {
		if ( is_string( $urls ) ) {
			$urls = array( $urls );
		}

		foreach ( $urls as $url ) {
			if ( AMP_Theme_Support::is_paired_available() ) {
				$url = add_query_arg( 'amp', 1, $url );
			}

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
					self::$site_invalid_urls[] = $url;
				}

				if ( self::$wp_cli_progress ) {
					self::$wp_cli_progress->tick();
				}
			}

			self::$number_crawled++;
		}
	}
}
