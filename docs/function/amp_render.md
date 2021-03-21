## Function `amp_render`

> :warning: This function is deprecated: This function is not used when &#039;amp&#039; theme support is added.

```php
function amp_render();
```

Render AMP for queried post.

### Source

:link: [includes/deprecated.php:118](/includes/deprecated.php#L118-L127)

<details>
<summary>Show Code</summary>

```php
function amp_render() {
	_deprecated_function( __FUNCTION__, '1.5' );

	// Note that queried object is used instead of the ID so that the_preview for the queried post can apply.
	$post = get_queried_object();
	if ( $post instanceof WP_Post ) {
		amp_render_post( $post );
		exit;
	}
}
```

</details>
