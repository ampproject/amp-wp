## Filter `amp_post_template_dir`

```php
apply_filters( 'amp_post_template_dir' );
```

Filters the Reader template directory.

### Source

:link: [includes/templates/class-amp-post-template.php:177](/includes/templates/class-amp-post-template.php#L177)

<details>
<summary>Show Code</summary>

```php
$template_dir = apply_filters( 'amp_post_template_dir', AMP__DIR__ . '/templates' );
```

</details>
