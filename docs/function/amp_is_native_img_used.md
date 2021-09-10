## Function `amp_is_native_img_used`

```php
function amp_is_native_img_used();
```

Determine whether native `img` should be used instead of converting to `amp-img`.

### Return value

`bool` - Whether to use `img`.

### Source

:link: [includes/amp-helper-functions.php:1398](/includes/amp-helper-functions.php#L1398-L1411)

<details>
<summary>Show Code</summary>

```php
function amp_is_native_img_used() {
	/**
	 * Filters whether to use the native `img` element rather than convert to `amp-img`.
	 *
	 * This filter is a feature flag to opt-in to discontinue using `amp-img` (and `amp-anim`) which will be deprecated
	 * in AMP in the near future. Once this lands in AMP, this filter will switch to defaulting to true instead of false.
	 *
	 * @since 2.2
	 * @link https://github.com/ampproject/amphtml/issues/30442
	 *
	 * @param bool $use_native Whether to use `img`.
	 */
	return (bool) apply_filters( 'amp_native_img_used', false );
}
```

</details>
