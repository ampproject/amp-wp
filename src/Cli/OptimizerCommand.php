<?php
/**
 * Class OptimizerCommand.
 *
 * Commands that deal with the AMP optimizer.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Cli;

use AmpProject\AmpWP\Infrastructure\CliCommand;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Optimizer\OptimizerService;
use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\TransformationEngine;
use WP_CLI;

/**
 * Commands that deal with the AMP optimizer.
 *
 * @since 2.1.0
 * @internal
 */
final class OptimizerCommand implements Service, CliCommand {


	/**
	 * Optimizer service instance to use.
	 *
	 * @var OptimizerService
	 */
	private $optimizer_service;

	/**
	 * Get the name under which to register the CLI command.
	 *
	 * @return string The name under which to register the CLI command.
	 */
	public static function get_command_name() {
		return 'amp optimizer';
	}

	/**
	 * OptimizerCommand constructor.
	 *
	 * @param OptimizerService $optimizer_service Optimizer service instance to use.
	 */
	public function __construct( OptimizerService $optimizer_service ) {
		$this->optimizer_service = $optimizer_service;
	}

	/**
	 * Run a file through the AMP Optimizer.
	 *
	 * ## OPTIONS
	 *
	 * [<file>]
	 * : Input file to run through the AMP Optimizer. Omit or use '-' to read from STDIN.
	 *
	 * ## EXAMPLES
	 *
	 * # Test <amp-img> SSR transformations and store them in a new file named 'output.html'.
	 * $ echo '<amp-img src="image.jpg" width="500" height="500">' | wp amp optimizer optimize > output.html
	 *
	 * @param array $args       Array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 * @throws WP_CLI\ExitException If the requested file could not be read.
	 */
	public function optimize( $args, $assoc_args ) {
		$file = '-';

		if ( count( $args ) > 0 ) {
			$file = array_shift( $args );
		}

		if (
			'-' !== $file
			&&
			(
				! is_file( $file )
				||
				! is_readable( $file )
			)
		) {
			WP_CLI::error( "Could not read file: '{$file}'." );
		}

		if ( '-' === $file ) {
			$file = 'php://stdin';
		}

		$html           = file_get_contents( $file );
		$errors         = new ErrorCollection();
		$optimized_html = $this->optimizer_service->optimizeHtml( $html, $errors );

		WP_CLI::line( $optimized_html );

		/** @var Error $error */
		foreach ( $errors as $error ) {
			WP_CLI::warning( "[{$error->getCode()}] {$error->getMessage()}" );
		}
	}
}
