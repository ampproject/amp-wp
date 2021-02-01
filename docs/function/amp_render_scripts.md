## Function `amp_render_scripts`

```php
function amp_render_scripts( $scripts );
```

Generate HTML for AMP scripts that have not yet been printed.

This is adapted from `wp_scripts()-&gt;do_items()`, but it runs only the bare minimum required to output the missing scripts, without allowing other filters to apply which may cause an invalid AMP response. The HTML for the scripts is returned instead of being printed.

### Arguments

* `array $scripts` - Script handles mapped to URLs or true.

### Return value

`string` - HTML for scripts tags that have not yet been done.

### Source

:link: [includes/amp-helper-functions.php:1071](../../includes/amp-helper-functions.php#L1071-L1101)

<details>
<summary>Show Code</summary>

```php
function amp_render_scripts( $scripts ) {
	$script_tags = '';

	/*
	 * Make sure the src is up to date. This allows for embed handlers to override the
	 * default extension version by defining a different URL.
	 */
	foreach ( $scripts as $handle => $src ) {
		if ( is_string( $src ) && wp_script_is( $handle, 'registered' ) ) {
			wp_scripts()->registered[ $handle ]->src = $src;
		}
	}

	foreach ( array_diff( array_keys( $scripts ), wp_scripts()->done ) as $handle ) {
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			continue;
		}

		$script_dep   = wp_scripts()->registered[ $handle ];
		$script_tags .= amp_filter_script_loader_tag(
			sprintf(
				"<script type='text/javascript' src='%s'></script>\n", // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				esc_url( $script_dep->src )
			),
			$handle
		);

		wp_scripts()->done[] = $handle;
	}
	return $script_tags;
}
```

</details>
