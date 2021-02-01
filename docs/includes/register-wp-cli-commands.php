<?php
/**
 * Register the WP-CLI commands for documentation generation.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\Documentation\Cli\DocsCommandNamespace;
use AmpProject\AmpWP\Documentation\Cli\GenerateCommand;

if ( ! defined( 'WP_CLI' ) || ! class_exists( 'WP_CLI' ) ) {
	return;
}

WP_CLI::add_command( 'amp docs', DocsCommandNamespace::class );
WP_CLI::add_command( 'amp docs generate', GenerateCommand::class );
