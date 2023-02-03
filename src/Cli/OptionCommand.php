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
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_CLI\Formatter;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\CliCommand;

/**
 * Retrieves and sets AMP plugin options.
 *
 * ## EXAMPLES
 *
 * # Get AMP plugin option.
 * $ wp amp option get theme_support
 * standard
 *
 * # Update AMP plugin option.
 * $ wp amp option update theme_support reader
 * Success: Updated theme_support option.
 *
 * # List AMP plugin options.
 * $ wp amp option list
 * +------------------+----------------+
 * | key              | value          |
 * +------------------+----------------+
 * | theme_support    | standard       |
 * | mobile_redirect  | disabled       |
 * | reader_theme     | legacy         |
 * +------------------+----------------+
 *
 * @since 2.4.0
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
		Option::SANDBOXING_ENABLED,
		Option::SANDBOXING_LEVEL,
		Option::DELETE_DATA_AT_UNINSTALL,
	];

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
	 * ## OPTIONS
	 *
	 * <key>
	 * : Key for the option.
	 *
	 * [--format=<format>]
	 * : Get value in a particular format.
	 * ---
	 * default: var_export
	 * options:
	 *   - var_export
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * # Get option.
	 * $ wp amp option get theme_support
	 * standard
	 *
	 * # Get option in JSON format.
	 * $ wp amp option get theme_support --format=json
	 *
	 * @param array $args       Array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 */
	public function get( $args, $assoc_args ) {
		list( $option_name ) = $args;

		$user_cap = $this->check_user_cap();

		if ( $user_cap instanceof WP_Error ) {
			WP_CLI::error( $user_cap->get_error_message( 'amp_rest_cannot_manage_options' ) . PHP_EOL . WP_CLI::colorize( '%y' . $user_cap->get_error_message( 'amp_rest_cannot_manage_options_help' ) . '%n' ) );
		}

		$options = $this->get_options();

		if ( $options instanceof WP_Error ) {
			/* translators: %s: error message */
			WP_CLI::error( sprintf( __( 'Could not retrieve options: %s', 'amp' ), $options->get_error_message() ) );
		}

		if ( ! isset( $options[ $option_name ] ) ) {
			/* translators: %s: option name */
			WP_CLI::error( sprintf( __( 'Could not get "%s" option. Does it exist?', 'amp' ), $option_name ) );
		}

		WP_CLI::print_value( $options[ $option_name ], $assoc_args );
	}

	/**
	 * Updates an option value.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The name of the option to update.
	 *
	 * <value>
	 * : The new value.
	 *
	 * ## EXAMPLES
	 *
	 * # Update plugin option.
	 * $ wp amp option update theme_support reader
	 * Success: Updated theme_support option.
	 *
	 * @alias set
	 *
	 * @param array $args       Array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 */
	public function update( $args, $assoc_args ) {
		list( $option_name, $option_value ) = $args;

		$user_cap = $this->check_user_cap();

		if ( $user_cap instanceof WP_Error ) {
			WP_CLI::error( $user_cap->get_error_message( 'amp_rest_cannot_manage_options' ) . PHP_EOL . WP_CLI::colorize( '%y' . $user_cap->get_error_message( 'amp_rest_cannot_manage_options_help' ) . '%n' ) );
		}

		if ( ! in_array( $option_name, self::ALLOWED_OPTIONS, true ) ) {
			/* translators: %1$s: option name, %2$s: list of allowed options */
			WP_CLI::error( sprintf( __( 'The option "%1$s" is not among the following options that can currently be managed via CLI: %2$s', 'amp' ), $option_name, implode( ', ', self::ALLOWED_OPTIONS ) ) );
		}

		// Update type for some options.
		if ( Option::SANDBOXING_LEVEL === $option_name ) {
			$option_value = (int) $option_value;
		}

		$update = $this->update_option( $option_name, $option_value );

		if ( $update instanceof WP_Error ) {
			/* translators: %1$s: option name, %2$s: error message */
			WP_CLI::error( sprintf( __( 'Could not update "%1$s" option: %2$s', 'amp' ), $option_name, $update->get_error_message() ) );
		}

		/* translators: %s: option name */
		WP_CLI::success( sprintf( __( 'Updated "%s" option.', 'amp' ), $option_name ) );
	}

	/**
	 * List plugin options.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : The serialization format for the value.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - count
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * # List plugin options.
	 * $ wp amp option list
	 * +--------------------------+--------------+
	 * | option_name              | option_value |
	 * +--------------------------+--------------+
	 * | reader_theme             | legacy       |
	 * | theme_support            | reader       |
	 * | delete_data_at_uninstall | 1            |
	 * +--------------------------+--------------+
	 *
	 * # List plugin options in JSON format.
	 * $ wp amp option list --format=json
	 * [{"option_name":"reader_theme","option_value":"legacy"},{"option_name":"theme_support","option_value":"reader"},{"option_name":"delete_data_at_uninstall","option_value":"1"}]
	 *
	 * @subcommand list
	 *
	 * @param array $args       Array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 */
	public function list_( $args, $assoc_args ) {
		$user_cap = $this->check_user_cap();

		if ( $user_cap instanceof WP_Error ) {
			WP_CLI::error( $user_cap->get_error_message( 'amp_rest_cannot_manage_options' ) . PHP_EOL . WP_CLI::colorize( '%y' . $user_cap->get_error_message( 'amp_rest_cannot_manage_options_help' ) . '%n' ) );
		}

		$options = $this->get_options();

		if ( $options instanceof WP_Error ) {
			/* translators: %s: error message */
			WP_CLI::error( sprintf( __( 'Could not retrieve options: %s', 'amp' ), $options->get_error_message() ) );
		}

		$formatter = new Formatter(
			$assoc_args,
			[
				'option_name',
				'option_value',
			]
		);

		$formatter->display_items(
			array_map(
				static function ( $option_name ) use ( $options ) {
					return [
						'option_name'  => $option_name,
						'option_value' => $options[ $option_name ],
					];
				},
				self::ALLOWED_OPTIONS
			)
		);

		if ( ! WP_CLI\Utils\isPiped() ) {
			WP_CLI::line( '' );

			WP_CLI::line(
				sprintf(
					/* translators: %s: wp option get amp-options command */
					__( '* Only the above listed options can currently be updated via the CLI. To list all options, use the %s command.', 'amp' ),
					WP_CLI::colorize( '%Ywp option get amp-options%n' )
				)
			);

			WP_CLI::line(
				sprintf(
					/* translators: %s: AMP plugin GitHub issues URL */
					__( '* Please raise a feature request at %s to request a new option to be managed via the CLI.', 'amp' ),
					WP_CLI::colorize( '%Bhttps://github.com/ampproject/amp-wp/issues%n' )
				)
			);
		}
	}

	/**
	 * List reader themes.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Get value in a particular format.
	 * ---
	 * default: json
	 * options:
	 *   - var_export
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * # List reader themes.
	 * $ wp amp option list-reader-themes
	 * ["twentytwenty","twentytwentyone","legacy"]
	 *
	 * @alias get-reader-themes
	 * @subcommand list-reader-themes
	 *
	 * @param array $args       Array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 */
	public function list_reader_themes( $args, $assoc_args ) {
		$user_cap = $this->check_user_cap();

		if ( $user_cap instanceof WP_Error ) {
			WP_CLI::error( $user_cap->get_error_message( 'amp_rest_cannot_manage_options' ) . PHP_EOL . WP_CLI::colorize( '%y' . $user_cap->get_error_message( 'amp_rest_cannot_manage_options_help' ) . '%n' ) );
		}

		WP_CLI::print_value( wp_list_pluck( $this->reader_themes->get_themes(), 'slug' ), $assoc_args );
	}

	/**
	 * Check if the user is set up to use the REST API.
	 *
	 * @return true|WP_Error True if the request has permission; WP_Error object otherwise.
	 */
	private function check_user_cap() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$cap_error = new WP_Error(
				'amp_rest_cannot_manage_options',
				__( 'Sorry, you are not allowed to manage options for the AMP plugin for WordPress.', 'amp' )
			);

			$cap_error->add(
				'amp_rest_cannot_manage_options_help',
				__( 'Try using --user=<id|login|email> to set the user context or set it in wp-cli.yml.', 'amp' )
			);

			return $cap_error;
		}

		return true;
	}

	/**
	 * Get the options.
	 *
	 * @return WP_Error|mixed WP_Error on failure, response data on success.
	 */
	private function get_options() {
		$response = $this->do_request( 'GET', self::OPTIONS_ENDPOINT, [] );

		if ( $response->as_error() ) {
			return $response->as_error();
		}

		return $response->get_data();
	}

	/**
	 * Update an option.
	 *
	 * @param string $option_name  Option name.
	 * @param string $option_value Option value.
	 *
	 * @return WP_Error|mixed WP_Error on failure, response data on success.
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
			return $response->as_error();
		}

		return $response->get_data();
	}

	/**
	 * Do a REST Request
	 *
	 * @param string $method HTTP method.
	 * @param string $route REST route.
	 * @param array  $assoc_args Associative args.
	 *
	 * @return WP_REST_Response Response object.
	 */
	private function do_request( $method, $route, $assoc_args ) {
		$request = new WP_REST_Request( $method, $route );

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
