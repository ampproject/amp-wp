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
	const WP_CLI_COMMAND = 'amp validate-site';

	/**
	 * The WP-CLI flag to force validation of all URLs.
	 *
	 * By default, this does not validate templates that the user has opted-out of.
	 * For example, by unchecking 'Categories' in 'AMP Settings' > 'Supported Templates'.
	 * But with this command flag, this will validate all URLs.
	 *
	 * @var string
	 */
	const FLAG_NAME_FORCE_VALIDATE_ALL = 'force-validation';

	/**
	 * The query var key to force AMP validation, regardless or whether the user has deselected support for a URL.
	 *
	 * If the WP-CLI flag above is present in the command,
	 * this query var is added to the URL.
	 * Then, this forces validation, no matter whether the user has deselected a certain template.
	 * Like by unchecking 'Categories' in 'AMP Settings' > 'Supported Templates'.
	 *
	 * @var string
	 */
	const FORCE_VALIDATION_QUERY_VAR = 'amp_force_validation';

	/**
	 * The WP-CLI argument to validate based on certain conditionals
	 *
	 * For example, --include=is_tag,is_author
	 * Normally, this script will validate all of the templates that don't have AMP disabled.
	 * But this allows validating only specific templates.
	 *
	 * @var string
	 */
	const INCLUDE_ARGUMENT = 'include';

	/**
	 * The supportable templates, mainly based on a user's selection in 'AMP Settings' > 'Supported Templates'.
	 *
	 * @var array
	 */
	public static $supportable_templates;

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
	 * Whether to force crawling of URLs.
	 *
	 * By default, this script only crawls URLs that support AMP,
	 * where the user hasn't opted-out of AMP for the URL.
	 * For example, by unchecking 'Posts' in 'AMP Settings' > 'Supported Templates'.
	 * Or unchecking 'Enable AMP' in the post's editor.
	 *
	 * @var int
	 */
	public static $force_crawl_urls = false;

	/**
	 * A whitelist of conditionals to use for validation.
	 *
	 * Usually, this script will validate all of the templates that don't have AMP disabled.
	 * But this allows validating based on only these conditionals.
	 *
	 * @var array
	 */
	public static $include_conditionals;

	/**
	 * Adds the WP-CLI command to validate the site.
	 */
	public static function init() {
		if ( class_exists( 'WP_CLI' ) ) {
			self::$supportable_templates = AMP_Theme_Support::get_supportable_templates();

			WP_CLI::add_command(
				self::WP_CLI_COMMAND,
				array( __CLASS__, 'crawl_site' ),
				array(
					'shortdesc' => __( 'Crawl the entire site to get AMP validation results', 'amp' ),
					'synopsis'  => array(
						array(
							'type'     => 'flag',
							'name'     => self::FLAG_NAME_FORCE_VALIDATE_ALL,
							'optional' => true,
						),
						array(
							'type'        => 'assoc',
							'name'        => self::INCLUDE_ARGUMENT,
							'description' => __( 'Only validate if one of the conditionals is true', 'amp' ),
							'optional'    => true,
						),
					),
					'when'      => 'after_wp_load',
					'longdesc'  => '## EXAMPLES' . "\n\n" . 'wp ' . self::WP_CLI_COMMAND . ' --include=is_author,is_tag',
				)
			);
		}
	}

	/**
	 * Crawls the entire site to validate it, and gets the results.
	 *
	 * @param array $args The arguments for the command.
	 * @param array $assoc_args The associative arguments, which can also include command flags like --force-validate-all.
	 */
	public static function crawl_site( $args, $assoc_args ) {
		unset( $args );
		if ( isset( $assoc_args[ self::INCLUDE_ARGUMENT ] ) ) {
			self::$include_conditionals = explode( ',', $assoc_args[ self::INCLUDE_ARGUMENT ] );
			self::$force_crawl_urls     = true;
		} elseif ( isset( $assoc_args[ self::FLAG_NAME_FORCE_VALIDATE_ALL ] ) ) {
			self::$force_crawl_urls = true;
		}

		$number_urls_to_crawl = self::count_posts_and_terms();
		if ( ! $number_urls_to_crawl ) {
			WP_CLI::error(
				sprintf(
					/* translators: %s is the command line argument to force validation */
					__( 'All of your template might be unchecked in AMP Settings > Supported Templates. You might pass --%s to this command.', 'amp' ),
					self::FLAG_NAME_FORCE_VALIDATE_ALL
				)
			);
		}

		WP_CLI::log( __( 'Crawling the site for AMP validity.', 'amp' ) );

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
	 * By default, this only counts AMP-enabled posts and terms.
	 * But if $force_crawl_urls is true, it counts all of them, regardless of their AMP status.
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

		$amp_enabled_taxonomies = array_filter(
			get_taxonomies( array( 'public' => true ) ),
			array( 'AMP_Site_Validation', 'does_taxonomy_support_amp' )
		);

		if ( ! empty( $amp_enabled_taxonomies ) ) {
			// Count all public taxonomy terms.
			$term_query   = new WP_Term_Query( array(
				'taxonomy' => $amp_enabled_taxonomies,
				'fields'   => 'ids',
			) );
			$total_count += count( $term_query->terms );
		}

		/**
		 * Count all public posts.
		 * This batches the queries, to avoid passing posts_per_page => -1 to WP_Query.
		 * After the first iteration, it passes an 'offset' value to WP_Query.
		 * And by passing 'orderby' => 'ID' to WP_Query, it ensures there isn't an issue if a post is added while counting them.
		 */
		$offset   = 0;
		$post_ids = self::get_posts_by_type( $public_post_types );
		while ( ! empty( $post_ids ) ) {
			$offset      += self::BATCH_SIZE;
			$total_count += count( self::get_posts_that_support_amp( $post_ids ) );
			$post_ids     = self::get_posts_by_type( $public_post_types, $offset );
		}

		/**
		 * Count all attachments.
		 * Attachment posts usually have the post_status of 'inherit,' so they can use the status of the post they're attached to.
		 */
		if ( in_array( 'attachment', $public_post_types, true ) ) {
			$offset         = 0;
			$attachment_ids = self::get_posts_by_type( 'attachment' );
			while ( ! empty( $attachment_ids ) ) {
				$offset        += self::BATCH_SIZE;
				$total_count   += count( self::get_posts_that_support_amp( $attachment_ids ) );
				$attachment_ids = self::get_posts_by_type( 'attachment', $offset );
			}
		}

		// Count author pages, like https://example.com/author/admin/.
		$total_count += count( self::get_author_page_urls() );

		// Count a single example search page, like https://example.com/?s=example.
		if ( self::get_search_page() ) {
			$total_count++;
		}

		return $total_count;
	}

	/**
	 * Gets the posts IDs that support AMP.
	 *
	 * By default, only get the post IDs if they support AMP.
	 * This means that 'Posts' isn't deselected in 'AMP Settings' > 'Supported Templates'.
	 * And 'Enable AMP' isn't unchecked in the post's editor.
	 * But if $force_crawl_urls is true, this simply returns all of the IDs.
	 *
	 * @param array $ids The post or term IDs.
	 * @return array The post IDs that support AMP.
	 */
	public static function get_posts_that_support_amp( $ids ) {
		/**
		 * If the user has passed an include argument to the WP-CLI command,
		 * is_singular must be present in that argument.
		 * For example, wp amp validate-site --include=is_singular,is_tag,is_category
		 * That argument is a whitelist of conditionals, one of which must be true to validate a URL.
		 * And is_singular must be true to access posts.
		 */
		if ( ! self::is_template_supported( 'is_singular' ) ) {
			return array();
		}

		if ( self::$force_crawl_urls ) {
			return $ids;
		}

		return array_filter(
			$ids,
			function( $id ) {
				return post_supports_amp( $id );
			}
		);
	}

	/**
	 * Gets whether the taxonomy supports AMP.
	 *
	 * Only get the term IDs if they support AMP.
	 * If their taxonomy is unchecked in 'AMP Settings' > 'Supported Templates,' don't return them.
	 * For example, if 'Categories' is unchecked there, don't return any category IDs.
	 *
	 * @param string $taxonomy The taxonomy.
	 * @return boolean Wether the taxonomy supports AMP.
	 */
	public static function does_taxonomy_support_amp( $taxonomy ) {
		if ( 'post_tag' === $taxonomy ) {
			$taxonomy = 'tag';
		}
		$taxonomy_key        = 'is_' . $taxonomy;
		$custom_taxonomy_key = sprintf( 'is_tax[%s]', $taxonomy );
		return self::is_template_supported( $taxonomy_key ) || self::is_template_supported( $custom_taxonomy_key );
	}

	/**
	 * Gets whether the template is supported.
	 *
	 * If the user has passed an include argument to the WP-CLI command, use that to find if this template supports AMP.
	 * For example, wp amp validate-site --include=is_tag,is_category
	 * would return true only if is_tag() or is_category().
	 * Then, if the user has not unchecked the template in 'AMP Settings' > 'Supported Templates', return false.
	 *
	 * @param string $template The template to check.
	 * @return bool Whether the template is supported.
	 */
	public static function is_template_supported( $template ) {
		// If the include argument is present in the WP-CLI command, this template conditional must be present in it.
		if ( isset( self::$include_conditionals ) && ! in_array( $template, self::$include_conditionals, true ) ) {
			return false;
		}
		if ( self::$force_crawl_urls ) {
			return true;
		}

		/**
		 * Check whether this taxonomy's template is supported, including in the 'AMP Settings' > 'Supported Templates' UI.
		 * This first conditional is for default taxonomies like categories.
		 */
		return isset( self::$supportable_templates[ $template ]['supported'] ) && self::$supportable_templates[ $template ]['supported'];
	}

	/**
	 * Gets the permalinks of public, published posts.
	 *
	 * @param string $post_type The post type.
	 * @param int    $offset The offset of the query (optional).
	 * @return int[] $post_ids The post IDs in an array.
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
	 * Gets the author page URLs, like https://example.com/author/admin/.
	 *
	 * Accepts an $offset parameter, for the query of authors.
	 * 0 is the first author in the query, and 1 is the second.
	 *
	 * @param int $offset The offset for the URL to query for.
	 * @param int $number The total number to query for.
	 * @return array The author page URLs, or an empty array.
	 */
	public static function get_author_page_urls( $offset = 0, $number = null ) {
		$author_page_urls = array();
		if ( ! self::is_template_supported( 'is_author' ) ) {
			return $author_page_urls;
		}

		$get_users_args = compact( 'offset' );
		if ( isset( $number ) ) {
			$get_users_args['number'] = $number;
		}
		$users = get_users( $get_users_args );

		foreach ( $users as $author ) {
			$author_page_urls[] = get_author_posts_url( $author->ID, $author->user_nicename );
		}

		return $author_page_urls;
	}

	/**
	 * Gets a search page URLs, like https://example.com/?s=example.
	 *
	 * @return string|null An example search page, or null.
	 */
	public static function get_search_page() {
		if ( ! self::is_template_supported( 'is_search' ) ) {
			return null;
		}

		return add_query_arg( 's', 'example', home_url( '/' ) );
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
			$post_ids   = self::get_posts_that_support_amp( self::get_posts_by_type( $post_type ) );
			$permalinks = array_map( 'get_permalink', $post_ids );
			$offset     = 0;

			while ( ! empty( $permalinks ) ) {
				self::validate_urls( $permalinks );
				$offset    += self::BATCH_SIZE;
				$permalinks = array_map( 'get_permalink', self::get_posts_by_type( $post_type, $offset ) );
			}
		}

		// Validate all public taxonomies that don't have AMP disabled.
		$amp_enabled_taxonomies = array_filter(
			get_taxonomies( array( 'public' => true ) ),
			array( 'AMP_Site_Validation', 'does_taxonomy_support_amp' )
		);

		foreach ( $amp_enabled_taxonomies as $taxonomy ) {
			$taxonomy_links = self::get_taxonomy_links( $taxonomy, self::BATCH_SIZE );
			$offset         = 0;

			while ( ! empty( $taxonomy_links ) ) {
				self::validate_urls( $taxonomy_links );
				$offset        += self::BATCH_SIZE;
				$taxonomy_links = self::get_taxonomy_links( $taxonomy, self::BATCH_SIZE, $offset );
			}
		}

		self::validate_urls( self::get_author_page_urls( 0, 1 ) );
		self::validate_urls( array( self::get_search_page() ) );
	}

	/**
	 * Validates the URLs, and increments the counts.
	 *
	 * @param array $urls The URLs to validate.
	 */
	public static function validate_urls( $urls ) {
		foreach ( $urls as $url ) {
			if ( self::$force_crawl_urls ) {
				$url = add_query_arg( self::FORCE_VALIDATION_QUERY_VAR, '', $url );
			}
			$validity = AMP_Validation_Manager::validate_url( $url );

			if ( is_wp_error( $validity ) ) {
				continue;
			}
			if ( self::$wp_cli_progress ) {
				self::$wp_cli_progress->tick();
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
