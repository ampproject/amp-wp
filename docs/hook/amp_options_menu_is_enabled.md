## Filter `amp_options_menu_is_enabled`

```php
apply_filters( 'amp_options_menu_is_enabled', $enable );
```

Filter whether to enable the AMP settings.

### Arguments

* `bool $enable` - Whether to enable the AMP settings. Default true.

### Source

:link: [src/Admin/OptionsMenu.php:79](/src/Admin/OptionsMenu.php#L79)

<details>
<summary>Show Code</summary>

```php
return (bool) apply_filters( 'amp_options_menu_is_enabled', true );
```

</details>
