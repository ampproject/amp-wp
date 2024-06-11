<?php
/**
 * PHPStan Baseline File.
 * All static analysis errors included in this file will be ignored by PHPStan.
 *
 * NOTE: `phpstan analyse --generate-baseline phpstan-baseline.php` should not be used to rewrite this file.
 *
 * How to include new errors to be ignored:
 * 1. Run `phpstan analyse --generate-baseline temp-baseline.php` to generate a new temp baseline file.
 * 2. Copy the contents of the temp baseline file to this file.
 *
 * Please be sure to explain why the error should be ignored in the remark above the error.
 *
 * @codeCoverageIgnore
 *
 * @package AMP
 */

/**
 * Errors due `function_exists() and method_exists() always evaluating to true` in PHPStan.
 *
 * @see https://github.com/phpstan/phpstan/issues/8980
 * @see https://github.com/phpstan/phpstan/issues/8980#issuecomment-1451284041
 *
 * @todo Remove once this is fixed in PHPStan.
 */
$ignore_errors_due_to_function_and_method_exists_always_evaluating_to_true = [
	[
		'message' => '#^Call to function method_exists\\(\\) with WP_Query and \'is_favicon\' will always evaluate to true\\.$#',
		'count'   => 1,
		'path'    => __DIR__ . '/includes/amp-helper-functions.php',
	],
	[
		'message' => '#^Call to function function_exists\\(\\) with \'amp_activate\'\\|\'amp_backcompat_use…\'\\|\'amp_init_customizer\'\\|\'amp_load_classes\' will always evaluate to true\\.$#',
		'count'   => 1,
		'path'    => __DIR__ . '/includes/bootstrap.php',
	],
	[
		'message' => '#^Call to function function_exists\\(\\) with \'curl_multi_add…\'\\|\'curl_multi_exec\'\\|\'curl_multi_init\' will always evaluate to true\\.$#',
		'count'   => 1,
		'path'    => __DIR__ . '/src/Admin/SiteHealth.php',
	],
];

return [
	'parameters' => [
		'ignoreErrors' => array_merge(
			$ignore_errors_due_to_function_and_method_exists_always_evaluating_to_true
		),
	],
];
