<?php
/**
 * Class AMP_CLI
 *
 * @package AMP
 */

/**
 * Class AMP_CLI
 *
 * Registers and implements a WP-CLI command to crawl the entire site for AMP validity.
 * To use this, run: wp amp validate-site.
 *
 * @since 1.0
 */
class AMP_CLI {

	/**
	 * The WP-CLI flag to force validation.
	 *
	 * By default, the WP-CLI command does not validate templates that the user has opted-out of.
	 * For example, by unchecking 'Categories' in 'AMP Settings' > 'Supported Templates'.
	 * But with this flag, validation will ignore these options.
	 *
	 * @var string
	 */
	const FLAG_NAME_FORCE_VALIDATION = 'force';

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
	 * it overrides the value of self::$maximum_urls_to_validate_for_each_type.
	 *
	 * @var string
	 */
	const LIMIT_URLS_ARGUMENT = 'limit';

	/**
	 * The WP CLI progress bar.
	 *
	 * @see https://make.wordpress.org/cli/handbook/internal-api/wp-cli-utils-make-progress-bar/
	 * @var \cli\progress\Bar|\WP_CLI\NoOp
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
	 * where the user has not opted-out of AMP for the URL.
	 * For example, by un-checking 'Posts' in 'AMP Settings' > 'Supported Templates'.
	 * Or un-checking 'Enable AMP' in the post's editor.
	 *
	 * @var int
	 */
	public static $force_crawl_urls = false;

	/**
	 * A whitelist of conditionals to use for validation.
	 *
	 * Usually, this script will validate all of the templates that don't have AMP disabled.
	 * But this allows validating based on only these conditionals.
	 * This is set if the WP-CLI command has an --include argument.
	 *
	 * @var array
	 */
	public static $include_conditionals = array();

	/**
	 * The maximum number of URLs to validate for each type.
	 *
	 * Templates are each a separate type, like those for is_category() and is_tag().
	 * Also, each post type is a separate type.
	 * This value is overridden if the WP-CLI command has an --limit argument, like --limit=10.
	 *
	 * @var int
	 */
	public static $limit_type_validate_count;

	/**
	 * The validation counts by type, like template or post type.
	 *
	 * @var array[][] {
	 *     Validity by type.
	 *
	 *     @type int $valid The number of valid URLs for this type.
	 *     @type int $total The total number of URLs for this type, valid or invalid.
	 * }
	 */
	public static $validity_by_type = array();

