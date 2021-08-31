## Function `amp_is_native_post_form_allowed`

```php
function amp_is_native_post_form_allowed();
```

Determine whether to allow native `POST` forms without conversion to use the `action-xhr` attribute and use the amp-form component.

### Return value

`bool` - Whether to allow native `POST` forms.

### Source

:link: [includes/amp-helper-functions.php:1421](/includes/amp-helper-functions.php#L1421-L1431)

<details>
<summary>Show Code</summary>

```php
function amp_is_native_post_form_allowed() {
	/**
	 * Filters whether to allow native `POST` forms without conversion to use the `action-xhr` attribute and use the amp-form component.
	 *
	 * @since 2.2
	 * @link https://github.com/ampproject/amphtml/issues/27638
	 *
	 * @param bool $use_native Whether to allow native `POST` forms.
	 */
	return (bool) apply_filters( 'amp_native_post_form_allowed', false );
}
```

</details>
