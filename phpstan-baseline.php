<?php
/**
 * PHPStan Baseline File.
 * All static analysis errors included in this file will be ignored by PHPStan.
 *
 * NOTE: `phpstan analyse —generate-baseline phpstan-baseline.php` should not be used to rewrite this file.
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
 * Errors to be ignored in class-amp-allowed-tags-generated.php
 * Note: class-amp-allowed-tags-generated.php is a auto-generated file.
 */
$ignore_errors_in_class_amp_allowed_tags_generated = [
	[
		'message' => '#^Static property AMP_Allowed_Tags_Generated\\:\\:\\$minimum_validator_revision_required is never read, only written\\.$#',
		'count'   => 1,
		'path'    => __DIR__ . '/includes/sanitizers/class-amp-allowed-tags-generated.php',
	],
	[
		'message' => '#^Static property AMP_Allowed_Tags_Generated\\:\\:\\$spec_file_revision is never read, only written\\.$#',
		'count'   => 1,
		'path'    => __DIR__ . '/includes/sanitizers/class-amp-allowed-tags-generated.php',
	],
];

/**
 * Errors due to `Instanceof always evaluate to false`` in PHPStan.
 *
 * @see https://github.com/phpstan/phpstan/issues/3632
 *
 * @todo Remove once this is fixed in PHPStan.
 */
$ignore_errors_due_to_instanceof_always_evaluating_to_false = [
	[
		'message' => '#^Instanceof between mixed and Error will always evaluate to false\\.$#',
		'count'   => 1,
		'path'    => __DIR__ . '/src/DevTools/ErrorPage.php',
	],
	[
		'message' => '#^Instanceof between mixed and Error will always evaluate to false\\.$#',
		'count'   => 1,
		'path'    => __DIR__ . '/src/DevTools/LikelyCulpritDetector.php',
	],
];

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
	[
		'message' => '#^Call to function method_exists\\(\\) with ReflectionType and \'isBuiltin\' will always evaluate to true\\.$#',
		'count'   => 1,
		'path'    => __DIR__ . '/src/Infrastructure/Injector/SimpleInjector.php',
	],
];

return [
	'parameters' => [
		'ignoreErrors' => array_merge(
			$ignore_errors_in_class_amp_allowed_tags_generated,
			$ignore_errors_due_to_instanceof_always_evaluating_to_false,
			$ignore_errors_due_to_function_and_method_exists_always_evaluating_to_true
		),
	],
];
