## Function `amp_bootstrap_plugin`

```php
function amp_bootstrap_plugin();
```

Bootstrap plugin.

### Source

:link: [includes/amp-helper-functions.php:59](../../includes/amp-helper-functions.php#L59-L95)

<details>
<summary>Show Code</summary>

```php
function amp_bootstrap_plugin() {
	/**
	 * Filters whether AMP is enabled on the current site.
	 *
	 * Useful if the plugin is network activated and you want to turn it off on select sites.
	 *
	 * @since 0.2
	 * @since 2.0 Filter now runs earlier at plugins_loaded (with earliest priority) rather than at the after_setup_theme action.
	 */
	if ( false === apply_filters( 'amp_is_enabled', true ) ) {
		return;
	}

	AmpWpPluginFactory::create()->register();

	// The amp_bootstrap_plugin() function is called at the plugins_loaded action with the earliest priority. This is
	// the earliest we can run this since that is when pluggable.php has been required and wp_hash() is available.
	AMP_Validation_Manager::init_validate_request();

	/*
	 * Register AMP scripts regardless of whether AMP is enabled or it is the AMP endpoint
	 * for the sake of being able to use AMP components on non-AMP documents ("dirty AMP").
	 */
	add_action( 'wp_default_scripts', 'amp_register_default_scripts' );

	add_action( 'wp_default_styles', 'amp_register_default_styles' );

	// Ensure async and custom-element/custom-template attributes are present on script tags.
	add_filter( 'script_loader_tag', 'amp_filter_script_loader_tag', PHP_INT_MAX, 2 );

	// Ensure crossorigin=anonymous is added to font links.
	add_filter( 'style_loader_tag', 'amp_filter_font_style_loader_tag_with_crossorigin_anonymous', 10, 4 );

	add_action( 'after_setup_theme', 'amp_after_setup_theme', 5 );

	add_action( 'plugins_loaded', '_amp_bootstrap_customizer', 9 ); // Should be hooked before priority 10 on 'plugins_loaded' to properly unhook core panels.
}
```

</details>
