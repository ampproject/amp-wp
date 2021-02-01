## Function `amp_has_paired_endpoint`

```php
function amp_has_paired_endpoint( $url = '' );
```

Determine a given URL is for a paired AMP request.

### Arguments

* `string $url` - URL to examine. If empty, will use the current URL.

### Return value

`bool` - True if the AMP query parameter is set with the required value, false if not.

### Source

:link: [includes/amp-helper-functions.php:1975](/includes/amp-helper-functions.php#L1975-L2012)

<details>
<summary>Show Code</summary>

```php
function amp_has_paired_endpoint( $url = '' ) {
	$slug = amp_get_slug();

	// If the URL was not provided, then use the environment which is already parsed.
	if ( empty( $url ) ) {
		global $wp_query;
		return (
			isset( $_GET[ $slug ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			||
			(
				$wp_query instanceof WP_Query
				&&
				false !== $wp_query->get( $slug, false )
			)
		);
	}

	$parsed_url = wp_parse_url( $url );
	if ( ! empty( $parsed_url['query'] ) ) {
		$query_vars = [];
		wp_parse_str( $parsed_url['query'], $query_vars );
		if ( isset( $query_vars[ $slug ] ) ) {
			return true;
		}
	}

	if ( ! empty( $parsed_url['path'] ) ) {
		$pattern = sprintf(
			'#/%s(/[^/^])?/?$#',
			preg_quote( $slug, '#' )
		);
		if ( preg_match( $pattern, $parsed_url['path'] ) ) {
			return true;
		}
	}

	return false;
}
```

</details>
