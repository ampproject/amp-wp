## Function `amp_get_permalink`

```php
function amp_get_permalink( $post_id );
```

Retrieves the full AMP-specific permalink for the given post ID.

### Arguments

* `int $post_id` - Post ID.

### Return value

`string` - AMP permalink.

### Source

:link: [includes/amp-helper-functions.php:722](/includes/amp-helper-functions.php#L722-L753)

<details>
<summary>Show Code</summary>

```php
function amp_get_permalink( $post_id ) {
	/**
	 * Filters the AMP permalink to short-circuit normal generation.
	 *
	 * Returning a non-false value in this filter will cause the `get_permalink()` to get called and the `amp_get_permalink` filter to not apply.
	 *
	 * @since 0.4
	 * @since 1.0 This filter does not apply when 'amp' theme support is present.
	 *
	 * @param false $url     Short-circuited URL.
	 * @param int   $post_id Post ID.
	 */
	$pre_url = apply_filters( 'amp_pre_get_permalink', false, $post_id );

	if ( false !== $pre_url ) {
		return $pre_url;
	}

	$permalink = get_permalink( $post_id );
	$amp_url   = amp_is_canonical() ? $permalink : amp_add_paired_endpoint( $permalink );

	/**
	 * Filters AMP permalink.
	 *
	 * @since 0.2
	 * @since 1.0 This filter does not apply when 'amp' theme support is present.
	 *
	 * @param false $amp_url AMP URL.
	 * @param int   $post_id Post ID.
	 */
	return apply_filters( 'amp_get_permalink', $amp_url, $post_id );
}
```

</details>
