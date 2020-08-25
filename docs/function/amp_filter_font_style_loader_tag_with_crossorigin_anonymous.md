## Function `amp_filter_font_style_loader_tag_with_crossorigin_anonymous`

```php
function amp_filter_font_style_loader_tag_with_crossorigin_anonymous( $tag, $handle, $href );
```

Explicitly opt-in to CORS mode by adding the crossorigin attribute to font stylesheet links.

This explicitly triggers a CORS request, and gets back a non-opaque response, ensuring that a service worker caching the external stylesheet will not inflate the storage quota. This must be done in AMP and non-AMP alike because in transitional mode the service worker could cache the font stylesheets in a non-AMP document without CORS (crossorigin=&quot;anonymous&quot;) in which case the service worker could then fail to serve the cached font resources in an AMP document with the warning:
 &gt; The FetchEvent resulted in a network error response: an &quot;opaque&quot; response was used for a request whose type is not no-cors

### Arguments

* `string $tag` - Link tag HTML.
* `string $handle` - Dependency handle.
* `string $href` - Link URL.

### Return value

`string` - Link tag HTML.

### Source

:link: [includes/amp-helper-functions.php:1188](../../includes/amp-helper-functions.php#L1188-L1207)

<details>
<summary>Show Code</summary>

```php
function amp_filter_font_style_loader_tag_with_crossorigin_anonymous( $tag, $handle, $href ) {
	static $allowed_font_src_regex = null;
	if ( ! $allowed_font_src_regex ) {
		$spec_name = 'link rel=stylesheet for fonts'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'link' ) as $spec_rule ) {
			if ( isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && $spec_name === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$allowed_font_src_regex = '@^(' . $spec_rule[ AMP_Rule_Spec::ATTR_SPEC_LIST ]['href']['value_regex'] . ')$@';
				break;
			}
		}
	}

	$href = preg_replace( '#^(http:)?(?=//)#', 'https:', $href );

	if ( preg_match( $allowed_font_src_regex, $href ) && false === strpos( $tag, 'crossorigin=' ) ) {
		$tag = preg_replace( '/(?<=<link\s)/', 'crossorigin="anonymous" ', $tag );
	}

	return $tag;
}
```

</details>
