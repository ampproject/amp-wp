## Function `amp_is_bento_enabled`

```php
function amp_is_bento_enabled();
```

Determine whether the use of Bento components is enabled.

When Bento is enabled, newer experimental versions of AMP components are used which incorporate the next generation of the component framework.

### Return value

`bool` - Whether Bento components are enabled.

### Source

:link: [includes/amp-helper-functions.php:887](/includes/amp-helper-functions.php#L887-L900)

<details>
<summary>Show Code</summary>

```php
function amp_is_bento_enabled() {
	/**
	 * Filters whether the use of Bento components is enabled.
	 *
	 * When Bento is enabled, newer experimental versions of AMP components are used which incorporate the next generation
	 * of the component framework.
	 *
	 * @since 2.2
	 * @link https://blog.amp.dev/2021/01/28/bento/
	 *
	 * @param bool $enabled Enabled.
	 */
	return apply_filters( 'amp_bento_enabled', false );
}
```

</details>
