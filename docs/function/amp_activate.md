## Function `amp_activate`

```php
function amp_activate( $network_wide = false );
```

Handle activation of plugin.

### Arguments

* `bool $network_wide` - Whether the activation was done network-wide.

### Source

:link: [includes/amp-helper-functions.php:22](../../includes/amp-helper-functions.php#L22-L29)

<details>
<summary>Show Code</summary>

```php
function amp_activate( $network_wide = false ) {
	AmpWpPluginFactory::create()->activate( $network_wide );
	amp_after_setup_theme();
	if ( ! did_action( 'amp_init' ) ) {
		amp_init();
	}
	flush_rewrite_rules();
}
```

</details>
