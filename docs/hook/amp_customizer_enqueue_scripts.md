## Action `amp_customizer_enqueue_scripts`


Fires when plugins should register settings for AMP.

In practice the `customize_controls_enqueue_scripts` hook should be used instead.

### Source

:link: [includes/admin/class-amp-template-customizer.php:611](../../includes/admin/class-amp-template-customizer.php#L611)

<details>
<summary>Show Code</summary>

```php
do_action( 'amp_customizer_enqueue_scripts', $this->wp_customize );
```

</details>
