## Function `amp_deactivate`

```php
function amp_deactivate( $network_wide = false );
```

Handle deactivation of plugin.

### Arguments

* `bool $network_wide` - Whether the activation was done network-wide.

### Source

:link: [includes/amp-helper-functions.php:39](../../includes/amp-helper-functions.php#L39-L51)

<details>
<summary>Show Code</summary>

```php
function amp_deactivate( $network_wide = false ) {
	AmpWpPluginFactory::create()->deactivate( $network_wide );
	// We need to manually remove the amp endpoint.
	global $wp_rewrite;
	foreach ( $wp_rewrite->endpoints as $index => $endpoint ) {
		if ( amp_get_slug() === $endpoint[1] ) {
			unset( $wp_rewrite->endpoints[ $index ] );
			break;
		}
	}

	flush_rewrite_rules( false );
}
```

</details>
