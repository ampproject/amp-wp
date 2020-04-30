<?php

namespace AmpProject\AmpWP;

use WP_CLI;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RecursiveRegexIterator;
use SplFileInfo;

if ( ! defined( 'WP_CLI' ) || 'cli' !== php_sapi_name() ) {
	echo "Error: Must invoke via WP-CLI.\n";
	exit( 1 );
}

const THEMES = [
	'twentytwenty',
// 'twentynineteen',
// 'twentyseventeen',
];

/**
 * Process theme.
 *
 * @param string $theme Theme.
 * @throws WP_CLI\ExitException When error happens.
 */
function process_theme( $theme ) {
	WP_CLI::line( $theme );
	$theme_obj = wp_get_theme( $theme );
	WP_CLI::line( $theme_obj->get_template_directory() );

	$theme_dir = constant( 'AMP__DIR__' ) . '/themes/' . $theme;
	if ( ! file_exists( $theme_dir ) ) {
		mkdir( $theme_dir );
	}

	// Copy the files from core.
	exec( // phpcs:ignore
		sprintf(
			'rsync -avz --delete %s %s',
			escapeshellarg( trailingslashit( $theme_obj->get_stylesheet_directory() ) ),
			escapeshellarg( trailingslashit( $theme_dir ) )
		),
		$output,
		$return
	);
	WP_CLI::line( implode( "\n", $output ) );
	if ( 0 !== $return ) {
		WP_CLI::error( "Error code: $return" );
	}

	// For each PHP file now inject an AMP namespace.
	$directory = new RecursiveDirectoryIterator( $theme_dir );
	$iterator  = new RecursiveIteratorIterator( $directory );
	$regex     = new RegexIterator( $iterator, '/\.php$/' );

	/**
	 * File.
	 *
	 * @var SplFileInfo $file
	 */
	foreach ( $regex as $file ) {
		try {
			process_file( $file );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( 'Failed to process file: %s. Error: %s', $file->getPathname(), $e->getMessage() ) );
		}
	}
}

/**
 * Process file.
 *
 * @param SplFileInfo $file File.
 * @throws Exception When error happens.
 */
function process_file( SplFileInfo $file ) {
	WP_CLI::line( $file->getPathname() );

	$contents = file_get_contents( $file->getPathname() );
	$tokens   = token_get_all( $contents );

	assert_token( $tokens, 0, T_OPEN_TAG );
	assert_token( $tokens, 1, T_DOC_COMMENT );
	assert_token( $tokens, 2, T_WHITESPACE );

	array_splice( $tokens, 3, 0, "namespace AmpProject\\AmpWP\\Themes;\n\n" );

	$used_classes = [];

	for ( $i = 4, $len = count( $tokens ); $i < $len; $i++ ) {
		if ( ! is_array( $tokens[ $i ] ) ) {
			continue;
		}

		switch ( $tokens[ $i ][0] ) {
			case T_STRING:
				if ( preg_match( '/^(WP|Walker)_/', $tokens[ $i ][1] ) ) {
					$used_classes[] = $tokens[ $i ][1];
				}
				break;
			case T_DOC_COMMENT:
				if ( preg_match_all( '/@(param|type|var)\s+(WP_\w+)/', $tokens[ $i ][1], $matches, PREG_SET_ORDER ) ) {
					foreach ( $matches as $match ) {
						$used_classes[] = $match[2];
					}
				}
				break;
			// @todo Rename get_sidebar().
			// @todo Rename get_header().
			// @todo Rename get_footer().
			// @todo Rename get_search_form().
			// @todo Rename get_template_part().
			// @todo Rename comments_template().
			// @todo Rename dynamic_sidebar()?
		}
	}

	// Add use statements.
	if ( ! empty( $used_classes ) ) {
		$used_classes = array_unique( $used_classes );
		sort( $used_classes );
		array_splice( $tokens, 4, 0, "\n" );
		foreach ( array_reverse( $used_classes ) as $class ) {
			array_splice( $tokens, 4, 0, "use $class;\n" );
		}
	}

	file_put_contents(
		$file->getPathname(),
		implode(
			'',
			array_map(
				function ( $token ) {
					if ( is_array( $token ) ) {
						return $token[1];
					} else {
						return $token;
					}
				},
				$tokens
			)
		)
	);
}

/**
 * Expect token.
 *
 * @param array $tokens   Tokens.
 * @param int   $position Position.
 * @param int   $token    Token constant.
 *
 * @throws Exception When unexpected token present.
 */
function assert_token( &$tokens, $position, $token ) {
	if ( ! isset( $tokens[ $position ] ) || ! is_array( $tokens[ $position ] ) || $token !== $tokens[ $position ][0] ) {
		throw new Exception( 'Expected token: ' . token_name( $token ) );
	}
}

foreach ( THEMES as $theme ) {
	process_theme( $theme );
}
