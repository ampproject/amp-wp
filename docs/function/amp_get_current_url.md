## Function `amp_get_current_url`

```php
function amp_get_current_url();
```

Get the URL for the current request.

This is essentially the REQUEST_URI prefixed by the scheme and host for the home URL. This is needed in particular due to subdirectory installs.

### Return value

`string` - Current URL.

### Source

:link: [includes/amp-helper-functions.php:620](../../includes/amp-helper-functions.php#L620-L652)

<details>
<summary>Show Code</summary>

```php
function amp_get_current_url() {
	$parsed_url = wp_parse_url( home_url() );

	if ( ! is_array( $parsed_url ) ) {
		$parsed_url = [];
	}

	if ( empty( $parsed_url['scheme'] ) ) {
		$parsed_url['scheme'] = is_ssl() ? 'https' : 'http';
	}
	if ( ! isset( $parsed_url['host'] ) ) {
		$parsed_url['host'] = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : 'localhost';
	}

	$current_url = $parsed_url['scheme'] . '://';
	if ( isset( $parsed_url['user'] ) ) {
		$current_url .= $parsed_url['user'];
		if ( isset( $parsed_url['pass'] ) ) {
			$current_url .= ':' . $parsed_url['pass'];
		}
		$current_url .= '@';
	}
	$current_url .= $parsed_url['host'];
	if ( isset( $parsed_url['port'] ) ) {
		$current_url .= ':' . $parsed_url['port'];
	}
	$current_url .= '/';

	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$current_url .= ltrim( wp_unslash( $_SERVER['REQUEST_URI'] ), '/' );
	}
	return esc_url_raw( $current_url );
}
```

</details>
