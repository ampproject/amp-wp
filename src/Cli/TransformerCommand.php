<?php
/**
 * Class TransformerCommand.
 *
 * Commands that deal with the transformers registered with the AMP optimizer.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Cli;

use AmpProject\AmpWP\Infrastructure\CliCommand;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\Optimizer\Configuration;
use WP_CLI;
use WP_CLI\Utils;

/**
 * Commands that deal with the transformers registered with the AMP optimizer.
 *
 * @since 2.1.0
 * @internal
 */
final class TransformerCommand implements Service, CliCommand {


	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * Get the name under which to register the CLI command.
	 *
	 * @return string The name under which to register the CLI command.
	 */
	public static function get_command_name() {
		return 'amp optimizer transformer';
	}

	/**
	 * TransformerCommand constructor.
	 *
	 * @param Configuration $configuration Configuration object instance to use.
	 */
	public function __construct( Configuration $configuration ) {
		$this->configuration = $configuration;
	}

	/**
	 * List the transformers registered with the AMP Optimizer.
	 *
	 * ## OPTIONS
	 *
	 * [<filter>...]
	 * : Name or partial name to filter the list of transformers by.
	 *
	 * [--strict]
	 * : Enforce strict matching when a filter is provided.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields. Defaults to all fields.
	 *
	 * [--field=<field>]
	 * : Prints the value of a single field for each transformer.
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
	 * @subcommand list
	 *
	 * @param array $args       Array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 * @throws WP_CLI\ExitException If the requested file could not be read.
	 */
	public function list_( $args, $assoc_args ) {
		$strict = Utils\get_flag_value( $assoc_args, 'strict' );
		if ( $strict && empty( $args ) ) {
			WP_CLI::error( 'The --strict option can only be used in combination with a filter.' );
		}

		$default_fields = [
			'transformer',
			'source',
		];

		$defaults = [
			'fields' => implode( ',', $default_fields ),
			'format' => 'table',
		];

		$assoc_args = array_merge( $defaults, $assoc_args );

		$transformer_classes = $this->configuration->get( Configuration::KEY_TRANSFORMERS );

		$transformers = [];
		foreach ( $transformer_classes as $transformer_class ) {
			$name_parts = explode( '\\', $transformer_class );
			$short_name = array_pop( $name_parts );

			$source = 'third-party';
			if ( 0 === strpos( $transformer_class, 'AmpProject\\Optimizer\\Transformer\\' ) ) {
				$source = 'toolbox';
			}
			if ( 0 === strpos( $transformer_class, 'AmpProject\\AmpWP\\Optimizer\\Transformer\\' ) ) {
				$source = 'plugin';
			}

			$transformers[] = [
				'transformer' => $short_name,
				'source'      => $source,
			];
		}

		$transformers = $this->filter_transformers( $transformers, $args, $strict );

		if ( 'count' === $assoc_args['format'] ) {
			WP_CLI::log( (string) count( $transformers ) );
			return;
		}

		if ( empty( $transformers ) ) {
			WP_CLI::error( 'No matching transformers found.' );
		}

		$formatter = new WP_CLI\Formatter( $assoc_args, $default_fields );
		$formatter->display_items( $transformers );
	}

	/**
	 * Filters the values based on a provided filter key.
	 *
	 * @param array $transformers Associative array of transformers to filter.
	 * @param array $filters      Filters to apply.
	 * @param bool  $strict       Whether to apply strict filtering.
	 *
	 * @return array
	 */
	private function filter_transformers( $transformers, $filters, $strict ) {
		if ( empty( $filters ) ) {
			return $transformers;
		}

		$result = [];

		foreach ( $transformers as $value ) {
			foreach ( $filters as $filter ) {
				if ( $strict && $filter !== $value['transformer'] ) {
					continue;
				}

				if ( false === strpos( $value['transformer'], $filter ) ) {
					continue;
				}

				$result[] = $value;
			}
		}

		return $result;
	}
}
