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

