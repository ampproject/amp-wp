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

:link: [includes/amp-helper-functions.php:2101](/includes/amp-helper-functions.php#L2101-L2113)

<details>
<summary>Show Code</summary>

```php
function amp_add_paired_endpoint( $url ) {
	try {
		return Services::get( 'paired_routing' )->add_endpoint( $url );
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
