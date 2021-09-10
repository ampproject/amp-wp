## Function `amp_remove_paired_endpoint`

```php
function amp_remove_paired_endpoint( $url );
```

Remove the paired AMP endpoint from a given URL.

### Arguments

* `string $url` - URL.

### Return value

`string` - URL with AMP stripped.

### Source

:link: [includes/amp-helper-functions.php:2038](/includes/amp-helper-functions.php#L2038-L2050)

<details>
<summary>Show Code</summary>

```php
function amp_remove_paired_endpoint( $url ) {
	try {
		return Services::get( 'paired_routing' )->remove_endpoint( $url );
	} catch ( InvalidService $e ) {
		if ( ! amp_is_enabled() ) {
			$reason = __( 'Function called while AMP is disabled via `amp_is_enabled` filter.', 'amp' );
		} else {
			$reason = __( 'Function cannot be called before services are registered.', 'amp' );
		}
		_doing_it_wrong( __FUNCTION__, esc_html( $reason ) . ' ' . esc_html( $e->getMessage() ), '2.1.1' );
		return $url;
	}
}
```

</details>
