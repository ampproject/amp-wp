<?php

use AmpProject\AmpWP\Tests\Cli\ReferenceSiteCommandNamespace;
use AmpProject\AmpWP\Tests\Cli\ReferenceSiteExportCommand;
use AmpProject\AmpWP\Tests\Cli\ReferenceSiteImportCommand;

if ( ! defined( 'WP_CLI' ) || ! class_exists( 'WP_CLI' ) ) {
	return;
}

WP_CLI::add_command( 'amp reference-site', ReferenceSiteCommandNamespace::class );
WP_CLI::add_command( 'amp reference-site import', ReferenceSiteImportCommand::class );
WP_CLI::add_command( 'amp reference-site export', ReferenceSiteExportCommand::class );
