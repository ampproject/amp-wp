## Function `amp_is_post_supported`

```php
function amp_is_post_supported( $post );
```

Determine whether a given post supports AMP.

### Arguments

* `\WP_Post|int $post` - Post.

### Return value

`bool` - Whether the post supports AMP.

### Source

:link: [includes/amp-helper-functions.php:759](/includes/amp-helper-functions.php#L759-L761)

<details>
<summary>Show Code</summary>

```php
function amp_is_post_supported( $post ) {
	return 0 === count( AMP_Post_Type_Support::get_support_errors( $post ) );
}
```

</details>
