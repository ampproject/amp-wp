## Function `amp_maybe_add_actions`

> :warning: This function is deprecated: This function is not used when &#039;amp&#039; theme support is added.

```php
function amp_maybe_add_actions();
```

Conditionally add AMP actions or render the transitional mode template(s).

If the request is for an AMP page and this is in 'canonical mode,' redirect to the non-AMP page. It won't need this plugin's template system, nor the frontend actions like the 'rel' link.

### Return value

`void`

### Source

:link: [includes/deprecated.php:34](/includes/deprecated.php#L34-L83)

<details>
<summary>Show Code</summary>

```php
function amp_maybe_add_actions() {
	_deprecated_function( __FUNCTION__, '1.5' );

	// Short-circuit when theme supports AMP, as everything is handled by AMP_Theme_Support.
	if ( current_theme_supports( AMP_Theme_Support::SLUG ) ) {
		return;
	}

	// The remaining logic here is for transitional mode running in themes that don't support AMP, the template system in AMP<=0.6.
	global $wp_query;
	if ( ! ( is_singular() || $wp_query->is_posts_page ) || is_feed() ) {
		return;
	}

	$is_amp_request = amp_is_request();

	/**
	 * Queried post object.
	 *
	 * @var WP_Post $post
	 */
	$post = get_queried_object();
	if ( ! amp_is_post_supported( $post ) ) {
		if ( $is_amp_request ) {
			/*
			 * Temporary redirect is used for admin users because reader mode and AMP support can be enabled by user at any time,
			 * so they will be able to make AMP available for this URL and see the change without wrestling with the redirect cache.
			 */
			wp_safe_redirect( get_permalink( $post->ID ), current_user_can( 'manage_options' ) ? 302 : 301 );
			exit;
		}
		return;
	}

	if ( $is_amp_request ) {

		// Prevent infinite URL space under /amp/ endpoint.
		global $wp;
		$path_args = [];
		wp_parse_str( $wp->matched_query, $path_args );
		if ( isset( $path_args[ amp_get_slug() ] ) && '' !== $path_args[ amp_get_slug() ] ) {
			wp_safe_redirect( amp_get_permalink( $post->ID ), 301 );
			exit;
		}

		amp_prepare_render();
	} else {
		amp_add_frontend_actions();
	}
}
```

</details>
