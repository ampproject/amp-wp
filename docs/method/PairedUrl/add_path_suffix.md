## Method `PairedUrl::add_path_suffix()`

```php
public function add_path_suffix( $url );
```

Get paired AMP URL using a endpoint suffix.

### Arguments

* `string $url` - URL (or REQUEST_URI).

### Return value

`string` - AMP URL.

### Source

:link: [src/PairedUrl.php:100](/src/PairedUrl.php#L100-L145)

<details>
<summary>Show Code</summary>

```php
public function add_path_suffix( $url ) {
	$url = $this->remove_path_suffix( $url );
	$parsed_url = wp_parse_url( $url );
	if ( false === $parsed_url ) {
		$parsed_url = [];
	}
	$parsed_url = array_merge(
		wp_parse_url( home_url( '/' ) ),
		$parsed_url
	);
	if ( empty( $parsed_url['scheme'] ) ) {
		$parsed_url['scheme'] = is_ssl() ? 'https' : 'http';
	}
	if ( empty( $parsed_url['host'] ) ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$parsed_url['host'] = ! empty( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : 'localhost';
	}
	$parsed_url['path']  = trailingslashit( $parsed_url['path'] );
	$parsed_url['path'] .= user_trailingslashit( amp_get_slug(), 'amp' );
	$amp_url = $parsed_url['scheme'] . '://';
	if ( ! empty( $parsed_url['user'] ) ) {
		$amp_url .= $parsed_url['user'];
		if ( ! empty( $parsed_url['pass'] ) ) {
			$amp_url .= ':' . $parsed_url['pass'];
		}
		$amp_url .= '@';
	}
	$amp_url .= $parsed_url['host'];
	if ( ! empty( $parsed_url['port'] ) ) {
		$amp_url .= ':' . $parsed_url['port'];
	}
	$amp_url .= $parsed_url['path'];
	if ( ! empty( $parsed_url['query'] ) ) {
		$amp_url .= '?' . $parsed_url['query'];
	}
	if ( ! empty( $parsed_url['fragment'] ) ) {
		$amp_url .= '#' . $parsed_url['fragment'];
	}
	return $amp_url;
}
```

</details>
