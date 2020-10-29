## Function `amp_add_paired_endpoint`

```php
function amp_add_paired_endpoint( $url );
```

Turn a given URL into a paired AMP URL.

### Arguments

* `string $url` - URL.

### Return value

`string` - AMP URL.

### Source

:link: [includes/amp-helper-functions.php:1946](/includes/amp-helper-functions.php#L1946-L1948)

<details>
<summary>Show Code</summary>

```php
function amp_add_paired_endpoint( $url ) {
	return add_query_arg( amp_get_slug(), '1', $url );
}
```

</details>
