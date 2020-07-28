<?php

use AmpProject\AmpWP\Tests\Cli\ReferenceSiteCommandNamespace;
use AmpProject\AmpWP\Tests\Cli\ReferenceSiteImportCommand;

if ( ! defined( 'WP_CLI' ) || ! class_exists( 'WP_CLI' ) ) {
	return;
}

// Conditional bootstrapping to prepare outside of active plugin.
if ( ! defined( 'AMP__DIR__' ) ) {
	define( 'AMP__DIR__', realpath( dirname( dirname( __DIR__ ) ) ) );
}
if ( ! class_exists( 'AmpProject\AmpWP\Tests\Cli\ReferenceSiteCommandNamespace' ) ) {
	$autoloader = AMP__DIR__ . '/vendor/autoload.php';
	if ( file_exists( $autoloader ) ) {
		include $autoloader;
	}
}

WP_CLI::add_command( 'amp reference-site', ReferenceSiteCommandNamespace::class );
WP_CLI::add_command( 'amp reference-site import', ReferenceSiteImportCommand::class );
