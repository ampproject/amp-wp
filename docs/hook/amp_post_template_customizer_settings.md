## Filter `amp_post_template_customizer_settings`

```php
apply_filters( 'amp_post_template_customizer_settings', $settings, $post );
```

Filter AMP Customizer settings.

Inject your Customizer settings here to make them accessible via the getter in your custom style.php template.
 Example:
     echo esc_html( $this-&gt;get_customizer_setting( &#039;your_setting_key&#039;, &#039;your_default_value&#039; ) );

### Arguments

* `array $settings` - Array of AMP Customizer settings.
* `\WP_Post $post` - Current post object.

### Source

:link: [includes/templates/class-amp-post-template.php:428](/includes/templates/class-amp-post-template.php#L428)

<details>
<summary>Show Code</summary>

```php
$this->add_data_by_key( 'customizer_settings', apply_filters( 'amp_post_template_customizer_settings', $settings, $this->post ) );
```

</details>
