#!/usr/bin/env php
<?php
/**
 * Rewrite README.md into WordPress's readme.txt
 *
 * @codeCoverageIgnore
 * @package AMP
 */

if ( 'cli' !== php_sapi_name() ) {
	fwrite( STDERR, "Must run from CLI.\n" );
	exit( __LINE__ );
}

$readme_md = file_get_contents( __DIR__ . '/../README.md' );

$readme_txt = $readme_md;

// Transform the sections above the description.
$readme_txt = preg_replace_callback(
	'/^.+?(?=## Description)/s',
	static function ( $matches ) {
		// Delete lines with images.
		$input = trim( preg_replace( '/\[?!\[.+/', '', $matches[0] ) );

		$parts = preg_split( '/\n\n+/', $input );

		if ( 3 !== count( $parts ) ) {
			fwrite( STDERR, "Too many sections in header found.\n" );
			exit( __LINE__ );
		}

		// Replace verbose name on GitHub with short name for plugin directory.
		$header = '# AMP';

		$description = $parts[1];
		if ( strlen( $description ) > 150 ) {
			fwrite( STDERR, "The short description is too long: $description\n" );
			exit( __LINE__ );
		}

		$metadata = [];
		foreach ( explode( "\n", $parts[2] ) as $meta ) {
			$meta = trim( $meta );
			if ( ! preg_match( '/^\*\*(?P<key>.+?):\*\* (?P<value>.+)/', $meta, $matches ) ) {
				fwrite( STDERR, "Parse error on for meta line: $meta.\n" );
				exit( __LINE__ );
			}

			$unlinked_value = preg_replace( '/\[(.+?)]\(.+?\)/', '$1', $matches['value'] );

			$metadata[ $matches['key'] ] = $unlinked_value;

			// Extract License URI from link.
			if ( 'License' === $matches['key'] ) {
				$license_uri = preg_replace( '/\[.+?]\((.+?)\)/', '$1', $matches['value'] );

				if ( 0 !== strpos( $license_uri, 'http' ) ) {
					fwrite( STDERR, "Unable to extract License URI from: $meta.\n" );
					exit( __LINE__ );
				}

				$metadata['License URI'] = $license_uri;
			}
		}

		$expected_metadata = [
			'Contributors',
			'Tags',
			'Requires at least',
			'Tested up to',
			'Stable tag',
			'License',
			'License URI',
			'Requires PHP',
		];
		foreach ( $expected_metadata as $key ) {
			if ( empty( $metadata[ $key ] ) ) {
				fwrite( STDERR, "Failed to parse metadata. Missign: $key\n" );
				exit( __LINE__ );
			}
		}

		$replaced = "$header\n";
		foreach ( $metadata as $key => $value ) {
			$replaced .= "$key: $value\n";
		}
		$replaced .= "\n$description\n\n";

		return $replaced;
	},
	$readme_txt
);

// Replace image-linked YouTube videos with bare URLs.
$readme_txt = preg_replace(
	'#\[!\[.+?]\(.+?\)]\((https://www\.youtube\.com/.+?)\)#',
	'$1',
	$readme_txt
);

// Fix up the screenshots.
$screenshots_captioned = 0;
$readme_txt            = preg_replace_callback(
	'/(?<=## Screenshots\n\n)(.+?)(?=## Changelog)/s',
	static function ( $matches ) use ( &$screenshots_captioned ) {
		if ( ! preg_match_all( '/### (.+)/', $matches[0], $screenshot_matches ) ) {
			fwrite( STDERR, "Unable to parse screenshot headings.\n" );
			exit( __LINE__ );
		}

		$screenshot_txt = '';
		foreach ( $screenshot_matches[1] as $i => $screenshot_caption ) {
			$screenshot_txt .= sprintf( "%d. %s\n", $i + 1, $screenshot_caption );
			$screenshots_captioned++;
		}
		$screenshot_txt .= "\n";

		return $screenshot_txt;
	},
	$readme_txt,
	1,
	$replace_count
);
if ( 0 === $replace_count ) {
	fwrite( STDERR, "Unable to transform screenshots.\n" );
	exit( __LINE__ );
}

$screenshot_files = glob( __DIR__ . '/../.wordpress-org/screenshot-*' );
if ( count( $screenshot_files ) !== $screenshots_captioned ) {
	fwrite( STDERR, "Number of screenshot files does not match number of screenshot captions.\n" );
	exit( __LINE__ );
}
foreach ( $screenshot_files as $i => $screenshot_file ) {
	if ( 0 !== strpos( basename( $screenshot_file ), sprintf( 'screenshot-%d.', $i + 1 ) ) ) {
		fwrite( STDERR, "Screenshot filename is not sequential: $screenshot_file.\n" );
		exit( __LINE__ );
	}
}

// Convert markdown headings into WP readme headings for good measure.
$readme_txt = preg_replace_callback(
	'/^(#+)\s(.+)/m',
	static function ( $matches ) {
		$md_heading_level = strlen( $matches[1] );
		$heading_text     = $matches[2];

		// #: ===
		// ##: ==
		// ###: =
		$txt_heading_level = 4 - $md_heading_level;
		if ( $txt_heading_level <= 0 ) {
			fwrite( STDERR, "Heading too small to transform: {$matches[0]}.\n" );
			exit( __LINE__ );
		}

		return sprintf(
			'%1$s %2$s %1$s',
			str_repeat( '=', $txt_heading_level ),
			$heading_text
		);
	},
	$readme_txt,
	-1,
	$replace_count
);
if ( 0 === $replace_count ) {
	fwrite( STDERR, "Unable to transform headings.\n" );
	exit( __LINE__ );
}

if ( ! file_put_contents( __DIR__ . '/../readme.txt', $readme_txt ) ) {
	fwrite( STDERR, "Failed to write readme.txt.\n" );
	exit( __LINE__ );
}

fwrite( STDOUT, "Validated README.md and generated readme.txt\n" );
