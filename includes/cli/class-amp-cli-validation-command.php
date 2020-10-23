<?php
/**
 * Class AMP_CLI_Validation_Command.
 *
 * Commands that deal with validation of AMP markup.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Option;

/**
 * Crawls the site for validation errors or resets the stored validation errors.
 *
 * @since 1.0
 * @since 1.3.0 Renamed subcommands.
 * @internal
 */
final class AMP_CLI_Validation_Command {

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
	 * it overrides the value of $this->maximum_urls_to_validate_for_each_type.
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
	public $wp_cli_progress;

	/**
	 * The total number of validation errors, regardless of whether they were accepted.
	 *
	 * @var int
	 */
	public $total_errors = 0;

	/**
	 * The total number of unaccepted validation errors.
	 *
	 * If an error has been accepted in the /wp-admin validation UI,
	 * it won't count toward this.
	 *
	 * @var int
	 */
	public $unaccepted_errors = 0;

	/**
	 * The number of URLs crawled, regardless of whether they have validation errors.
	 *
	 * @var int
	 */
	public $number_crawled = 0;

	/**
	 * Whether to force crawling of URLs.
	 *
	 * By default, this script only crawls URLs that support AMP,
	 * where the user has not opted-out of AMP for the URL.
	 * For example, by un-checking 'Posts' in 'AMP Settings' > 'Supported Templates'.
	 * Or un-checking 'Enable AMP' in the post's editor.
	 *
	 * @var bool
	 */
	public $force_crawl_urls = false;

	/**
	 * An allowlist of conditionals to use for validation.
	 *
	 * Usually, this script will validate all of the templates that don't have AMP disabled.
	 * But this allows validating based on only these conditionals.
	 * This is set if the WP-CLI command has an --include argument.
	 *
	 * @var array
	 */
	public $include_conditionals = [];

	/**
	 * The maximum number of URLs to validate for each type.
	 *
	 * Templates are each a separate type, like those for is_category() and is_tag().
	 * Also, each post type is a separate type.
	 * This value is overridden if the WP-CLI command has an --limit argument, like --limit=10.
	 *
	 * @var int
	 */
	public $limit_type_validate_count;

