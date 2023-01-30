<?php
/**
 * Class OptionCommand.
 *
 * Commands that deal with the AMP options.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Cli;

use WP_CLI;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\CliCommand;

/**
 * CLI command to manage AMP options.
 *
 * @since 2.3.1
 */
final class OptionCommand implements Service, CliCommand {

	/**
	 * Options endpoint.
	 *
	 * @var string
	 */
	const OPTIONS_ENDPOINT = '/amp/v1/options';

	/**
	 * Allowed options to be managed via the CLI.
	 *
	 * @var string[]
	 */
	const ALLOWED_OPTIONS = [
		Option::READER_THEME,
		Option::THEME_SUPPORT,
		Option::MOBILE_REDIRECT,
	];

	/**
	 * Reader themes key.
	 *
	 * @var string
	 */
	const READER_THEMES = 'reader_themes';

	/**
	 * ReaderThemes instance.
	 *
	 * @var ReaderThemes
	 */
	private $reader_themes;

	/**
	 * Get the name under which to register the CLI command.
	 *
	 * @return string The name under which to register the CLI command.
	 */
	public static function get_command_name() {
		return 'amp option';
	}

	/**
	 * OptionCommand constructor.
	 *
	 * @param ReaderThemes $reader_themes ReaderThemes instance.
	 */
	public function __construct( ReaderThemes $reader_themes ) {
		$this->reader_themes = $reader_themes;
	}

	/**
	 * Gets the value for an option.
	 *
	 * @param array $args       Array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 */
	public function get( $args, $assoc_args ) {
		list( $option_name ) = $args;

		$this->check_user();

		$options = $this->get_options();

		if ( ! isset( $options[ $option_name ] ) ) {
			/* translators: %s: option name */
			WP_CLI::error( sprintf( __( 'Option %s does not exist.', 'amp' ), $option_name ), false );
			WP_CLI::line( WP_CLI::colorize( '%y' . __( 'Try using `wp amp option list` to see all available options.', 'amp' ) . '%n' ) );
			WP_CLI::halt( 1 );
		}

		WP_CLI\Utils\format_items(
			'table',
			[
				'option_name'  => $option_name,
				'option_value' => $options[ $option_name ],
			],
			[ 'option_name', 'option_value' ]
		);
	}

	/**
	 * Updates the value for an option.
	 *
	 * @param array $args       Array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 *
	 * @alias set
	 */
	public function update( $args, $assoc_args ) {
		list( $option_name, $option_value ) = $args;

		$help_message = __( 'Try using `wp amp option list` to see all available options.', 'amp' );

		// Check if option is allowed to be managed via CLI.
		if ( ! in_array( $option_name, self::ALLOWED_OPTIONS, true ) ) {
			/* translators: %s: option name */
			WP_CLI::error( sprintf( __( 'Option %s is not allowed to be managed via CLI.', 'amp' ), $option_name ), false );
			WP_CLI::line( WP_CLI::colorize( '%y' . $help_message . '%n' ) );
			WP_CLI::halt( 1 );
		}

		$this->check_user();

		$options = $this->get_options();

		if ( ! isset( $options[ $option_name ] ) ) {
			/* translators: %s: option name */
			WP_CLI::error( sprintf( __( 'Option %s does not exist.', 'amp' ), $option_name ), false );
			WP_CLI::line( WP_CLI::colorize( '%y' . $help_message . '%n' ) );
			WP_CLI::halt( 1 );
		}

		// Update the option.
		$this->update_option( $option_name, $option_value );
	}

	/**
	 * List AMP options.
	 *
	 * @subcommand list
	 */
	public function list_() {
		$this->check_user();

		$options = $this->get_options();

		// Add reader themes to the options.
		$options[ self::READER_THEMES ] = wp_list_pluck( $this->reader_themes->get_themes(), 'slug' );

		WP_CLI::line( WP_CLI::colorize( '%y' . __( 'Available options:', 'amp' ) . '%n' ) );
		WP_CLI\Utils\format_items(
			'table',
			array_map(
				static function ( $option_name, $option_value ) {
					return compact( 'option_name', 'option_value' );
				},
				array_keys( $options ),
				$options
			),
			[ 'option_name', 'option_value' ]
		);

		WP_CLI::line( '' ); // Add a line break for readability.
		WP_CLI::line( WP_CLI::colorize( '%y' . __( 'Allowed options to be managed via CLI:', 'amp' ) . '%n' ) );
		WP_CLI\Utils\format_items(
			'table',
			array_map(
				static function ( $option_name ) {
					return compact( 'option_name' );
				},
				self::ALLOWED_OPTIONS
			),
			[ 'option_name' ]
		);
	}

	/**
	 * Get the options.
	 *
	 * @return array Options.
	 */
	private function get_options() {
		$response = $this->do_request( 'GET', self::OPTIONS_ENDPOINT, [] );

		if ( $response->as_error() ) {
			/* translators: %s: option name */
			WP_CLI::error( sprintf( __( 'Could not get options: %s', 'amp' ), $response->as_error()->get_error_message() ) );
		}

		return $response->get_data();
	}

	/**
	 * Update an option.
	 *
	 * @param string $option_name  Option name.
	 * @param string $option_value Option value.
	 */
	private function update_option( $option_name, $option_value ) {
		$response = $this->do_request(
			'POST',
			self::OPTIONS_ENDPOINT,
			[
				$option_name => $option_value,
			]
		);

		if ( $response->as_error() ) {
			/* translators: %s: option name */
			WP_CLI::error( sprintf( __( 'Could not update option: %s', 'amp' ), $response->as_error()->get_error_message() ) );
		}

			/* translators: %s: option name */
		WP_CLI::success( sprintf( __( 'Option %s updated.', 'amp' ), $option_name ) );
	}

	/**
	 * Check if the user is set up to use the REST API.
	 *
	 * @return bool Whether the user is set up to use the REST API.
	 */
	private function check_user() {
		if ( ! current_user_can( 'manage_options' ) ) {
			WP_CLI::error( __( 'Sorry, you are not allowed to manage options for this site.', 'amp' ), false );
			WP_CLI::line( WP_CLI::colorize( '%y' . __( 'Try using --user=<id|login|email> to set the user context or set it in wp-cli.yml.', 'amp' ) . '%n' ) );
			WP_CLI::halt( 1 );
		}

		return true;
	}

	/**
	 * Do a REST Request
	 *
	 * @param string $method HTTP method.
	 * @param string $route REST route.
	 * @param array  $assoc_args Associative args.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	private function do_request( $method, $route, $assoc_args ) {
		if ( ! defined( 'REST_REQUEST' ) ) {
			define( 'REST_REQUEST', true );
		}

		$request = new \WP_REST_Request( $method, $route );

		if ( in_array( $method, [ 'POST', 'PUT' ] ) ) {
			$request->set_body_params( $assoc_args );
		} else {
			foreach ( $assoc_args as $key => $value ) {
				$request->set_param( $key, $value );
			}
		}

		return rest_do_request( $request );
	}
}
