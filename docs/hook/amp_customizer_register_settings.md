## Hook `amp_customizer_register_settings`


Fires when plugins should register settings for AMP.

In practice the `customize_register` hook should be used instead.

### Source

:link: [includes/admin/class-amp-template-customizer.php:295](../../includes/admin/class-amp-template-customizer.php#L295)

<details>
<summary>Show Code</summary>

```php
do_action( 'amp_customizer_register_settings', $this->wp_customize );
```

</details>
