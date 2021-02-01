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

:link: [includes/amp-helper-functions.php:1962](/includes/amp-helper-functions.php#L1962-L1964)

<details>
<summary>Show Code</summary>

```php
function amp_add_paired_endpoint( $url ) {
	return add_query_arg( amp_get_slug(), '1', $url );
}
```

</details>
