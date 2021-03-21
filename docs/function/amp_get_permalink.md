## Function `amp_get_permalink`

```php
function amp_get_permalink( $post_id );
```

Retrieves the full AMP-specific permalink for the given post ID.

On a site in Standard mode, this is the same as `get_permalink()`.

### Arguments

* `int $post_id` - Post ID.

### Return value

`string` - AMP permalink.

### Source

:link: [includes/amp-helper-functions.php:624](/includes/amp-helper-functions.php#L624-L629)

<details>
<summary>Show Code</summary>

```php
function amp_get_permalink( $post_id ) {
	if ( amp_is_canonical() ) {
		return get_permalink( $post_id );
	}
	return amp_add_paired_endpoint( get_permalink( $post_id ) );
}
```

</details>
