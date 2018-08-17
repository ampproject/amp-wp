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
	 * The WP-CLI argument for the maximum URLs to validate for each type.
	 *
	 * If this is passed in the command,
	 * it's applied to self::$maximum_urls_to_validate_for_each_type.
	 *
	 * @var string
	 */
	const MAXIMUM_URLS_ARGUMENT = 'max-url-count';

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
	 * The maximum number of URLs to validate for each type.
	 *
	 * Templates are each a separate type, like those for is_category() and is_tag().
	 * Also, post types are a separate type.
	 * So by default, this validates 100 posts of each post type.
	 *
	 * @var int
	 */
	public static $maximum_urls_to_validate_for_each_type = 100;

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
							'description' => __( 'Only validates a URL if one of the conditionals is true', 'amp' ),
							'optional'    => true,
						),
						array(
							'type'        => 'assoc',
							'name'        => self::MAXIMUM_URLS_ARGUMENT,
							'description' => __( 'The maximum number of URLs to validate for each template and content type', 'amp' ),
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

		// Handle the argument and flag passed to the command: --include and --force-validation.
		if ( ! empty( $assoc_args[ self::INCLUDE_ARGUMENT ] ) ) {
			self::$include_conditionals = explode( ',', $assoc_args[ self::INCLUDE_ARGUMENT ] );
			self::$force_crawl_urls     = true;
		} elseif ( isset( $assoc_args[ self::FLAG_NAME_FORCE_VALIDATE_ALL ] ) ) {
			self::$force_crawl_urls = true;
		}

		if ( ! empty( $assoc_args[ self::MAXIMUM_URLS_ARGUMENT ] ) ) {
			self::$maximum_urls_to_validate_for_each_type = intval( $assoc_args[ self::MAXIMUM_URLS_ARGUMENT ] );
		}

		$number_urls_to_crawl = self::count_urls_to_validate();
		if ( ! $number_urls_to_crawl ) {
			WP_CLI::error(
				sprintf(
					/* translators: %s is the command line argument to force validation */
					__( 'All of your templates might be unchecked in AMP Settings > Supported Templates. You might pass --%s to this command.', 'amp' ),
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
		self::validate_site();
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
	public static function count_urls_to_validate() {
		/*
		 * If the homepage is set to 'Your latest posts,' start the $total_count at 1.
		 * Otherwise, it will probably be counted in the query for pages below.
		 */
		$total_count = 'posts' === get_option( 'show_on_front' ) && self::is_template_supported( 'is_home' ) ? 1 : 0;

		$amp_enabled_taxonomies = array_filter(
			get_taxonomies( array( 'public' => true ) ),
			array( 'AMP_Site_Validation', 'does_taxonomy_support_amp' )
		);

		// Count all public taxonomy terms.
		foreach ( $amp_enabled_taxonomies as $taxonomy ) {
			$term_query = new WP_Term_Query( array(
				'taxonomy' => $taxonomy,
				'fields'   => 'ids',
				'number'   => self::$maximum_urls_to_validate_for_each_type,
			) );
			// If $term_query->terms is an empty array, passing it to count() will throw an error.
			$total_count += ! empty( $term_query->terms ) ? count( $term_query->terms ) : 0;
		}

		// Count posts by type, like post, page, attachment, etc.
		$public_post_types = get_post_types( array( 'public' => true ), 'names' );
		foreach ( $public_post_types as $post_type ) {
			$posts        = self::get_posts_that_support_amp( self::get_posts_by_type( $post_type ) );
			$total_count += ! empty( $posts ) ? count( $posts ) : 0;
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
	 * @param int    $number The number of posts to query for (optional).
	 * @return int[] $post_ids The post IDs in an array.
	 */
	public static function get_posts_by_type( $post_type, $offset = null, $number = null ) {
		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => isset( $number ) ? $number : self::$maximum_urls_to_validate_for_each_type,
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
	 * @param string     $taxonomy The name of the taxonomy, like 'category' or 'post_tag'.
	 * @param int|string $offset The number at which to offset the query (optional).
	 * @param int        $number The maximum amount of links to get (optional).
	 * @return string[]  The term links in an array.
	 */
	public static function get_taxonomy_links( $taxonomy, $offset = '', $number = 200 ) {
		return array_map(
			'get_term_link',
			get_terms(
				array_merge(
					compact( 'taxonomy', 'offset', 'number' ),
					array(
						'orderby' => 'id',
					)
				)
			)
		);
	}

	/**
	 * Gets the author page URLs, like https://example.com/author/admin/.
	 *
	 * Accepts an $offset parameter, for the query of authors.
	 * 0 is the first author in the query, and 1 is the second.
	 *
	 * @param int|string $offset The offset for the URL to query for, should be an int if passing an argument.
	 * @param int|string $number The total number to query for, should be an int if passing an argument.
	 * @return array The author page URLs, or an empty array.
	 */
	public static function get_author_page_urls( $offset = '', $number = '' ) {
		$author_page_urls = array();
		if ( ! self::is_template_supported( 'is_author' ) ) {
			return $author_page_urls;
		}

		$number = ! empty( $number ) ? $number : self::$maximum_urls_to_validate_for_each_type;
		foreach ( get_users( compact( 'offset', 'number' ) ) as $author ) {
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
	 *
	 * Includes the URLs of public, published posts, public taxonomies, and other templates.
	 * This validates one of each type at a time.
	 * And continues this until it reaches the maximum number of URLs for each type.
	 */
	public static function validate_site() {
		$amp_enabled_taxonomies = array_filter(
			get_taxonomies( array( 'public' => true ) ),
			array( 'AMP_Site_Validation', 'does_taxonomy_support_amp' )
		);
		$public_post_types      = get_post_types( array( 'public' => true ), 'names' );
		$i                      = 0;

		// Validate one URL of each type at a time, then another URL of each type on the next iteration.
		while ( $i < self::$maximum_urls_to_validate_for_each_type ) {
			// Validate all public, published posts.
			foreach ( $public_post_types as $post_type ) {
				$post_ids = self::get_posts_that_support_amp( self::get_posts_by_type( $post_type, $i, 1 ) );
				if ( ! empty( $post_ids ) ) {
					self::validate_urls( array_map( 'get_permalink', $post_ids ) );
				}
			}

			foreach ( $amp_enabled_taxonomies as $taxonomy ) {
				self::validate_urls( self::get_taxonomy_links( $taxonomy, $i, 1 ) );
			}

			self::validate_urls( self::get_author_page_urls( $i, 1 ) );

			$i++;
		}

		/**
		 * If 'Your homepage displays' is set to 'Your latest posts',
		 * validate the homepage.
		 * It would not have been validated above in the page validation.
		 */
		if ( 'posts' === get_option( 'show_on_front' ) && self::is_template_supported( 'is_home' ) ) {
			self::validate_urls( array( home_url( '/' ) ) );
		}

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
