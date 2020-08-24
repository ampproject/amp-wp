## Function `post_supports_amp`

```php
function post_supports_amp( $post );
```

Determine whether a given post supports AMP.

### Arguments

* `\WP_Post $post` - Post.

### Source

:link: [includes/amp-helper-functions.php:835](https://github.com/ampproject/amp-wp/blob/develop/includes/amp-helper-functions.php#L835-L837)

<details>
<summary>Show Code</summary>

```php
function post_supports_amp( $post ) {
	return amp_is_post_supported( $post );
}
```

</details>
