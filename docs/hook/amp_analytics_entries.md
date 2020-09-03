## Hook `amp_analytics_entries`


Add amp-analytics tags.

This filter allows you to easily insert any amp-analytics tags without needing much heavy lifting. This filter should be used to alter entries for transitional mode.

### Source

:link: [includes/amp-helper-functions.php:1242](../../includes/amp-helper-functions.php#L1242)

<details>
<summary>Show Code</summary>

```php
$analytics_entries = apply_filters( 'amp_analytics_entries', $analytics_entries );
```

</details>
