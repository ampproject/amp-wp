## Filter `amp_post_template_metadata`

```php
apply_filters( 'amp_post_template_metadata', $metadata, $queried_object );
```

Filters Schema.org metadata for a post.

The &#039;post_template&#039; in the filter name here is due to this filter originally being introduced in `AMP_Post_Template`. In general the `amp_schemaorg_metadata` filter should be used instead.

### Arguments

* `array $metadata` - Metadata.
* `\WP_Post $queried_object` - Post.

### Source

:link: [includes/amp-helper-functions.php:1836](/includes/amp-helper-functions.php#L1836)

<details>
<summary>Show Code</summary>

```php
$metadata = apply_filters( 'amp_post_template_metadata', $metadata, $queried_object );
```

</details>
