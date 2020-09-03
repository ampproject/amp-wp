## Filter `amp_post_template_file`

```php
apply_filters( 'amp_post_template_file', $file, $template_type, $post );
```

Filters the template file being loaded for a given template type.

### Arguments

* `string $file` - Template file.
* `string $template_type` - Template type.
* `\WP_Post $post` - Post.

### Source

:link: [includes/templates/class-amp-post-template.php:443](../../includes/templates/class-amp-post-template.php#L443)

<details>
<summary>Show Code</summary>

```php
$file = apply_filters( 'amp_post_template_file', $file, $template_type, $this->post );
```

</details>
