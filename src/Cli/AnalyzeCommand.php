<?php
/**
 * Class AnalyzeCommand.
 *
 * Commands that deal with analyze an URL against the PX Engine.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Cli;

use AmpProject\AmpWP\Infrastructure\CliCommand;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\RemoteRequest\CachedRemoteGetRequest;
use AmpProject\AmpWP\RemoteRequest\WpHttpRemoteGetRequest;
use AmpProject\AmpWP\Validation\ScannableURLProvider;
use PageExperience\Engine;
use PageExperience\Engine\Analysis\Issue;
use PageExperience\Engine\ConfigurationProfile;
use WP_CLI;
use WP_CLI\Formatter;

/**
 * Analyze site URLs with Page Experience Engine.
 *
 * @internal
 */
final class AnalyzeCommand implements Service, CliCommand {

	/**
	 * The WP CLI progress bar.
	 *
	 * @var \cli\progress\Bar|\WP_CLI\NoOp
	 */
	public $wp_cli_progress;

	/**
	 * ScannableURLProvider instance.
	 *
	 * @var ScannableURLProvider
	 */
	private $scannable_url_provider;

	/**
	 * Get the name under which to register the CLI command.
	 *
	 * @return string The name under which to register the CLI command.
	 */
	public static function get_command_name() {
		return 'amp';
	}

	/**
	 * Construct.
	 *
	 * @param ScannableURLProvider $scannable_url_provider  Scannable URL provider.
	 */
	public function __construct( ScannableURLProvider $scannable_url_provider ) {
		$this->scannable_url_provider = $scannable_url_provider;
	}

	/**
	 * Analyze site URLs with Page Experience Engine.
	 *
	 * [<url>]
	 * : URL to analyze.
	 *
	 * ## OPTIONS
	 *
	 * [--timeout=<timeout_in_seconds>]
	 * : Timeout value to use in seconds.
	 * ---
	 * default: 5
	 * ---
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - count
	 *   - csv
	 *   - json
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * # Analyze all site URLs.
	 * $ wp amp analyze --timeout=20
	 *
	 * # Analyze a specific URL.
	 * $ wp amp analyze http://example.com
	 *
	 * @param array $urls       URLs to analyze.
	 * @param array $assoc_args Associative args.
	 */
	public function analyze( $urls = [], $assoc_args ) {
		if ( ! is_array( $urls ) || empty( $urls ) ) {
			$urls = $this->scannable_url_provider->get_urls();
		}

		$number_of_urls = count( $urls );

		$assoc_args = wp_parse_args(
			$assoc_args,
			[
				'timeout' => WpHttpRemoteGetRequest::DEFAULT_TIMEOUT,
				'format'  => 'table',
			]
		);

		WP_CLI::log( 'Analyzing Page Experience.' );

		$this->wp_cli_progress = WP_CLI\Utils\make_progress_bar(
			sprintf( 'Analyzing %d URLs...', $number_of_urls ),
			$number_of_urls
		);

		$results     = [];
		$failed_urls = [];

		$this->wp_cli_progress->display();

		foreach ( $urls as $url ) {
			$url = isset( $url['url'] ) ? $url['url'] : $url;

			try {
				$results[ $url ] = $this->get_analysis_results(
					$url,
					absint( $assoc_args['timeout'] )
				);
			} catch ( \Exception $error ) {
				$failed_urls[] = [
					'URL'     => $url,
					'Message' => $error->getMessage(),
				];
			}

			if ( $this->wp_cli_progress ) {
				$this->wp_cli_progress->tick();
			}
		}

		$this->wp_cli_progress->finish();

		if ( ! empty( $results ) ) {
			WP_CLI::line();
			WP_CLI::line( 'Analyzed URLs:' );
			$this->print_metrics( $results, $assoc_args );
		}

		if ( ! empty( $failed_urls ) ) {
			WP_CLI::line();
			WP_CLI::line( 'URLs failed to analyze:' );
			WP_CLI\Utils\format_items( $assoc_args['format'], $failed_urls, [ 'URL' ] );
		}
	}

	/**
	 * Get URL analysis result.
	 *
	 * @param string $url     The URL to analyze.
	 * @param int    $timeout Timeout value to use in seconds.
	 */
	private function get_analysis_results( $url, $timeout ) {
		$remote_request = new CachedRemoteGetRequest( new WpHttpRemoteGetRequest( true, $timeout ) );
		$engine         = new Engine( $remote_request );
		$profile        = new ConfigurationProfile();
		$analysis       = $engine->analyze( $url, $profile );

		return $analysis->getResults();
	}

	/**
	 * Print PageSpeed Insight metrics.
	 *
	 * @param array $results    Analysis results.
	 * @param array $assoc_args Associative args.
	 */
	private function print_metrics( $results, $assoc_args ) {
		$table_data = [];
		$home_url   = home_url();

		foreach ( $results as $url => $result ) {
			$table_data[] = [
				'URL'     => str_replace( $home_url, '', $url ),
				'Metrics' => $this->get_metrics( $result['first-contentful-paint'], $assoc_args ),
			];

			$table_data[] = [
				'URL'     => '',
				'Metrics' => $this->get_metrics( $result['interactive'], $assoc_args ),
			];

			$table_data[] = [
				'URL'     => '',
				'Metrics' => $this->get_metrics( $result['speed-index'], $assoc_args ),
			];

			$table_data[] = [
				'URL'     => '',
				'Metrics' => $this->get_metrics( $result['total-blocking-time'], $assoc_args ),
			];

			$table_data[] = [
				'URL'     => '',
				'Metrics' => $this->get_metrics( $result['largest-contentful-paint'], $assoc_args ),
			];

			$table_data[] = [
				'URL'     => '',
				'Metrics' => $this->get_metrics( $result['cumulative-layout-shift'], $assoc_args ),
			];

			$table_data[] = [
				'URL'     => '',
				'Metrics' => '',
			];
		}

		$args      = [
			'format' => $assoc_args['format'],
			'fields' => [ 'URL', 'Metrics' ],
		];
		$formatter = new Formatter( $args );
		$formatter->display_items( $table_data, true );
	}

	/**
	 * Get insight metrics.
	 *
	 * @param Issue $issue      Result entry of an analysis for a URL.
	 * @param array $assoc_args Associative args.
	 */
	private function get_metrics( Issue $issue, $assoc_args ) {
		$score = $issue->getScore();
		$value = $issue->getDisplayValue();

		$icon        = '';
		$color_token = '';

		if ( $score >= 0.9 ) {
			$icon        = '●';
			$color_token = '%g';
		} elseif ( $score >= 0.5 ) {
			$icon        = '◼';
			$color_token = '%y';
		} else {
			$icon        = '▲';
			$color_token = '%r';
		}

		if ( 'table' === $assoc_args['format'] ) {
			$icon  = $this->colorize( $icon, $color_token );
			$value = $this->colorize( $value, $color_token );
		}

		return $icon . ' ' . $issue->getLabel() . ': ' . $value;
	}

	/**
	 * Colorize a string.
	 *
	 * @param string $string      String to colorize for output.
	 * @param string $color_token Color token to colorize with.
	 */
	private function colorize( $string, $color_token ) {
		return WP_CLI::colorize( $color_token . $string . '%n' );
	}
}
