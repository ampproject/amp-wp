## Function `amp_get_asset_url`

```php
function amp_get_asset_url( $file );
```

Get AMP asset URL.

### Arguments

* `string $file` - Relative path to file in assets directory.

### Return value

`string` - URL.

### Source

:link: [includes/amp-helper-functions.php:916](../../includes/amp-helper-functions.php#L916-L918)

<details>
<summary>Show Code</summary>

```php
function amp_get_asset_url( $file ) {
	return plugins_url( sprintf( 'assets/%s', $file ), AMP__FILE__ );
}
```

</details>
