## Method `PairedUrl::has_path_suffix()`

```php
public function has_path_suffix( $url );
```

Determine whether the given URL has the endpoint suffix.

### Arguments

* `string $url` - URL (or REQUEST_URI).

### Return value

`bool` - Has endpoint suffix.

### Source

:link: [src/PairedUrl.php:36](/src/PairedUrl.php#L36-L44)

<details>
<summary>Show Code</summary>

```php
public function has_path_suffix( $url ) {
	$path    = wp_parse_url( $url, PHP_URL_PATH );
	$pattern = sprintf(
		':/%s/?$:',
		preg_quote( amp_get_slug(), ':' )
	);
	return (bool) preg_match( $pattern, $path );
}
```

</details>
