## Action `amp_post_template_include_{$template_type}`

```php
do_action( 'amp_post_template_include_{$template_type}', $this );
```

Fires before including a template.

### Arguments

* `\AMP_Post_Template $this` - Post template.

### Source

:link: [includes/templates/class-amp-post-template.php:484](/includes/templates/class-amp-post-template.php#L484)

<details>
<summary>Show Code</summary>

```php
do_action( "amp_post_template_include_{$template_type}", $this );
```

</details>
