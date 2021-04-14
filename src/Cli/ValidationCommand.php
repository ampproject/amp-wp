<?php
/**
 * Class ValidationCommand.
 *
 * Commands that deal with validation of AMP markup.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Cli;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AMP_Validated_URL_Post_Type;
use AMP_Validation_Error_Taxonomy;
use AMP_Validation_Manager;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Infrastructure\CliCommand;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Validation\URLValidationProvider;
use AmpProject\AmpWP\Validation\ScannableURLProvider;
use AmpProject\AmpWP\Validation\URLScanningContext;
use Exception;
use WP_CLI;
use WP_CLI\Utils;
use WP_Error;

/**
 * Crawls the site for validation errors or resets the stored validation errors.
 *
 * @since 1.0
 * @since 1.3.0 Renamed subcommands.
 * @since 2.1.0 Refactored into service-based architecture.
 * @internal
 */
final class ValidationCommand implements Service, CliCommand {

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
	 * URLValidationProvider instance.
	 *
	 * @var URLValidationProvider
	 */
	private $url_validation_provider;

	/**
	 * ScannableURLProvider instance.
	 *
	 * @var ScannableURLProvider
	 */
	private $scannable_url_provider;

	/**
	 * Associative args passed to the command.
	 *
	 * @var array
	 */
	private $assoc_args;

	/**
	 * Get the name under which to register the CLI command.
	 *
	 * @return string The name under which to register the CLI command.
	 */
	public static function get_command_name() {
		return 'amp validation';
	}

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
	public function run( /** @noinspection PhpUnusedParameterInspection */ $args, $assoc_args ) {
		$this->assoc_args = $assoc_args;

		$scannable_url_provider  = $this->get_validation_url_provider();
		$url_validation_provider = $this->get_validation_provider();
		$urls                    = $scannable_url_provider->get_urls();

		$number_urls_to_crawl = count( $urls );
		if ( ! $number_urls_to_crawl ) {
			if ( ! empty( Utils\get_flag_value( $this->assoc_args, self::INCLUDE_ARGUMENT, [] ) ) ) {
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

		$this->validate_urls( $urls );

		$this->wp_cli_progress->finish();

		$key_template_type = 'Template or content type';
		$key_url_count     = 'URL Count';
		$key_validity_rate = 'Validity Rate';

		$table_validation_by_type = [];
		foreach ( $url_validation_provider->get_validity_by_type() as $type_name => $validity ) {
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
				$url_validation_provider->get_number_validated(),
				$url_validation_provider->get_total_errors(),
				$url_validation_provider->get_unaccepted_errors()
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
	 * Provides the ScannableURLProvider instance.
	 *
	 * @return ScannableURLProvider
	 * @throws WP_CLI\ExitException If templates are disallowed by current config.
	 */
	private function get_validation_url_provider() {
		if ( ! is_null( $this->scannable_url_provider ) ) {
			return $this->scannable_url_provider;
		}

		$include_conditionals      = Utils\get_flag_value( $this->assoc_args, self::INCLUDE_ARGUMENT, [] );
		$force_crawl_urls          = Utils\get_flag_value( $this->assoc_args, self::FLAG_NAME_FORCE_VALIDATION, false );
		$limit_type_validate_count = Utils\get_flag_value( $this->assoc_args, self::LIMIT_URLS_ARGUMENT, 100 );

		/*
		 * Handle the argument and flag passed to the command: --include and --force.
		 * If the self::INCLUDE_ARGUMENT is present, force crawling of URLs.
		 * The WP-CLI command should indicate which templates are crawled, not the /wp-admin options.
		 */
		if ( ! empty( $include_conditionals ) ) {
			if ( is_string( $include_conditionals ) ) {
				$include_conditionals = explode( ',', $include_conditionals );
			}

			$force_crawl_urls = true;
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

			$disallowed_templates = array_diff( $include_conditionals, $allowed_templates );
			if ( ! empty( $disallowed_templates ) ) {
				WP_CLI::error(
					sprintf(
						'Templates not supported in legacy Reader mode with current configuration: %s',
						implode( ',', $disallowed_templates )
					)
				);
			}

			if ( empty( $include_conditionals ) ) {
				$include_conditionals = $allowed_templates;
			}
		}

		$this->scannable_url_provider = new ScannableURLProvider(
			new URLScanningContext(
				$limit_type_validate_count,
				$include_conditionals,
				$force_crawl_urls
			)
		);

		return $this->scannable_url_provider;
	}

	/**
	 * Provides the site scan instance.
	 *
	 * @return URLValidationProvider
	 */
	private function get_validation_provider() {
		if ( ! is_null( $this->url_validation_provider ) ) {
			return $this->url_validation_provider;
		}

		$this->url_validation_provider = new URLValidationProvider();

		return $this->url_validation_provider;
	}

	/**
	 * Validates the URLs.
	 *
	 * @param array $urls URLs to validate, or null to get URLs from the scannable URL provider.
	 */
	private function validate_urls( $urls = null ) {
		$url_validation_provider = $this->get_validation_provider();

		if ( ! $urls ) {
			$scannable_url_provider = $this->get_validation_url_provider();
			$urls                   = $scannable_url_provider->get_urls();
		}

		foreach ( $urls as $url ) {
			$validity = $url_validation_provider->get_url_validation( $url['url'], $url['type'] );

			if ( $this->wp_cli_progress ) {
				$this->wp_cli_progress->tick();
			}

			if ( is_wp_error( $validity ) ) {
				WP_CLI::warning(
					sprintf(
						'Validate URL error (%1$s): %2$s URL: %3$s',
						$validity->get_error_code(),
						$validity->get_error_message(),
						$url['url']
					)
				);
			}
		}
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
	public function reset( /** @noinspection PhpUnusedParameterInspection */ $args, $assoc_args ) {
		global $wpdb;
		WP_CLI::confirm(
			'Are you sure you want to empty all amp_validated_url posts and amp_validation_error taxonomy terms?',
			$assoc_args
		);

		// Delete all posts.
		$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s",
				AMP_Validated_URL_Post_Type::POST_TYPE_SLUG
			)
		);
		$query = $wpdb->prepare(
			"SELECT ID FROM $wpdb->posts WHERE post_type = %s",
			AMP_Validated_URL_Post_Type::POST_TYPE_SLUG
		);
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
		$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT( * ) FROM $wpdb->term_taxonomy WHERE taxonomy = %s",
				AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG
			)
		);
		$query = $wpdb->prepare(
			"SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = %s",
			AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG
		);
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
	 * @alias      nonce
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
	 * @alias      check
	 *
	 * @param array $args Args.
	 * @throws WP_CLI\ExitException If the home URL is missing a scheme or host.
	 * @throws WP_CLI\ExitException If the supplied URL does not belong to the current site.
	 * @throws WP_CLI\ExitException If an error occurred during the validation.
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
			WP_CLI::error( $result->get_error_message() ? $result : $result->get_error_code() );
		}

		WP_CLI::line( wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}
}