	/**
	 * The validation counts by type, like template or post type.
	 *
	 * @var array[] {
	 *     Validity by type.
	 *
	 *     @type array $type {
	 *         @type int $valid The number of valid URLs for this type.
	 *         @type int $total The total number of URLs for this type, valid or invalid.
	 *     }
	 * }
	 */
	public $validity_by_type = [];

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
	 *     wp amp validation run --include=is_author,is_tag
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative args.
	 * @throws Exception If an error happens.
	 */
	public function run( $args, $assoc_args ) {
		$this->include_conditionals      = [];
		$this->force_crawl_urls          = false;
		$this->limit_type_validate_count = (int) $assoc_args[ self::LIMIT_URLS_ARGUMENT ];

		/*
		 * Handle the argument and flag passed to the command: --include and --force.
		 * If the self::INCLUDE_ARGUMENT is present, force crawling or URLs.
		 * The WP-CLI command should indicate which templates are crawled, not the /wp-admin options.
		 */
		if ( ! empty( $assoc_args[ self::INCLUDE_ARGUMENT ] ) ) {
			$this->include_conditionals = explode( ',', $assoc_args[ self::INCLUDE_ARGUMENT ] );
			$this->force_crawl_urls     = true;
		} elseif ( isset( $assoc_args[ self::FLAG_NAME_FORCE_VALIDATION ] ) ) {
			$this->force_crawl_urls = true;
		}

		// Handle special case for Legacy Reader mode.
		if (
			AMP_Theme_Support::READER_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
			&&
			ReaderThemes::DEFAULT_READER_THEME === AMP_Options_Manager::get_option( Option::READER_THEME )
		) {
			$allowed_templates = [
				'is_singular',
			];
			if ( 'page' === get_option( 'show_on_front' ) ) {
				$allowed_templates[] = 'is_home';
				$allowed_templates[] = 'is_front_page';
			}

			$disallowed_templates = array_diff( $this->include_conditionals, $allowed_templates );
			if ( ! empty( $disallowed_templates ) ) {
				WP_CLI::error( sprintf( 'Templates not supported in legacy Reader mode with current configuration: %s', implode( ',', $disallowed_templates ) ) );
			}

			if ( empty( $this->include_conditionals ) ) {
				$this->include_conditionals = $allowed_templates;
			}
		}

		$number_urls_to_crawl = $this->count_urls_to_validate();
		if ( ! $number_urls_to_crawl ) {
			if ( ! empty( $this->include_conditionals ) ) {
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

		$this->wp_cli_progress = WP_CLI\Utils\make_progress_bar(
			sprintf( 'Validating %d URLs...', $number_urls_to_crawl ),
			$number_urls_to_crawl
		);
		$this->crawl_site();
		$this->wp_cli_progress->finish();

		$key_template_type = 'Template or content type';
		$key_url_count     = 'URL Count';
		$key_validity_rate = 'Validity Rate';

		$table_validation_by_type = [];
		foreach ( $this->validity_by_type as $type_name => $validity ) {
			$table_validation_by_type[] = [
				$key_template_type => $type_name,
				$key_url_count     => $validity['total'],
				$key_validity_rate => sprintf( '%d%%', 100.0 * ( $validity['valid'] / $validity['total'] ) ),
			];
		}

		if ( empty( $table_validation_by_type ) ) {
			WP_CLI::error( 'No validation results were obtained from the URLs.' );
			return;
		}

		WP_CLI::success(
			sprintf(
				'%3$d crawled URLs have invalid markup kept out of %2$d total with AMP validation issue(s); %1$d URLs were crawled.',
				$this->number_crawled,
				$this->total_errors,
				$this->unaccepted_errors
			)
		);

		// Output a table of validity by template/content type.
		WP_CLI\Utils\format_items(
			'table',
			$table_validation_by_type,
			[ $key_template_type, $key_url_count, $key_validity_rate ]
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
	 *     wp amp validation reset --yes
	 *
	 * @param array $args       Positional args. Unused.
	 * @param array $assoc_args Associative args.
	 * @throws Exception If an error happens.
	 */
	public function reset( $args, $assoc_args ) {
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

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
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

		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
			$progress->tick();
		}

		$progress->finish();

		WP_CLI::success( 'All AMP validation data has been removed.' );
	}

	/**
	 * Generate the authorization nonce needed for a validate request.
	 *
	 * @subcommand generate-nonce
	 * @alias nonce
	 */
	public function generate_nonce() {
		WP_CLI::line( AMP_Validation_Manager::get_amp_validate_nonce() );
	}

	/**
	 * Get the validation results for a given URL.
	 *
	 * The results are returned in JSON format.
	 *
	 * ## OPTIONS
	 *
	 * <url>
	 * : The URL to check. The host name need not be included. The URL must be local to this WordPress install.
	 *
	 * ## EXAMPLES
	 *
	 *     wp amp validation check-url /about/
	 *     wp amp validation check-url $( wp option get home )/?p=1
	 *
	 * @subcommand check-url
	 * @alias check
	 *
	 * @param array $args Args.
	 */
	public function check_url( $args ) {
		list( $url ) = $args;

		$host            = wp_parse_url( $url, PHP_URL_HOST );
		$parsed_home_url = wp_parse_url( home_url( '/' ) );

		if ( ! isset( $parsed_home_url['host'], $parsed_home_url['scheme'] ) ) {
			WP_CLI::error(
				sprintf(
					'The home URL (%s) is missing a scheme and host.',
					home_url( '/' )
				)
			);
		}

		if ( $host && $host !== $parsed_home_url['host'] ) {
			WP_CLI::error(
				sprintf(
					'Supplied URL must be for this WordPress install. Expected host "%1$s" but provided is "%2$s".',
					$parsed_home_url['host'],
					$host
				)
			);
		}

		if ( ! $host ) {
			$origin = $parsed_home_url['scheme'] . '://' . $parsed_home_url['host'];
			if ( ! empty( $parsed_home_url['port'] ) ) {
				$origin .= ':' . $parsed_home_url['port'];
			}
			$url = $origin . '/' . ltrim( $url, '/' );
		}

		$result = AMP_Validation_Manager::validate_url( $url );
		if ( $result instanceof WP_Error ) {
			WP_CLI::error( $result );
		}

		WP_CLI::line( wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	/**
	 * Gets the total number of URLs to validate.
	 *
	 * By default, this only counts AMP-enabled posts and terms.
	 * But if $force_crawl_urls is true, it counts all of them, regardless of their AMP status.
	 * It also uses $this->maximum_urls_to_validate_for_each_type,
	 * which can be overridden with a command line argument.
	 *
	 * @return int The number of URLs to validate.
	 */
	private function count_urls_to_validate() {
		/*
		 * If the homepage is set to 'Your latest posts,' start the $total_count at 1.
		 * Otherwise, it will probably be counted in the query for pages below.
		 */
		$total_count = 'posts' === get_option( 'show_on_front' ) && $this->is_template_supported( 'is_home' ) ? 1 : 0;

		$amp_enabled_taxonomies = array_filter(
			get_taxonomies( [ 'public' => true ] ),
			[ $this, 'does_taxonomy_support_amp' ]
		);

		// Count all public taxonomy terms.
		foreach ( $amp_enabled_taxonomies as $taxonomy ) {
			$term_query = new WP_Term_Query(
				[
					'taxonomy' => $taxonomy,
					'fields'   => 'ids',
					'number'   => $this->limit_type_validate_count,
				]
			);

			// If $term_query->terms is an empty array, passing it to count() will throw an error.
			$total_count += ! empty( $term_query->terms ) ? count( $term_query->terms ) : 0;
		}

		// Count posts by type, like post, page, attachment, etc.
		$public_post_types = get_post_types( [ 'public' => true ], 'names' );
		foreach ( $public_post_types as $post_type ) {
			$posts        = $this->get_posts_that_support_amp( $this->get_posts_by_type( $post_type ) );
			$total_count += ! empty( $posts ) ? count( $posts ) : 0;
		}

		// Count author pages, like https://example.com/author/admin/.
		$total_count += count( $this->get_author_page_urls() );

		// Count a single example date page, like https://example.com/?year=2019.
		if ( $this->get_date_page() ) {
			$total_count++;
		}

		// Count a single example search page, like https://example.com/?s=example.
		if ( $this->get_search_page() ) {
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
	private function get_posts_that_support_amp( $ids ) {
		if ( ! $this->is_template_supported( 'is_singular' ) ) {
			return [];
		}

		if ( $this->force_crawl_urls ) {
			return $ids;
		}

		return array_filter(
			$ids,
			'amp_is_post_supported'
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
	private function does_taxonomy_support_amp( $taxonomy ) {
		if ( 'post_tag' === $taxonomy ) {
			$taxonomy = 'tag';
		}
		$taxonomy_key        = 'is_' . $taxonomy;
		$custom_taxonomy_key = sprintf( 'is_tax[%s]', $taxonomy );
		return $this->is_template_supported( $taxonomy_key ) || $this->is_template_supported( $custom_taxonomy_key );
	}

	/**
	 * Gets whether the template is supported.
	 *
	 * If the user has passed an include argument to the WP-CLI command, use that to find if this template supports AMP.
	 * For example, wp amp validation run --include=is_tag,is_category
	 * would return true only if is_tag() or is_category().
	 * But passing the self::FLAG_NAME_FORCE_VALIDATION argument to the WP-CLI command overrides this.
	 *
	 * @param string $template The template to check.
	 * @return bool Whether the template is supported.
	 */
	private function is_template_supported( $template ) {
		// If the --include argument is present in the WP-CLI command, this template conditional must be present in it.
		if ( ! empty( $this->include_conditionals ) ) {
			return in_array( $template, $this->include_conditionals, true );
		}
		if ( $this->force_crawl_urls ) {
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
	private function get_posts_by_type( $post_type, $offset = null, $number = null ) {
		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => is_int( $number ) ? $number : $this->limit_type_validate_count,
			'post_status'    => 'publish',
			'orderby'        => 'ID',
			'order'          => 'DESC',
			'fields'         => 'ids',
		];
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
	private function get_taxonomy_links( $taxonomy, $offset = '', $number = 1 ) {
		return array_map(
			'get_term_link',
			get_terms(
				array_merge(
					compact( 'taxonomy', 'offset', 'number' ),
					[
						'orderby' => 'id',
					]
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
	private function get_author_page_urls( $offset = '', $number = '' ) {
		$author_page_urls = [];
		if ( ! $this->is_template_supported( 'is_author' ) ) {
			return $author_page_urls;
		}

		$number = ! empty( $number ) ? $number : $this->limit_type_validate_count;
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
	private function get_search_page() {
		if ( ! $this->is_template_supported( 'is_search' ) ) {
			return null;
		}

		return add_query_arg( 's', 'example', home_url( '/' ) );
	}

	/**
	 * Gets a single date page URL, like https://example.com/?year=2018.
	 *
	 * @return string|null An example search page, or null.
	 */
	private function get_date_page() {
		if ( ! $this->is_template_supported( 'is_date' ) ) {
			return null;
		}

		return add_query_arg( 'year', gmdate( 'Y' ), home_url( '/' ) );
	}

	/**
	 * Validates the URLs of the entire site.
	 *
	 * Includes the URLs of public, published posts, public taxonomies, and other templates.
	 * This validates one of each type at a time,
	 * and iterates until it reaches the maximum number of URLs for each type.
	 */
	private function crawl_site() {
		/*
		 * If 'Your homepage displays' is set to 'Your latest posts', validate the homepage.
		 * It will not be part of the page validation below.
		 */
		if ( 'posts' === get_option( 'show_on_front' ) && $this->is_template_supported( 'is_home' ) ) {
			$this->validate_and_store_url( home_url( '/' ), 'home' );
		}

		$amp_enabled_taxonomies = array_filter(
			get_taxonomies( [ 'public' => true ] ),
			[ $this, 'does_taxonomy_support_amp' ]
		);
		$public_post_types      = get_post_types( [ 'public' => true ], 'names' );

		// Validate one URL of each template/content type, then another URL of each type on the next iteration.
		for ( $i = 0; $i < $this->limit_type_validate_count; $i++ ) {
			// Validate all public, published posts.
			foreach ( $public_post_types as $post_type ) {
				$post_ids = $this->get_posts_that_support_amp( $this->get_posts_by_type( $post_type, $i, 1 ) );
				if ( ! empty( $post_ids[0] ) ) {
					$this->validate_and_store_url( get_permalink( $post_ids[0] ), $post_type );
				}
			}

			foreach ( $amp_enabled_taxonomies as $taxonomy ) {
				$taxonomy_links = $this->get_taxonomy_links( $taxonomy, $i, 1 );
				$link           = reset( $taxonomy_links );
				if ( ! empty( $link ) ) {
					$this->validate_and_store_url( $link, $taxonomy );
				}
			}

			$author_page_urls = $this->get_author_page_urls( $i, 1 );
			if ( ! empty( $author_page_urls[0] ) ) {
				$this->validate_and_store_url( $author_page_urls[0], 'author' );
			}
		}

		// Only validate 1 date and 1 search page.
		$url = $this->get_date_page();
		if ( $url ) {
			$this->validate_and_store_url( $url, 'date' );
		}
		$url = $this->get_search_page();
		if ( $url ) {
			$this->validate_and_store_url( $url, 'search' );
		}
	}

	/**
	 * Validates the URL, stores the results, and increments the counts.
	 *
	 * @param string $url  The URL to validate.
	 * @param string $type The type of template, post, or taxonomy.
	 */
	private function validate_and_store_url( $url, $type ) {
		$validity = AMP_Validation_Manager::validate_url_and_store( $url );

		/*
		 * If the request to validate this returns a WP_Error, return.
		 * One cause of an error is if the validation request results in a 404 response code.
		 */
		if ( is_wp_error( $validity ) ) {
			WP_CLI::warning( sprintf( 'Validate URL error (%1$s): %2$s URL: %3$s', $validity->get_error_code(), $validity->get_error_message(), $url ) );
			return;
		}
		if ( $this->wp_cli_progress ) {
			$this->wp_cli_progress->tick();
		}

		$validation_errors      = wp_list_pluck( $validity['results'], 'error' );
		$unaccepted_error_count = count(
			array_filter(
				$validation_errors,
				static function( $error ) {
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
			$this->total_errors++;
		}
		if ( $unaccepted_error_count > 0 ) {
			$this->unaccepted_errors++;
		}

		$this->number_crawled++;

		if ( ! isset( $this->validity_by_type[ $type ] ) ) {
			$this->validity_by_type[ $type ] = [
				'valid' => 0,
				'total' => 0,
			];
		}
		$this->validity_by_type[ $type ]['total']++;
		if ( 0 === $unaccepted_error_count ) {
			$this->validity_by_type[ $type ]['valid']++;
		}
	}
}
