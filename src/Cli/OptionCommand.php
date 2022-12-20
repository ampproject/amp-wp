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
use AmpProject\AmpWP\PluginSuppression;
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
	 * ReaderThemes instance.
	 *
	 * @var ReaderThemes
	 */
	private $reader_themes;

	/**
	 * PluginSuppression instance.
	 *
	 * @var PluginSuppression
	 */
	private $plugin_suppression;

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
	 * @param ReaderThemes      $reader_themes ReaderThemes instance.
	 * @param PluginSuppression $plugin_suppression PluginSuppression instance.
	 */
	public function __construct( ReaderThemes $reader_themes, PluginSuppression $plugin_suppression ) {
		$this->reader_themes      = $reader_themes;
		$this->plugin_suppression = $plugin_suppression;
	}

	/**
	 * Gets the value for an option.
	 *
	 * @param array $args       Array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 */
	public function get( $args, $assoc_args ) {
		// TODO: Implement get command using REST API.
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
		// TODO: Implement update command using REST API.
	}

	/**
	 * List AMP options.
	 *
	 * @subcommand list
	 */
	public function list_() {
		// TODO: Implement list command using REST API.
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
