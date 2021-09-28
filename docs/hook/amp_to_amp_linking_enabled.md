## Filter `amp_to_amp_linking_enabled`

```php
apply_filters( 'amp_to_amp_linking_enabled', $amp_to_amp_linking_enabled );
```

Filters whether AMP-to-AMP linking should be enabled.

### Arguments

* `bool $amp_to_amp_linking_enabled` - Whether AMP-to-AMP linking should be enabled.

### Source

:link: [includes/amp-helper-functions.php:1371](/includes/amp-helper-functions.php#L1371-L1374)

<details>
<summary>Show Code</summary>

```php
$amp_to_amp_linking_enabled = (bool) apply_filters(
	'amp_to_amp_linking_enabled',
	AMP_Theme_Support::TRANSITIONAL_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
);
```

</details>
