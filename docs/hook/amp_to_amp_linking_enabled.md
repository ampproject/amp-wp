## Hook `amp_to_amp_linking_enabled`

### Source

:link: [includes/amp-helper-functions.php:1457](https://github.com/ampproject/amp-wp/blob/develop/includes/amp-helper-functions.php#L1457-L1460)

<details>
<summary>Show Code</summary>

```php
$amp_to_amp_linking_enabled = (bool) apply_filters(
	'amp_to_amp_linking_enabled',
	AMP_Theme_Support::TRANSITIONAL_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
);
```

</details>
