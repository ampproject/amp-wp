<?php
/**
 * Determine the number of modified files based on the given path pattern.
 *
 * Usage:
 * php -f determine-modified-files-count.php <file path pattern> <file paths delimited by newlines> [--invert]
 *
 * For example:
 * php -f determine-modified-files-count.php "foo\/bar|bar*" "foo/bar/baz\nquux" --invert
 *
 * Would output: 1
 *
 * @codeCoverageIgnore
 * @package AMP
 */

$file_pattern    = sprintf( '/^%s$/m', $argv[1] );
$modified_files  = explode( "\n", trim( $argv[2] ) );
$preg_grep_flags = isset( $argv[3] ) && trim( $argv[3] ) === '--invert' ? PREG_GREP_INVERT : 0;

$filtered_files = preg_grep( $file_pattern, $modified_files, $preg_grep_flags );

echo $filtered_files ? count( $filtered_files ) : 0;
