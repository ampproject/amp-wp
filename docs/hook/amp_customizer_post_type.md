## Hook `amp_customizer_post_type`


Filter the post type to retrieve the latest for use in the AMP template customizer.

### Source

:link: [includes/admin/functions.php:43](../../includes/admin/functions.php#L43)

<details>
<summary>Show Code</summary>

```php
$post_type = (string) apply_filters( 'amp_customizer_post_type', 'post' );
```

</details>
