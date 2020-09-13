## Filter `amp_customizer_is_enabled`

```php
apply_filters( 'amp_customizer_is_enabled', $enable );
```

Filter whether to enable the AMP default template design settings.

### Arguments

* `bool $enable` - Whether to enable the AMP default template design settings. Default true.

### Source

:link: [includes/settings/class-amp-customizer-design-settings.php:58](/includes/settings/class-amp-customizer-design-settings.php#L58)

<details>
<summary>Show Code</summary>

```php
return apply_filters( 'amp_customizer_is_enabled', true );
```

</details>
