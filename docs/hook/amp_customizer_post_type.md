## Filter `amp_customizer_post_type`

```php
apply_filters( 'amp_customizer_post_type', $post_type );
```

Filter the post type to retrieve the latest for use in the AMP template customizer.

### Arguments

* `string $post_type` - Post type slug. Default &#039;post&#039;.

### Source

:link: [includes/admin/functions.php:45](/includes/admin/functions.php#L45)

<details>
<summary>Show Code</summary>

```php
$post_type = (string) apply_filters( 'amp_customizer_post_type', 'post' );
```

</details>
