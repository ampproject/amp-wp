## Action `amp_customizer_register_ui`

```php
do_action( 'amp_customizer_register_ui', $manager );
```

Fires after the AMP panel has been registered for plugins to add additional controls.

In practice the `customize_register` hook should be used instead.

### Arguments

* `\WP_Customize_Manager $manager` - Manager.

### Source

:link: [includes/admin/class-amp-template-customizer.php:302](/includes/admin/class-amp-template-customizer.php#L302)

<details>
<summary>Show Code</summary>

```php
do_action( 'amp_customizer_register_ui', $this->wp_customize );
```

</details>
