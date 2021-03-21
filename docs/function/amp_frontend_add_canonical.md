## Function `amp_frontend_add_canonical`

> :warning: This function is deprecated: Use amp_add_amphtml_link() instead.

```php
function amp_frontend_add_canonical();
```

Add amphtml link to frontend.

### Source

:link: [includes/amp-frontend-actions.php:31](/includes/amp-frontend-actions.php#L31-L34)

<details>
<summary>Show Code</summary>

```php
function amp_frontend_add_canonical() {
	_deprecated_function( __FUNCTION__, '1.0', 'amp_add_amphtml_link' );
	amp_add_amphtml_link();
}
```

</details>
