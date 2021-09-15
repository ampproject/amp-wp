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

:link: [includes/amp-helper-functions.php:2016](/includes/amp-helper-functions.php#L2016-L2028)

<details>
<summary>Show Code</summary>

```php
function amp_has_paired_endpoint( $url = '' ) {
	try {
		return Services::get( 'paired_routing' )->has_endpoint( $url );
	} catch ( InvalidService $e ) {
		if ( ! amp_is_enabled() ) {
			$reason = __( 'Function called while AMP is disabled via `amp_is_enabled` filter.', 'amp' );
		} else {
			$reason = __( 'Function cannot be called before services are registered.', 'amp' );
		}
		_doing_it_wrong( __FUNCTION__, esc_html( $reason ) . ' ' . esc_html( $e->getMessage() ), '2.1.1' );
		return false;
	}
}
```

</details>
