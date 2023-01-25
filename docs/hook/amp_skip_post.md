## Filter `amp_skip_post`

```php
apply_filters( 'amp_skip_post', $skipped, $post_id, $post );
```

Filters whether to skip the post from AMP.

### Arguments

* `bool $skipped` - Skipped.
* `int $post_id` - Post ID.
* `\WP_Post $post` - Post.

### Source

:link: [includes/class-amp-post-type-support.php:141](/includes/class-amp-post-type-support.php#L141)

<details>
<summary>Show Code</summary>

```php
if ( ! empty( $post->ID ) && true === apply_filters( 'amp_skip_post', false, $post->ID, $post ) ) {
```

</details>
