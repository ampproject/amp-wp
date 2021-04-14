<?php
/**
 * Central bootstrapping entry point for all non-autoloaded files.
 *
 * This file is mainly used for taking direct control of included files off from Composer's
 * "files" directive, as that one can easily include the files multiple times, leading to
 * redeclaration fatal errors.
 *
 * @package AmpProject/AmpWP
 */

$files_to_include = [
	__DIR__ . '/../back-compat/back-compat.php'       => 'amp_backcompat_use_v03_templates',
	__DIR__ . '/../includes/amp-helper-functions.php' => 'amp_activate',
	__DIR__ . '/../includes/admin/functions.php'      => 'amp_init_customizer',
	__DIR__ . '/../includes/deprecated.php'           => 'amp_load_classes',
];

foreach ( $files_to_include as $file_to_include => $function_to_check ) {
	if ( ! function_exists( $function_to_check ) ) {
		include $file_to_include;
	}
}
