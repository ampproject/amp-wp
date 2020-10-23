## Function `amp_after_setup_theme`

```php
function amp_after_setup_theme();
```

Set up AMP.

This function must be invoked through the &#039;after_setup_theme&#039; action to allow the AMP setting to declare the post types support earlier than plugins/theme.

### Source

:link: [includes/amp-helper-functions.php:237](../../includes/amp-helper-functions.php#L237-L257)

<details>
<summary>Show Code</summary>

```php
function amp_after_setup_theme() {
	amp_get_slug(); // Ensure AMP_QUERY_VAR is set.

	/** This filter is documented in includes/amp-helper-functions.php */
	if ( false === apply_filters( 'amp_is_enabled', true ) ) {
		_doing_it_wrong(
			'add_filter',
			esc_html(
				sprintf(
					/* translators: 1: amp_is_enabled filter name, 2: plugins_loaded action */
					__( 'Filter for "%1$s" added too late. To disable AMP, this filter must be added before the "%2$s" action.', 'amp' ),
					'amp_is_enabled',
					'plugins_loaded'
				)
			),
			'2.0'
		);
	}

	add_action( 'init', 'amp_init', 0 ); // Must be 0 because widgets_init happens at init priority 1.
}
```

</details>
