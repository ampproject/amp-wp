## Function `amp_register_default_scripts`

```php
function amp_register_default_scripts( $wp_scripts );
```

Register default scripts for AMP components.

### Arguments

* `\WP_Scripts $wp_scripts` - Scripts.

### Source

:link: [includes/amp-helper-functions.php:979](../../includes/amp-helper-functions.php#L979-L1035)

<details>
<summary>Show Code</summary>

```php
function amp_register_default_scripts( $wp_scripts ) {
	// AMP Runtime.
	$handle = 'amp-runtime';
	$wp_scripts->add(
		$handle,
		'https://cdn.ampproject.org/v0.js',
		[],
		null
	);
	$wp_scripts->add_data(
		$handle,
		'amp_script_attributes',
		[
			'async' => true,
		]
	);

	// Shadow AMP API.
	$handle = 'amp-shadow';
	$wp_scripts->add(
		$handle,
		'https://cdn.ampproject.org/shadow-v0.js',
		[],
		null
	);
	$wp_scripts->add_data(
		$handle,
		'amp_script_attributes',
		[
			'async' => true,
		]
	);

	// Register all AMP components as defined in the spec.
	foreach ( AMP_Allowed_Tags_Generated::get_extension_specs() as $extension_name => $extension_spec ) {
		$src = sprintf(
			'https://cdn.ampproject.org/v0/%s-%s.js',
			$extension_name,
			end( $extension_spec['version'] )
		);

		$wp_scripts->add(
			$extension_name,
			$src,
			[ 'amp-runtime' ],
			null
		);
	}

	if ( $wp_scripts->query( 'amp-experiment', 'registered' ) ) {
		/*
		 * Version 1.0 of amp-experiment is still experimental and requires the user to enable it.
		 * @todo Revisit once amp-experiment is no longer experimental.
		 */
		$wp_scripts->registered['amp-experiment']->src = 'https://cdn.ampproject.org/v0/amp-experiment-0.1.js';
	}
}
```

</details>
