## Method `URLValidationProvider::get_url_validation()`

```php
public function get_url_validation( $url, $type, $force_revalidate = false );
```

Validates a URL, stores the results, and increments the counts.

### Arguments

* `string $url` - The URL to validate.
* `string $type` - The type of template, post, or taxonomy.
* `bool $force_revalidate` - Whether to force revalidation regardless of whether the current results are stale.

### Return value

`array|\WP_Error` - Associative array containing validity result and whether the URL was revalidated, or a WP_Error on failure.

### Source

:link: [src/Validation/URLValidationProvider.php:173](/src/Validation/URLValidationProvider.php#L173-L199)

<details>
<summary>Show Code</summary>

```php
public function get_url_validation( $url, $type, $force_revalidate = false ) {
	$validity    = null;
	$revalidated = true;
	if ( ! $force_revalidate ) {
		$url_post = AMP_Validated_URL_Post_Type::get_invalid_url_post( $url );
		if ( $url_post && empty( AMP_Validated_URL_Post_Type::get_post_staleness( $url_post ) ) ) {
			$validity    = AMP_Validated_URL_Post_Type::get_invalid_url_validation_errors( $url_post );
			$revalidated = false;
		}
	}
	if ( is_null( $validity ) ) {
		$validity = AMP_Validation_Manager::validate_url_and_store( $url );
	}
	if ( is_wp_error( $validity ) ) {
		return $validity;
	}
	if ( $validity && isset( $validity['results'] ) ) {
		$this->update_state_from_validity( $validity, $type );
	}
	return compact( 'validity', 'revalidated' );
}
```

</details>
