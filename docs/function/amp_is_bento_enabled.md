## Function `amp_is_bento_enabled`

> :warning: This function is deprecated: 2.5.0

```php
function amp_is_bento_enabled();
```

Determine whether the use of Bento components is enabled.

When Bento is enabled, newer experimental versions of AMP components are used which incorporate the next generation of the component framework.

### Return value

`bool` - Whether Bento components are enabled.

### Source

:link: [includes/deprecated.php:368](/includes/deprecated.php#L368-L385)

<details>
<summary>Show Code</summary>

```php
function amp_is_bento_enabled() {
	_deprecated_function( __FUNCTION__, 'AMP 2.5.0' );

	/**
	 * Filters whether the use of Bento components is enabled.
	 *
	 * When Bento is enabled, newer experimental versions of AMP components are used which incorporate the next generation
	 * of the component framework.
	 *
	 * @since 2.2
	 * @link https://blog.amp.dev/2021/01/28/bento/
	 *
	 * @deprecated 2.5.0 Bento support has been removed.
	 *
	 * @param bool $enabled Enabled.
	 */
	return apply_filters_deprecated( 'amp_bento_enabled', [ false ], 'AMP 2.5.0', 'Remove bento support', 'Bento support has been removed.' );
}
```

</details>
