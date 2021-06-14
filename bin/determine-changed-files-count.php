<?php
/**
 * Determine the number of files that have changed based on the given path pattern.
 *
 * Usage:
 * php -f determine-changed-files-count.php <PCRE pattern> <file paths delimited by newlines> [--invert]
 *
 * For example:
 * php -f determine-changed-files-count.php "foo\/bar|bar*" "foo/bar/baz\nquux" --invert
 *
 * Would output: 1
 *
 * @codeCoverageIgnore
 * @package AMP
 */

$file_pattern = sprintf( '/^%s$/m', $argv[1] );
$changed_files  = explode( "\n", rtrim( $argv[2] ) );
$preg_grep_flags = isset( $argv[3] ) && trim( $argv[3] ) === '--invert' ? PREG_GREP_INVERT : 0;

var_dump($file_pattern, $changed_files, $preg_grep_flags);

$filtered_files = preg_grep( $file_pattern, $changed_files, $preg_grep_flags );

echo $filtered_files ? count( $filtered_files ) : 0;
