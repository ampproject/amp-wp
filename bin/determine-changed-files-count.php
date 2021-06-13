<?php
/**
 * Determine the number of files that have changed based on the given ignore path pattern.
 *
 * Usage:
 * php -f <file name> <list of PCRE patterns> <list of files>
 *
 * For example:
 * php -f determine-changed-files-count.php "foo\/bar\nbar*" "foo/bar/baz\nquux"
 *
 * Would output: 1
 */

$ignore_pattern = str_replace( "\n", '|', rtrim($argv[1] ) );
$changed_files   = explode( "\n", rtrim( $argv[2] ) );

$filtered_files = preg_grep( "/^${ignore_pattern}$/m", $changed_files, PREG_GREP_INVERT );

echo $filtered_files ? count( $filtered_files ) : 0;
