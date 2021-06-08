## Filter `amp_post_template_analytics`

```php
apply_filters( 'amp_post_template_analytics', $analytics, $post );
```

Add amp-analytics tags.

This filter allows you to easily insert any amp-analytics tags without needing much heavy lifting. This filter should be used to alter entries for legacy AMP templates.

### Arguments

* `array $analytics` - An associative array of the analytics entries we want to output. Each array entry must have a unique key, and the value should be an array with the following keys: `type`, `attributes`, `script_data`. See readme for more details.
* `\WP_Post $post` - The current post.

### Source

:link: [includes/amp-post-template-functions.php:154](/includes/amp-post-template-functions.php#L154)

<details>
<summary>Show Code</summary>

```php
$analytics = apply_filters( 'amp_post_template_analytics', $analytics, get_queried_object() );
```

</details>
