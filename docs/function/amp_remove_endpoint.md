## Function `amp_remove_endpoint`

> :warning: This function is deprecated: Use amp_remove_paired_endpoint() instead.

```php
function amp_remove_endpoint( $url );
```

Remove the AMP endpoint (and query var) from a given URL.

### Arguments

* `string $url` - URL.

### Return value

`string` - URL with AMP stripped.

### Source

:link: [includes/amp-helper-functions.php:638](/includes/amp-helper-functions.php#L638-L640)

<details>
<summary>Show Code</summary>

```php
function amp_remove_endpoint( $url ) {
	return amp_remove_paired_endpoint( $url );
}
```

</details>
