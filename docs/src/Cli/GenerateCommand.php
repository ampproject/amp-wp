<?php
/**
 * Class FileReflector.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Cli;

use AmpProject\AmpWP\Documentation\Parser\Parser;
use WP_CLI;
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

		$source_folder      = realpath( $source_folder );
		$destination_folder = realpath( $destination_folder );

		$output_file = $destination_folder . '/docs.json';

		$json   = $this->get_phpdoc_data( $source_folder );
		$result = file_put_contents( $output_file, $json );

		WP_CLI::line();

		if ( false === $result ) {
			WP_CLI::error( sprintf( 'Problem writing %1$s bytes of data to %2$s', strlen( $json ), $output_file ) );
			exit;
		}

		WP_CLI::success( sprintf( 'Data exported to %1$s', $output_file ) );
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
	private function get_phpdoc_data( $path, $format = 'json' ) {
		WP_CLI::line( sprintf( 'Extracting PHPDoc from %1$s. This may take a few minutes...', $path ) );
		$parser  = new Parser();
		$is_file = is_file( $path );
		$files   = $is_file ? [ $path ] : $parser->get_wp_files( $path );
		$path    = $is_file ? dirname( $path ) : $path;

		if ( $files instanceof WP_Error ) {
			WP_CLI::error( sprintf( 'Problem with %1$s: %2$s', $path, $files->get_error_message() ) );
			exit;
		}

		$output = $parser->parse_files( $files, $path );

		if ( 'json' === $format ) {
			return json_encode( $output, JSON_PRETTY_PRINT );
		}

		return $output;
	}
}
