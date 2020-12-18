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

:link: [includes/amp-helper-functions.php:722](/includes/amp-helper-functions.php#L722-L791)

<details>
<summary>Show Code</summary>

```php
function amp_get_permalink( $post_id ) {

	// When theme support is present (i.e. not using legacy Reader post templates), the plain query var should always be used.
	if ( ! amp_is_legacy() ) {
		$permalink = get_permalink( $post_id );
		if ( ! amp_is_canonical() ) {
			$permalink = add_query_arg( amp_get_slug(), '', $permalink );
		}
		return $permalink;
	}

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

	if ( amp_is_canonical() ) {
		$amp_url = $permalink;
	} else {
		$parsed_url    = wp_parse_url( get_permalink( $post_id ) );
		$structure     = get_option( 'permalink_structure' );
		$use_query_var = (
			// If pretty permalinks aren't available, then query var must be used.
			empty( $structure )
			||
			// If there are existing query vars, then always use the amp query var as well.
			! empty( $parsed_url['query'] )
			||
			// If the post type is hierarchical then the /amp/ endpoint isn't available.
			is_post_type_hierarchical( get_post_type( $post_id ) )
			||
			// Attachment pages don't accept the /amp/ endpoint.
			'attachment' === get_post_type( $post_id )
		);
		if ( $use_query_var ) {
			$amp_url = add_query_arg( amp_get_slug(), '', $permalink );
		} else {
			$amp_url = preg_replace( '/#.*/', '', $permalink );
			$amp_url = trailingslashit( $amp_url ) . user_trailingslashit( amp_get_slug(), 'single_amp' );
			if ( ! empty( $parsed_url['fragment'] ) ) {
				$amp_url .= '#' . $parsed_url['fragment'];
			}
		}
	}

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
