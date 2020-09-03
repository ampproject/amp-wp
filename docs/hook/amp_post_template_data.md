## Hook `amp_post_template_data`


Filters AMP template data.

### Source

:link: [includes/templates/class-amp-post-template.php:160](../../includes/templates/class-amp-post-template.php#L160)

<details>
<summary>Show Code</summary>

```php
$this->data = apply_filters( 'amp_post_template_data', $this->data, $this->post );
```

</details>
