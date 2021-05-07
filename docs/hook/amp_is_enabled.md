## Filter `amp_is_enabled`

```php
apply_filters( 'amp_is_enabled', $enabled );
```

Filters whether AMP is enabled on the current site.

Useful if the plugin is network activated and you want to turn it off on select sites.

### Arguments

* `bool $enabled` - Whether the AMP plugin&#039;s functionality should be enabled.

### Source

:link: [includes/amp-helper-functions.php:36](/includes/amp-helper-functions.php#L36)

<details>
<summary>Show Code</summary>

```php
return (bool) apply_filters( 'amp_is_enabled', true );
```

</details>