	/**
	 * Crawl the entire site to get AMP validation results.
	 *
	 * ## OPTIONS
	 *
	 * [--limit=<count>]
	 * : The maximum number of URLs to validate for each template and content type.
	 * ---
	 * default: 100
	 * ---
	 *
	 * [--include=<templates>]
	 * : Only validates a URL if one of the conditionals is true.
	 *
	 * [--force]
	 * : Force validation of URLs even if their associated templates or object types do not have AMP enabled.
	 *
	 * ## EXAMPLES
	 *
	 *     wp amp validate-site --include=is_author,is_tag
	 *
	 * @subcommand validate-site
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative args.
	 * @throws Exception If an error happens.
	 */
	public function validate_site( $args, $assoc_args ) {
		unset( $args );
		self::$include_conditionals      = array();
		self::$force_crawl_urls          = false;
		self::$limit_type_validate_count = (int) $assoc_args[ self::LIMIT_URLS_ARGUMENT ];

		/*
		 * Handle the argument and flag passed to the command: --include and --force.
		 * If the self::INCLUDE_ARGUMENT is present, force crawling or URLs.
		 * The WP-CLI command should indicate which templates are crawled, not the /wp-admin options.
		 */
		if ( ! empty( $assoc_args[ self::INCLUDE_ARGUMENT ] ) ) {
			self::$include_conditionals = explode( ',', $assoc_args[ self::INCLUDE_ARGUMENT ] );
			self::$force_crawl_urls     = true;
		} elseif ( isset( $assoc_args[ self::FLAG_NAME_FORCE_VALIDATION ] ) ) {
			self::$force_crawl_urls = true;
		}

		if ( ! current_theme_supports( AMP_Theme_Support::SLUG ) ) {
			if ( self::$force_crawl_urls ) {
				/*
				 * There is no theme support added programmatically or via options.
				 * So make sure that theme support is present so that AMP_Validation_Manager::validate_url()
				 * will use a canonical URL as the basis for obtaining validation results.
				 */
				add_theme_support( AMP_Theme_Support::SLUG );
			} else {
				WP_CLI::error(
					sprintf(
						'Your templates are currently in Reader mode, which does not allow crawling the site. Please pass the --%s flag in order to force crawling for validation.',
						self::FLAG_NAME_FORCE_VALIDATION
					)
				);
			}
		}

		$number_urls_to_crawl = self::count_urls_to_validate();
		if ( ! $number_urls_to_crawl ) {
			if ( ! empty( self::$include_conditionals ) ) {
				WP_CLI::error(
					sprintf(
						'The templates passed via the --%s argument did not match any URLs. You might try passing different templates to it.',
						self::INCLUDE_ARGUMENT
					)
				);
			} else {
				WP_CLI::error(
					sprintf(
						'All of your templates might be unchecked in AMP Settings > Supported Templates. You might pass --%s to this command.',
						self::FLAG_NAME_FORCE_VALIDATION
					)
				);
			}
		}

		WP_CLI::log( 'Crawling the site for AMP validity.' );

		self::$wp_cli_progress = WP_CLI\Utils\make_progress_bar(
			sprintf( 'Validating %d URLs...', $number_urls_to_crawl ),
			$number_urls_to_crawl
		);
		self::crawl_site();
		self::$wp_cli_progress->finish();

		$key_template_type = 'Template or content type';
		$key_url_count     = 'URL Count';
		$key_validity_rate = 'Validity Rate';

		$table_validation_by_type = array();
		foreach ( self::$validity_by_type as $type_name => $validity ) {
			$table_validation_by_type[] = array(
				$key_template_type => $type_name,
				$key_url_count     => $validity['total'],
				$key_validity_rate => sprintf( '%d%%', 100.0 * ( $validity['valid'] / $validity['total'] ) ),
			);
		}

		if ( empty( $table_validation_by_type ) ) {
			WP_CLI::error( 'No validation results were obtained from the URLs.' );
			return;
		}

		WP_CLI::success(
			sprintf(
				'%3$d crawled URLs have unaccepted issue(s) out of %2$d total with AMP validation issue(s); %1$d URLs were crawled.',
				self::$number_crawled,
				self::$total_errors,
				self::$unaccepted_errors
			)
		);

		// Output a table of validity by template/content type.
		WP_CLI\Utils\format_items(
			'table',
			$table_validation_by_type,
			array( $key_template_type, $key_url_count, $key_validity_rate )
		);

		$url_more_details = add_query_arg(
			'post_type',
			AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
			admin_url( 'edit.php' )
		);
		WP_CLI::line( sprintf( 'For more details, please see: %s', $url_more_details ) );
	}

