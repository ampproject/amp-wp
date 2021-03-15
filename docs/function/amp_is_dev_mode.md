## Function `amp_is_dev_mode`

```php
function amp_is_dev_mode();
```

Determine whether AMP dev mode is enabled.

When enabled, the `<html>` element will get the data-ampdevmode attribute and the plugin will add the same attribute to elements associated with the admin bar and other elements that are provided by the `amp_dev_mode_element_xpaths` filter.

### Return value

`bool` - Whether AMP dev mode is enabled.

### Source

:link: [includes/amp-helper-functions.php:1279](/includes/amp-helper-functions.php#L1279-L1302)

<details>
<summary>Show Code</summary>

```php
function amp_is_dev_mode() {

	/**
	 * Filters whether AMP mode is enabled.
	 *
	 * When enabled, the data-ampdevmode attribute will be added to the document element and it will allow the
	 * attributes to be added to the admin bar. It will also add the attribute to all elements which match the
	 * queries for the expressions returned by the 'amp_dev_mode_element_xpaths' filter.
	 *
	 * @since 1.3
	 * @param bool $is_dev_mode_enabled Whether AMP dev mode is enabled.
	 */
	return apply_filters(
		'amp_dev_mode_enabled',
		(
			// For the few sites that forcibly show the admin bar even when the user is logged out, only enable dev
			// mode if the user is actually logged in. This prevents the dev mode from being served to crawlers
			// when they index the AMP version. The theme support check disables dev mode in Reader mode.
			( is_admin_bar_showing() && is_user_logged_in() )
			||
			is_customize_preview()
		)
	);
}
```

</details>
