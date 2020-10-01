## Action `amp_customizer_register_settings`

```php
do_action( 'amp_customizer_register_settings', $manager );
```

Fires when plugins should register settings for AMP.

In practice the `customize_register` hook should be used instead.

### Arguments

* `\WP_Customize_Manager $manager` - Manager.

### Source

:link: [includes/admin/class-amp-template-customizer.php:296](/includes/admin/class-amp-template-customizer.php#L296)

<details>
<summary>Show Code</summary>

```php
do_action( 'amp_customizer_register_settings', $this->wp_customize );
```

</details>
