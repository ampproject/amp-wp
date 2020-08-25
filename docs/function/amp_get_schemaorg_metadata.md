## Function `amp_get_schemaorg_metadata`

```php
function amp_get_schemaorg_metadata();
```

Get schema.org metadata for the current query.

### Return value

`array` - $metadata All schema.org metadata for the post.

### Source

:link: [includes/amp-helper-functions.php:1740](../../includes/amp-helper-functions.php#L1740-L1811)

<details>
<summary>Show Code</summary>

```php
function amp_get_schemaorg_metadata() {
	$metadata = [
		'@context'  => 'http://schema.org',
		'publisher' => [
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
		],
	];

	$publisher_logo = amp_get_publisher_logo();
	if ( $publisher_logo ) {
		$metadata['publisher']['logo'] = [
			'@type' => 'ImageObject',
			'url'   => $publisher_logo,
		];
	}

	$queried_object = get_queried_object();
	if ( $queried_object instanceof WP_Post ) {
		$metadata = array_merge(
			$metadata,
			[
				'@type'            => is_page() ? 'WebPage' : 'BlogPosting',
				'mainEntityOfPage' => get_permalink(),
				'headline'         => get_the_title(),
				'datePublished'    => mysql2date( 'c', $queried_object->post_date_gmt, false ),
				'dateModified'     => mysql2date( 'c', $queried_object->post_modified_gmt, false ),
			]
		);

		$post_author = get_userdata( $queried_object->post_author );
		if ( $post_author ) {
			$metadata['author'] = [
				'@type' => 'Person',
				'name'  => html_entity_decode( $post_author->display_name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
			];
		}

		$image_metadata = amp_get_post_image_metadata( $queried_object );
		if ( $image_metadata ) {
			$metadata['image'] = $image_metadata['url'];
		}

		/**
		 * Filters Schema.org metadata for a post.
		 *
		 * The 'post_template' in the filter name here is due to this filter originally being introduced in `AMP_Post_Template`.
		 * In general the `amp_schemaorg_metadata` filter should be used instead.
		 *
		 * @since 0.3
		 *
		 * @param array   $metadata       Metadata.
		 * @param WP_Post $queried_object Post.
		 */
		$metadata = apply_filters( 'amp_post_template_metadata', $metadata, $queried_object );
	} elseif ( is_archive() ) {
		$metadata['@type'] = 'CollectionPage';
	}

	/**
	 * Filters Schema.org metadata for a query.
	 *
	 * Check the the main query for the context for which metadata should be added.
	 *
	 * @since 0.7
	 *
	 * @param array   $metadata Metadata.
	 */
	$metadata = apply_filters( 'amp_schemaorg_metadata', $metadata );

	return $metadata;
}
```

</details>
