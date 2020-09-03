## Hook `amp_customizer_register_ui`


Fires after the AMP panel has been registered for plugins to add additional controls.

In practice the `customize_register` hook should be used instead.

### Source

:link: [includes/admin/class-amp-template-customizer.php:259](../../includes/admin/class-amp-template-customizer.php#L259)

<details>
<summary>Show Code</summary>

```php
do_action( 'amp_customizer_register_ui', $this->wp_customize );
```

</details>
