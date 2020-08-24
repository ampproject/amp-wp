## Function `amp_redirect_old_slug_to_new_url`

```php
function amp_redirect_old_slug_to_new_url( $link );
```

Redirects the old AMP URL to the new AMP URL.

If post slug is updated the amp page with old post slug will be redirected to the updated url.

### Arguments

* `string $link` - New URL of the post.

### Source

[includes/amp-helper-functions.php:572](https://github.com/ampproject/amp-wp/blob/develop/includes/amp-helper-functions.php#L572-L583)

<details>
<summary>Show Code</summary>

```php
function amp_redirect_old_slug_to_new_url( $link ) {

	if ( amp_is_request() && ! amp_is_canonical() ) {
		if ( ! amp_is_legacy() ) {
			$link = add_query_arg( amp_get_slug(), '', $link );
		} else {
			$link = trailingslashit( trailingslashit( $link ) . amp_get_slug() );
		}
	}

	return $link;
}
```

</details>
