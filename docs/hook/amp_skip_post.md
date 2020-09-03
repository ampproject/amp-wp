## Filter `amp_skip_post`


Filters whether to skip the post from AMP.

### Source

:link: [includes/class-amp-post-type-support.php:140](../../includes/class-amp-post-type-support.php#L140)

<details>
<summary>Show Code</summary>

```php
if ( isset( $post->ID ) && true === apply_filters( 'amp_skip_post', false, $post->ID, $post ) ) {
```

</details>
