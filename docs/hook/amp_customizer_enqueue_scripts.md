## Action `amp_customizer_enqueue_scripts`

```php
do_action( 'amp_customizer_enqueue_scripts', $manager );
```

Fires when plugins should register settings for AMP.

In practice the `customize_controls_enqueue_scripts` hook should be used instead.

### Arguments

* `\WP_Customize_Manager $manager` - Manager.

### Source

:link: [includes/admin/class-amp-template-customizer.php:632](/includes/admin/class-amp-template-customizer.php#L632)

<details>
<summary>Show Code</summary>

```php
do_action( 'amp_customizer_enqueue_scripts', $this->wp_customize );
```

</details>
