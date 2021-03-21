## Filter `amp_post_status_default_enabled`

```php
apply_filters( 'amp_post_status_default_enabled', $status, $post );
```

Filters whether default AMP status should be enabled or not.

### Arguments

* `string $status` - Status.
* `\WP_Post $post` - Post.

### Source

:link: [includes/class-amp-post-type-support.php:196](/includes/class-amp-post-type-support.php#L196)

<details>
<summary>Show Code</summary>

```php
$enabled = apply_filters( 'amp_post_status_default_enabled', $enabled, $post );
```

</details>
