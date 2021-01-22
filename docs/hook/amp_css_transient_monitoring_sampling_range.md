## Filter `amp_css_transient_monitoring_sampling_range`

```php
apply_filters( 'amp_css_transient_monitoring_sampling_range', $sampling_rage );
```

Filters the sampling range to use for monitoring the transient caching of stylesheets.

### Arguments

* `int $sampling_rage` - Sampling range in number of days.

### Source

:link: [src/BackgroundTask/MonitorCssTransientCaching.php:270](/src/BackgroundTask/MonitorCssTransientCaching.php#L270)

<details>
<summary>Show Code</summary>

```php
$sampling_range = (int) apply_filters( 'amp_css_transient_monitoring_sampling_range', self::DEFAULT_SAMPLING_RANGE );
```

</details>
