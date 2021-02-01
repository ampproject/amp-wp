## Function `amp_add_frontend_actions`

```php
function amp_add_frontend_actions();
```

Add frontend actions.

### Source

:link: [includes/amp-helper-functions.php:392](../../includes/amp-helper-functions.php#L392-L394)

<details>
<summary>Show Code</summary>

```php
function amp_add_frontend_actions() {
	add_action( 'wp_head', 'amp_add_amphtml_link' );
}
```

</details>
