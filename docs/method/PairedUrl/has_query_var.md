## Method `PairedUrl::has_query_var()`

```php
public function has_query_var( $url );
```

Determine whether the given URL has the query var.

### Arguments

* `string $url` - URL (or REQUEST_URI).

### Return value

`bool` - Has query var.

### Source

:link: [src/PairedUrl.php:69](/src/PairedUrl.php#L69-L79)

<details>
<summary>Show Code</summary>

```php
public function has_query_var( $url ) {
	$parsed_url = wp_parse_url( $url );
	if ( ! empty( $parsed_url['query'] ) ) {
		$query_vars = [];
		wp_parse_str( $parsed_url['query'], $query_vars );
		if ( isset( $query_vars[ amp_get_slug() ] ) ) {
			return true;
		}
	}
	return false;
}
```

</details>
