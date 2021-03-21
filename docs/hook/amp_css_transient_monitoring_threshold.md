## Filter `amp_css_transient_monitoring_threshold`

```php
apply_filters( 'amp_css_transient_monitoring_threshold', $threshold );
```

Filters the threshold to use for disabling transient caching of stylesheets.

### Arguments

* `int $threshold` - Maximum average number of transients per day.

### Source

:link: [src/BackgroundTask/MonitorCssTransientCaching.php:249](/src/BackgroundTask/MonitorCssTransientCaching.php#L249)

<details>
<summary>Show Code</summary>

```php
$threshold = (float) apply_filters( 'amp_css_transient_monitoring_threshold', self::DEFAULT_THRESHOLD );
```

</details>
