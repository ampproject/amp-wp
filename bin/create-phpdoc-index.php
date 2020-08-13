<?php
/**
 * This script outputs an index of the public-facing PHP API from the src and includes directories.
 *
 * @codeCoverageIgnore
 * @package AMP
 */

if ( ! defined( 'WP_CLI' ) ) {
	fwrite( STDERR, sprintf( 'Invoke via WP-CLI: wp eval-file bin/%s', basename( __FILE__ ) ) );
	exit( 1 );
}
if ( ! function_exists( 'WP_Parser\get_wp_files' ) || ! function_exists( 'WP_Parser\parse_files' ) ) {
	WP_CLI::error( 'Install and activate the phpdoc-parser plugin.' );
}

error_reporting( E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED ); // phpcs:ignore -- Because of some phpdoc internal errors.

chdir( __DIR__ . '/..' );
$files = [];
if ( empty( $files ) ) {
	$directories = [
		'src/',
		'includes/',
		'templates/',
		'back-compat/',
	];

	$files = [];
	foreach ( $directories as $directory ) {
		$files = array_merge( $files, WP_Parser\get_wp_files( $directory ) );
	}
}

$parsed = WP_Parser\parse_files( $files, __DIR__ );

/**
 * Is parsed entity internal?
 *
 * @param array $parsed Parsed entity.
 * @return bool Whether internal (or deprecated)
 * @throws Exception If lacking tags.
 */
function is_internal_doc( $parsed ) {
	if ( isset( $parsed['doc']['description'] ) && preg_match( '/This (filter|action) is documented in/', $parsed['doc']['description'] ) ) {
		return true;
	}

	if ( empty( $parsed['doc']['tags'] ) ) {
		throw new Exception( "Missing tags for {$parsed['name']}." );
	}

	foreach ( $parsed['doc']['tags'] as $tag ) {
		if ( 'internal' === $tag['name'] ) {
			return true;
		} elseif ( 'deprecated' === $tag['name'] ) {
			return true;
		}
	}

	return false;
}

/**
 * Process function.
 *
 * @param array        $parsed_function Parsed function.
 * @param string|null  $class           Class name the function belongs to. Defaults to null.
 * @return array Entities.
 */
function process_function( $parsed_function, $class = null ) {
	$entries = [];

	if ( ! $class && ! is_internal_doc( $parsed_function ) ) {
		$name = '';
		if ( 'global' !== $parsed_function['namespace'] ) {
			$name .= $parsed_function['namespace'] . '\\';
		}
		if ( $class ) {
			$name .= "{$class}::";
		}
		$name .= $parsed_function['name'] . '()';

		$entries[] = [
			'type' => $class ? 'method' : 'function',
			'name' => $name,
		];
	}

	if ( isset( $parsed_function['hooks'] ) ) {
		foreach ( $parsed_function['hooks'] as $parsed_hook ) {
			$entries = array_merge( $entries, process_hook( $parsed_hook ) );
		}
	}

	return $entries;
}

/**
 * Process hook.
 *
 * @param array $parsed_hook Parsed hook.
 * @return array Entities.
 */
function process_hook( $parsed_hook ) {
	if ( is_internal_doc( $parsed_hook ) ) {
		return [];
	}
	return [
		wp_array_slice_assoc( $parsed_hook, [ 'name', 'type' ] ),
	];
}

/**
 * Process class.
 *
 * @param array $parsed_class Parsed class.
 * @return array Entities.
 */
function process_class( $parsed_class ) {
	$entries  = [];
	$internal = is_internal_doc( $parsed_class );
	if ( ! $internal ) {
		$name = '';
		if ( 'global' !== $parsed_class['namespace'] ) {
			$name .= $parsed_class['namespace'] . '\\';
		}
		$name .= $parsed_class['name'];

		$entries[] = [
			'type' => 'class',
			'name' => $name,
		];
	}
	if ( isset( $parsed_class['methods'] ) ) {
		foreach ( $parsed_class['methods'] as $parsed_method ) {
			$entries = array_merge(
				$entries,
				process_function(
					$parsed_method,
					$parsed_class['name']
				)
			);
		}
	}
	return $entries;
}

$entries = [];
foreach ( $parsed as $parsed_file ) {
	if ( isset( $parsed_file['classes'] ) ) {
		foreach ( $parsed_file['classes'] as $parsed_class ) {
			$entries = array_merge( $entries, process_class( $parsed_class ) );
		}
	}
	if ( isset( $parsed_file['functions'] ) ) {
		foreach ( $parsed_file['functions'] as $parsed_function ) {
			$entries = array_merge( $entries, process_function( $parsed_function, null ) );
		}
	}
	if ( isset( $parsed_file['hooks'] ) ) {
		foreach ( $parsed_file['hooks'] as $parsed_hook ) {
			$entries = array_merge( $entries, process_hook( $parsed_hook, null ) );
		}
	}
}

foreach ( $entries as $entry ) {
	echo "{$entry['type']}\t{$entry['name']}\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
