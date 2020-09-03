## Hook `pre_amp_render_post`

> :warning: This function is deprecated: Check amp_is_request() on the template_redirect action instead.


Fires before rendering a post in AMP.

This action is not triggered when &#039;amp&#039; theme support is present. Instead, you should use &#039;template_redirect&#039; action and check if `amp_is_request()`.

### Source

:link: [includes/deprecated.php:174](../../includes/deprecated.php#L174)

<details>
<summary>Show Code</summary>

```php
do_action( 'pre_amp_render_post', $post_id );
```

</details>
