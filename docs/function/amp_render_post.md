## Function `amp_render_post`

> :warning: This function is deprecated: Rendering a post is now handled by AMP_Theme_Support.

```php
function amp_render_post( $post );
```

Render AMP post template.

### Arguments

* `\WP_Post|int $post` - Post.

### Source

:link: [includes/deprecated.php:137](/includes/deprecated.php#L137-L183)

<details>
<summary>Show Code</summary>

```php
function amp_render_post( $post ) {
	_deprecated_function( __FUNCTION__, '1.5' );
	global $wp_query;

	if ( ! ( $post instanceof WP_Post ) ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}
	}
	$post_id = $post->ID;

	/*
	 * If amp_render_post is called directly outside of the standard endpoint, amp_is_request() will return false,
	 * which is not ideal for any code that expects to run in an AMP context.
	 * Let's force the value to be true while we render AMP.
	 */
	$was_set = isset( $wp_query->query_vars[ amp_get_slug() ] );
	if ( ! $was_set ) {
		$wp_query->query_vars[ amp_get_slug() ] = true;
	}

	// Prevent New Relic from causing invalid AMP responses due the NREUM script it injects after the meta charset.
	if ( extension_loaded( 'newrelic' ) ) {
		newrelic_disable_autorum();
	}

	/**
	 * Fires before rendering a post in AMP.
	 *
	 * This action is not triggered when 'amp' theme support is present. Instead, you should use 'template_redirect' action and check if `amp_is_request()`.
	 *
	 * @since 0.2
	 * @deprecated Check amp_is_request() on the template_redirect action instead.
	 *
	 * @param int $post_id Post ID.
	 */
	do_action( 'pre_amp_render_post', $post_id );

	amp_add_post_template_actions();
	$template = new AMP_Post_Template( $post );
	$template->load();

	if ( ! $was_set ) {
		unset( $wp_query->query_vars[ amp_get_slug() ] );
	}
}
```

</details>
