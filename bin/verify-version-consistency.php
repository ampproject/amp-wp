#!/usr/bin/env php
<?php
/**
 * Verify versions referenced in plugin match.
 *
 * @codeCoverageIgnore
 * @package AMP
 */

if ( 'cli' !== php_sapi_name() ) {
	fwrite( STDERR, "Must run from CLI.\n" );
	exit( 1 );
}

$versions = [];

$readme_md = file_get_contents( dirname( __FILE__ ) . '/../README.md' );
if ( ! preg_match( '/\*\*Stable tag:\*\*\s+(?P<version>\S+)/i', $readme_md, $matches ) ) {
	echo "Could not find stable tag in readme\n";
	exit( 1 );
}
$versions['README.md#stable-tag'] = $matches['version'];

$plugin_file = file_get_contents( dirname( __FILE__ ) . '/../amp.php' );
if ( ! preg_match( '/\*\s*Version:\s*(?P<version>\d+\.\d+(?:.\d+)?(-\w+)?)/', $plugin_file, $matches ) ) {
	echo "Could not find version in readme metadata\n";
	exit( 1 );
}
$versions['amp.php#metadata'] = $matches['version'];

if ( ! preg_match( '/define\( \'AMP__VERSION\', \'(?P<version>[^\\\']+)\'/', $plugin_file, $matches ) ) {
	echo "Could not find version in AMP__VERSION constant\n";
	exit( 1 );
}
$versions['AMP__VERSION'] = $matches['version'];

fwrite( STDERR, "Version references:\n" );

echo json_encode( $versions, JSON_PRETTY_PRINT ) . "\n";

if ( 1 !== count( array_unique( $versions ) ) ) {
	fwrite( STDERR, "Error: Not all version references have been updated.\n" );
	exit( 1 );
}

if ( false === strpos( $versions['amp.php#metadata'], '-' ) && ! preg_match( '/^\d+\.\d+\.\d+$/', $versions['amp.php#metadata'] ) ) {
	fwrite( STDERR, sprintf( "Error: Release version (%s) lacks patch number. For new point releases, supply patch number of 0, such as 0.9.0 instead of 0.9.\n", $versions['amp.php#metadata'] ) );
	exit( 1 );
}
