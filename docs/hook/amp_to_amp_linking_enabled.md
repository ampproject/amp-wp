## Hook `amp_to_amp_linking_enabled`


Filters whether AMP-to-AMP linking should be enabled.

### Source

:link: [includes/amp-helper-functions.php:1468](../../includes/amp-helper-functions.php#L1468-L1471)

<details>
<summary>Show Code</summary>

```php
$amp_to_amp_linking_enabled = (bool) apply_filters(
	'amp_to_amp_linking_enabled',
	AMP_Theme_Support::TRANSITIONAL_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
);
```

</details>
