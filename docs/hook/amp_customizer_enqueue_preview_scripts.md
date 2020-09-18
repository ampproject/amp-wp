## Action `amp_customizer_enqueue_preview_scripts`

```php
do_action( 'amp_customizer_enqueue_preview_scripts', $wp_customize );
```

Fires when plugins should enqueue their own scripts for the AMP Customizer preview.

### Arguments

* `\WP_Customize_Manager $wp_customize` - Manager.

### Source

:link: [includes/admin/class-amp-template-customizer.php:701](/includes/admin/class-amp-template-customizer.php#L701)

<details>
<summary>Show Code</summary>

```php
do_action( 'amp_customizer_enqueue_preview_scripts', $this->wp_customize );
```

</details>
