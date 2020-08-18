<?php
/**
 * Class FileReflector.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Cli;

use AmpProject\AmpWP\Documentation\Parser\Parser;
use WP_CLI;
use WP_CLI\Utils;
use WP_Error;

/**
 * Generate the reference documentation by parsing the source code.
 *
 * @package AmpProject\AmpWP\Docs\Cli
 */
final class GenerateCommand {

	/**
	 * Generates the reference documentation by parsing the source code.
	 *
	 * Uses phpDocumentor and custom reflector from the WP DevHub parser plugin.
	 *
	 * ## OPTIONS
	 *
	 * <source_folder>
	 * : Path to the source folder that contains the source files to be parsed.
	 *
	 * <destination_folder>
	 * : Path to the destination folder where the output should be written to.
	 *
	 * ## EXAMPLES
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		list( $source_folder, $destination_folder ) = $args;
		$format = (string) Utils\get_flag_value( $assoc_args, 'format', 'json' );

		$source_folder      = realpath( $source_folder );
		$destination_folder = realpath( $destination_folder );

		$output_file = $destination_folder . '/docs.json';

		$data = $this->get_phpdoc_data( $source_folder );

		switch ( $format ) {
			case 'json':
				$json   = json_encode( $data, JSON_PRETTY_PRINT );
				$result = file_put_contents( $output_file, $data );
				break;
			case 'markdown':
				// TODO
			case '':
				WP_CLI::error( "A value of 'json' or 'markdown' is required for the --format flag." );
			default:
				WP_CLI::error( "Invalid --format value '{$format}' provided. Possible values: json, markdown" );
		}

		WP_CLI::line();

		if ( false === $result ) {
			WP_CLI::error( "Problem writing data to file '{$output_file}'" );
			exit;
		}

		WP_CLI::success( "Data exported to {$output_file}" );
		WP_CLI::line();
	}

	/**
	 * Generate the data from the PHPDoc markup.
	 *
	 * @param string $path   Directory or file to scan for PHPDoc
	 * @param string $format What format the data is returned in: [json|array].
	 *
	 * @return string|array
	 */
	private function get_phpdoc_data( $path ) {
		WP_CLI::line( sprintf( 'Extracting PHPDoc from %1$s. This may take a few minutes...', $path ) );
		$parser  = new Parser();
		$is_file = is_file( $path );
		$files   = $is_file ? [ $path ] : $parser->get_files( $path, $this->get_excluded_dirs() );
		$path    = $is_file ? dirname( $path ) : $path;

		if ( $files instanceof WP_Error ) {
			WP_CLI::error( sprintf( 'Problem with %1$s: %2$s', $path, $files->get_error_message() ) );
			exit;
		}

		return $parser->parse_files( $files, $path );
	}

	/**
	 * Get the list of regex patterns of folders to exclude.
	 *
	 * @return string[] Array of regex patterns.
	 */
	private function get_excluded_dirs() {
		return [
			'#^.*/amp/(assets|bin|build|node_modules|tests|vendor)/#',
			'#^.*/amp/lib/common/(tests|vendor)/#',
			'#^.*/amp/lib/optimizer/(tests|vendor)/#',
		];
	}
}
