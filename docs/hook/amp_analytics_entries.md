## Filter `amp_analytics_entries`

```php
apply_filters( 'amp_analytics_entries', $analytics_entries );
```

Add amp-analytics tags.

This filter allows you to easily insert any amp-analytics tags without needing much heavy lifting. This filter should be used to alter entries for transitional mode.

### Arguments

* `array $analytics_entries` - An associative array of the analytics entries we want to output. Each array entry must have a unique key, and the value should be an array with the following keys: `type`, `attributes`, `config_data`. See readme for more details.

### Source

:link: [includes/amp-helper-functions.php:1292](/includes/amp-helper-functions.php#L1292)

<details>
<summary>Show Code</summary>

```php
$analytics_entries = apply_filters( 'amp_analytics_entries', $analytics_entries );
```

</details>
