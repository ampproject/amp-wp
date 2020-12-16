## Method `ScannableURLProvider::get_urls()`

```php
public function get_urls( $offset );
```

Provides the array of URLs to check.

Each URL is an array with two elements, with the URL at index 0 and the type at index 1.

### Arguments

* `int|null $offset` - Optional. The number of URLs to offset by, where applicable. Defaults to 0.

### Return value

`array` - Array of URLs and types.

### Source

:link: [src/Validation/ScannableURLProvider.php:71](/src/Validation/ScannableURLProvider.php#L71-L140)

<details>
<summary>Show Code</summary>

```php
public function get_urls( $offset = 0 ) {
	$urls = [];
	/*
	 * If 'Your homepage displays' is set to 'Your latest posts', include the homepage.
	 */
	if ( 'posts' === get_option( 'show_on_front' ) && $this->is_template_supported( 'is_home' ) ) {
		$urls[] = [
			'url'  => home_url( '/' ),
			'type' => 'home',
		];
	}
	$amp_enabled_taxonomies = array_filter(
		get_taxonomies( [ 'public' => true ] ),
		[ $this, 'does_taxonomy_support_amp' ]
	);
	$public_post_types      = get_post_types( [ 'public' => true ] );
	// Include one URL of each template/content type, then another URL of each type on the next iteration.
	for ( $i = $offset; $i < $this->limit_per_type + $offset; $i++ ) {
		// Include all public, published posts.
		foreach ( $public_post_types as $post_type ) {
			$post_ids = $this->get_posts_that_support_amp( $this->get_posts_by_type( $post_type, $i, 1 ) );
			if ( ! empty( $post_ids[0] ) ) {
				$urls[] = [
					'url'  => get_permalink( $post_ids[0] ),
					'type' => $post_type,
				];
			}
		}
		foreach ( $amp_enabled_taxonomies as $taxonomy ) {
			$taxonomy_links = $this->get_taxonomy_links( $taxonomy, $i, 1 );
			$link           = reset( $taxonomy_links );
			if ( ! empty( $link ) ) {
				$urls[] = [
					'url'  => $link,
					'type' => $taxonomy,
				];
			}
		}
		$author_page_urls = $this->get_author_page_urls( $i, 1 );
		if ( ! empty( $author_page_urls[0] ) ) {
			$urls[] = [
				'url'  => $author_page_urls[0],
				'type' => 'author',
			];
		}
	}
	// Only validate 1 date and 1 search page.
	$url = $this->get_date_page();
	if ( $url ) {
		$urls[] = [
			'url'  => $url,
			'type' => 'date',
		];
	}
	$url = $this->get_search_page();
	if ( $url ) {
		$urls[] = [
			'url'  => $url,
			'type' => 'search',
		];
	}
	return $urls;
}
```

</details>