	/**
	 * Reset all validation data on a site.
	 *
	 * This deletes all amp_validated_url posts and all amp_validation_error terms.
	 *
	 * ## OPTIONS
	 *
	 * [--yes]
	 * : Proceed to empty the site validation data without a confirmation prompt.
	 *
	 * ## EXAMPLES
	 *
	 *     wp amp reset-site-validation --yes
	 *
	 * @subcommand reset-site-validation
	 * @param array $args       Positional args. Unused.
	 * @param array $assoc_args Associative args.
	 * @throws Exception If an error happens.
	 */
	public function reset_site_validation( $args, $assoc_args ) {
		unset( $args );
		global $wpdb;
		WP_CLI::confirm( 'Are you sure you want to empty all amp_validated_url posts and amp_validation_error taxonomy terms?', $assoc_args );

		// Delete all posts.
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s", AMP_Validated_URL_Post_Type::POST_TYPE_SLUG ) );
		$query = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", AMP_Validated_URL_Post_Type::POST_TYPE_SLUG );
		$posts = new WP_CLI\Iterators\Query( $query, 10000 );

		$progress = WP_CLI\Utils\make_progress_bar(
			sprintf( 'Deleting %d amp_validated_url posts...', $count ),
			$count
		);
		while ( $posts->valid() ) {
			$post_id = $posts->current()->ID;
			$posts->next();
			wp_delete_post( $post_id, true );
			$progress->tick();
		}
		$progress->finish();

		// Delete all terms. Note that many terms should get deleted when their post counts go to zero above.
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( * ) FROM $wpdb->term_taxonomy WHERE taxonomy = %s", AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ) );
		$query = $wpdb->prepare( "SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = %s", AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
		$terms = new WP_CLI\Iterators\Query( $query, 10000 );

		$progress = WP_CLI\Utils\make_progress_bar(
			sprintf( 'Deleting %d amp_taxonomy_error terms...', $count ),
			$count
		);
		while ( $terms->valid() ) {
			$term_id = $terms->current()->term_id;
			$terms->next();
			wp_delete_term( $term_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
			$progress->tick();
		}
		$progress->finish();

		WP_CLI::success( 'All AMP validation data has been removed.' );
	}

	/**
	 * Gets the total number of URLs to validate.
	 *
	 * By default, this only counts AMP-enabled posts and terms.
	 * But if $force_crawl_urls is true, it counts all of them, regardless of their AMP status.
	 * It also uses self::$maximum_urls_to_validate_for_each_type,
	 * which can be overridden with a command line argument.
	 *
	 * @return int The number of URLs to validate.
	 */
	public static function count_urls_to_validate() {
		/*
		 * If the homepage is set to 'Your latest posts,' start the $total_count at 1.
		 * Otherwise, it will probably be counted in the query for pages below.
		 */
		$total_count = 'posts' === get_option( 'show_on_front' ) && self::is_template_supported( 'is_home' ) ? 1 : 0;

		$amp_enabled_taxonomies = array_filter(
			get_taxonomies( array( 'public' => true ) ),
			array( 'AMP_CLI', 'does_taxonomy_support_amp' )
		);

		// Count all public taxonomy terms.
		foreach ( $amp_enabled_taxonomies as $taxonomy ) {
			$term_query = new WP_Term_Query(
				array(
					'taxonomy' => $taxonomy,
					'fields'   => 'ids',
					'number'   => self::$limit_type_validate_count,
				)
			);

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

		// Count a single example date page, like https://example.com/?year=2019.
		if ( self::get_date_page() ) {
			$total_count++;
		}

		// Count a single example search page, like https://example.com/?s=example.
		if ( self::get_search_page() ) {
			$total_count++;
		}

		return $total_count;
	}

	/**
	 * Gets the posts IDs that support AMP.
	 *
	 * By default, this only gets the post IDs if they support AMP.
	 * This means that 'Posts' isn't deselected in 'AMP Settings' > 'Supported Templates'.
	 * And 'Enable AMP' isn't unchecked in the post's editor.
	 * But if $force_crawl_urls is true, this simply returns all of the IDs.
	 *
	 * @param array $ids THe post IDs to check for AMP support.
	 * @return array The post IDs that support AMP, or an empty array.
	 */
	public static function get_posts_that_support_amp( $ids ) {
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
	 * This only gets the term IDs if they support AMP.
	 * If their taxonomy is unchecked in 'AMP Settings' > 'Supported Templates,' this does not return them.
	 * For example, if 'Categories' is unchecked.
	 * This can be overridden by passing the self::FLAG_NAME_FORCE_VALIDATION argument to the WP-CLI command.
	 *
	 * @param string $taxonomy The taxonomy.
	 * @return boolean Whether the taxonomy supports AMP.
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
	 * But passing the self::FLAG_NAME_FORCE_VALIDATION argument to the WP-CLI command overrides this.
	 *
	 * @param string $template The template to check.
	 * @return bool Whether the template is supported.
	 */
	public static function is_template_supported( $template ) {
		// If the --include argument is present in the WP-CLI command, this template conditional must be present in it.
		if ( ! empty( self::$include_conditionals ) ) {
			return in_array( $template, self::$include_conditionals, true );
		}
		if ( self::$force_crawl_urls ) {
			return true;
		}

		$supportable_templates = AMP_Theme_Support::get_supportable_templates();

		// Check whether this taxonomy's template is supported, including in the 'AMP Settings' > 'Supported Templates' UI.
		return ! empty( $supportable_templates[ $template ]['supported'] );
	}

	/**
	 * Gets the IDs of public, published posts.
	 *
	 * @param string   $post_type The post type.
	 * @param int|null $offset The offset of the query (optional).
	 * @param int|null $number The number of posts to query for (optional).
	 * @return int[]   $post_ids The post IDs in an array.
	 */
	public static function get_posts_by_type( $post_type, $offset = null, $number = null ) {
		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => is_int( $number ) ? $number : self::$limit_type_validate_count,
			'post_status'    => 'publish',
			'orderby'        => 'ID',
			'order'          => 'DESC',
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
	 * Gets the front-end links for taxonomy terms.
	 * For example, https://example.org/?cat=2
	 *
	 * @param string     $taxonomy The name of the taxonomy, like 'category' or 'post_tag'.
	 * @param int|string $offset The number at which to offset the query (optional).
	 * @param int        $number The maximum amount of links to get (optional).
	 * @return string[]  The term links, as an array of strings.
	 */
	public static function get_taxonomy_links( $taxonomy, $offset = '', $number = 1 ) {
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

		$number = ! empty( $number ) ? $number : self::$limit_type_validate_count;
		foreach ( get_users( compact( 'offset', 'number' ) ) as $author ) {
			$author_page_urls[] = get_author_posts_url( $author->ID, $author->user_nicename );
		}

		return $author_page_urls;
	}

	/**
	 * Gets a single search page URL, like https://example.com/?s=example.
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
	 * Gets a single date page URL, like https://example.com/?year=2018.
	 *
	 * @return string|null An example search page, or null.
	 */
	public static function get_date_page() {
		if ( ! self::is_template_supported( 'is_date' ) ) {
			return null;
		}

		return add_query_arg( 'year', date( 'Y' ), home_url( '/' ) );
	}

	/**
	 * Validates the URLs of the entire site.
	 *
	 * Includes the URLs of public, published posts, public taxonomies, and other templates.
	 * This validates one of each type at a time,
	 * and iterates until it reaches the maximum number of URLs for each type.
	 */
	public static function crawl_site() {
		/*
		 * If 'Your homepage displays' is set to 'Your latest posts', validate the homepage.
		 * It will not be part of the page validation below.
		 */
		if ( 'posts' === get_option( 'show_on_front' ) && self::is_template_supported( 'is_home' ) ) {
			self::validate_and_store_url( home_url( '/' ), 'home' );
		}

		$amp_enabled_taxonomies = array_filter(
			get_taxonomies( array( 'public' => true ) ),
			array( 'AMP_CLI', 'does_taxonomy_support_amp' )
		);
		$public_post_types      = get_post_types( array( 'public' => true ), 'names' );

		// Validate one URL of each template/content type, then another URL of each type on the next iteration.
		for ( $i = 0; $i < self::$limit_type_validate_count; $i++ ) {
			// Validate all public, published posts.
			foreach ( $public_post_types as $post_type ) {
				$post_ids = self::get_posts_that_support_amp( self::get_posts_by_type( $post_type, $i, 1 ) );
				if ( ! empty( $post_ids[0] ) ) {
					self::validate_and_store_url( get_permalink( $post_ids[0] ), $post_type );
				}
			}

			foreach ( $amp_enabled_taxonomies as $taxonomy ) {
				$taxonomy_links = self::get_taxonomy_links( $taxonomy, $i, 1 );
				$link           = reset( $taxonomy_links );
				if ( ! empty( $link ) ) {
					self::validate_and_store_url( $link, $taxonomy );
				}
			}

			$author_page_urls = self::get_author_page_urls( $i, 1 );
			if ( ! empty( $author_page_urls[0] ) ) {
				self::validate_and_store_url( $author_page_urls[0], 'author' );
			}
		}

		// Only validate 1 date and 1 search page.
		$url = self::get_date_page();
		if ( $url ) {
			self::validate_and_store_url( $url, 'date' );
		}
		$url = self::get_search_page();
		if ( $url ) {
			self::validate_and_store_url( $url, 'search' );
		}
	}

	/**
	 * Validates the URL, stores the results, and increments the counts.
	 *
	 * @param string $url  The URL to validate.
	 * @param string $type The type of template, post, or taxonomy.
	 */
	public static function validate_and_store_url( $url, $type ) {
		$validity = AMP_Validation_Manager::validate_url( $url );

		/*
		 * If the request to validate this returns a WP_Error, return.
		 * One cause of an error is if the validation request results in a 404 response code.
		 */
		if ( is_wp_error( $validity ) ) {
			WP_CLI::warning( sprintf( 'Validate URL error (%1$s): %2$s URL: %3$s', $validity->get_error_code(), $validity->get_error_message(), $url ) );
			return;
		}
		if ( self::$wp_cli_progress ) {
			self::$wp_cli_progress->tick();
		}

		$validation_errors = wp_list_pluck( $validity['results'], 'error' );
		AMP_Validated_URL_Post_Type::store_validation_errors(
			$validation_errors,
			$validity['url'],
			wp_array_slice_assoc( $validity, array( 'queried_object' ) )
		);
		$unaccepted_error_count = count(
			array_filter(
				$validation_errors,
				function( $error ) {
					$validation_status = AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $error );
					return (
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS !== $validation_status['term_status']
					&&
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS !== $validation_status['term_status']
					);
				}
			)
		);

		if ( count( $validation_errors ) > 0 ) {
			self::$total_errors++;
		}
		if ( $unaccepted_error_count > 0 ) {
			self::$unaccepted_errors++;
		}

		self::$number_crawled++;

		if ( ! isset( self::$validity_by_type[ $type ] ) ) {
			self::$validity_by_type[ $type ] = array(
				'valid' => 0,
				'total' => 0,
			);
		}
		self::$validity_by_type[ $type ]['total']++;
		if ( 0 === $unaccepted_error_count ) {
			self::$validity_by_type[ $type ]['valid']++;
		}
	}
}
