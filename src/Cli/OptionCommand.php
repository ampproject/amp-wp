<?php
/**
 * Class OptionCommand.
 *
 * Commands that deal with the AMP options.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Cli;

use AmpProject\AmpWP\Infrastructure\CliCommand;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AMP_Options_Manager;
use AMP_Theme_Support;
use WP_CLI;
use AmpProject\AmpWP\PluginSuppression;
use AmpProject\AmpWP\Admin\ReaderThemes;

/**
 * CLI command to manage AMP options.
 *
 * @since 2.3.1
 */
final class OptionCommand implements Service, CliCommand {

	/**
	 * Cached results of allowed_args.
	 *
	 * @var array
	 */
	private $allowed_args;

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
		list( $key ) = $args;

		$value = AMP_Options_Manager::get_option( $key );

		if ( false === $value ) {
			WP_CLI::error( "Could not get '{$key}' option. Does it exist?" );
			WP_CLI::line( 'Try `wp amp option list` to see all options.' );
		}

		WP_CLI::print_value( $value, $assoc_args );
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
		list( $key, $value ) = $args;

		if ( ! $this->is_allowed_arg( $key ) ) {
			WP_CLI::error( "Could not update '{$key}' option. Is it whitelisted?", false );
			WP_CLI::line( 'Allowed AMP options to be updated using WP CLI are: ' );

			$allowed_args = array_keys( $this->get_allowed_args() );

			/**
			 * Format array to be used with WP_CLI\Utils\format_items().
			 */
			$allowed_args = array_map(
				static function ( $allowed_arg ) {
					return [ 'option_name' => $allowed_arg ];
				},
				$allowed_args
			);

			WP_CLI\Utils\format_items( 'table', $allowed_args, [ 'option_name' ] );
			WP_CLI::halt( 1 );
		}

		if ( ! $this->is_valid_value( $key, $value ) ) {
			WP_CLI::error( "Could not update '{$key}' option. Is the value valid?", false );
			WP_CLI::line( "Allowed values for '{$key}' option are: " );

			$allowed_args = $this->get_allowed_args()[ $key ]['enum'];

			/**
			 * Format array to be used with WP_CLI\Utils\format_items().
			 */
			$allowed_args = array_map(
				static function ( $allowed_arg ) {
					return [ 'option_value' => $allowed_arg ];
				},
				$allowed_args
			);

			WP_CLI\Utils\format_items( 'table', $allowed_args, [ 'option_value' ] );
			WP_CLI::halt( 1 );
		}

		if ( ! AMP_Options_Manager::update_option( $key, $value ) ) {
			WP_CLI::error( "Could not update '{$key}' option." );
		}

		WP_CLI::success( "Updated '{$key}' option." );
	}

	/**
	 * List AMP options.
	 *
	 * @subcommand list
	 */
	public function list_() {
		$amp_options = AMP_Options_Manager::get_options();

		/**
		 * Format array to be used with WP_CLI\Utils\format_items().
		 */
		$amp_options = array_map(
			static function ( $value, $key ) {
				return [
					'option_name'  => $key,
					'option_value' => $value,
				];
			},
			$amp_options,
			array_keys( $amp_options )
		);

		WP_CLI::line( 'AMP options:' );
		WP_CLI\Utils\format_items( 'table', $amp_options, [ 'option_name', 'option_value' ] );

		WP_CLI::line(); // Add a new line for readability.
		WP_CLI::line( 'Allowed AMP options to be updated using WP CLI are: ' );

		$allowed_args = $this->get_allowed_args();

		/**
		 * Format array to be used with WP_CLI\Utils\format_items().
		 */
		$allowed_args = array_map(
			static function ( $allowed_arg, $key ) {
				return [
					'option_name'  => $key,
					'option_value' => implode( ', ', $allowed_arg['enum'] ),
				];
			},
			$allowed_args,
			array_keys( $allowed_args )
		);

		WP_CLI\Utils\format_items( 'table', $allowed_args, [ 'option_name', 'option_value' ] );
	}

	/**
	 * Construct schema for the options.
	 *
	 * @return array
	 */
	private function get_allowed_args() {
		if ( ! $this->allowed_args ) {
			$this->allowed_args = [
				Option::THEME_SUPPORT      => [
					'type' => 'string',
					'enum' => [
						AMP_Theme_Support::READER_MODE_SLUG,
						AMP_Theme_Support::STANDARD_MODE_SLUG,
						AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
					],
				],
				Option::MOBILE_REDIRECT    => [
					'type' => 'boolean',
					'enum' => [
						'true',
						'false',
					],
				],
				Option::READER_THEME       => [
					'type' => 'string',
					'enum' => wp_list_pluck( $this->reader_themes->get_themes(), 'slug' ),
				],
				Option::SUPPRESSED_PLUGINS => [
					'type'  => 'array',
					'items' => [
						'type' => 'string',
					],
					'enum'  => array_keys( $this->plugin_suppression->get_suppressible_plugins_with_details() ),
				],
			];
		}

		return $this->allowed_args;
	}

	/**
	 * Validate if a valid option name is passed.
	 *
	 * @param string $name Option name.
	 * @return bool Whether the option name is valid.
	 */
	private function is_allowed_arg( $name ) {
		if ( ! isset( $this->get_allowed_args()[ $name ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate if a valid option value is passed.
	 *
	 * @param string $name  Option name.
	 * @param mixed  $value Option value.
	 *
	 * @return bool Whether the option value is valid.
	 */
	private function is_valid_value( $name, $value ) {
		$arg = isset( $this->get_allowed_args()[ $name ] ) ? $this->get_allowed_args()[ $name ] : null;

		if ( isset( $arg['enum'] ) && ! in_array( $value, $arg['enum'], true ) ) {
			return false;
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
