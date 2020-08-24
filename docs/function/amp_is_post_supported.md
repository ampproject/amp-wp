## Function `amp_is_post_supported`

```php
function amp_is_post_supported( $post );
```

Determine whether a given post supports AMP.

### Arguments

* `\WP_Post $post` - Post.

### Source

:link: [includes/amp-helper-functions.php:820](../../includes/amp-helper-functions.php#L820-L822)

<details>
<summary>Show Code</summary>

```php
function amp_is_post_supported( $post ) {
	return 0 === count( AMP_Post_Type_Support::get_support_errors( $post ) );
}
```

</details>
