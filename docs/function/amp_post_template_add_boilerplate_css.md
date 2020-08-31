## Function `amp_post_template_add_boilerplate_css`

> :warning: This function is deprecated: Boilerplate is now automatically added via the ampproject/optimizer library.

```php
function amp_post_template_add_boilerplate_css();
```

Print boilerplate CSS.

### Source

:link: [includes/deprecated.php:218](../../includes/deprecated.php#L218-L221)

<details>
<summary>Show Code</summary>

```php
function amp_post_template_add_boilerplate_css() {
	_deprecated_function( __FUNCTION__, '1.5' );
	echo amp_get_boilerplate_code(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
```

</details>
