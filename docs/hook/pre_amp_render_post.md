## Action `pre_amp_render_post`

> :warning: This action is deprecated: Check amp_is_request() on the template_redirect action instead.

```php
do_action( 'pre_amp_render_post', $post_id );
```

Fires before rendering a post in AMP.

This action is not triggered when &#039;amp&#039; theme support is present. Instead, you should use &#039;template_redirect&#039; action and check if `amp_is_request()`.

### Arguments

* `int $post_id` - Post ID.

### Source

:link: [includes/deprecated.php:176](/includes/deprecated.php#L176)

<details>
<summary>Show Code</summary>

```php
do_action( 'pre_amp_render_post', $post_id );
```

</details>
