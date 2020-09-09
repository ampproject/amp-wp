## Filter `amp_post_template_data`

```php
apply_filters( 'amp_post_template_data', $data, $post );
```

Filters AMP template data.

### Arguments

* `array $data` - Template data.
* `\WP_Post $post` - Post.

### Source

:link: [includes/templates/class-amp-post-template.php:160](/includes/templates/class-amp-post-template.php#L160)

<details>
<summary>Show Code</summary>

```php
$this->data = apply_filters( 'amp_post_template_data', $this->data, $this->post );
```

</details>
