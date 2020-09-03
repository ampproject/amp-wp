## Hook `amp_css_transient_monitoring_sampling_range`


Filters the sampling range to use for monitoring the transient caching of stylesheets.

### Source

:link: [src/BackgroundTask/MonitorCssTransientCaching.php:268](../../src/BackgroundTask/MonitorCssTransientCaching.php#L268)

<details>
<summary>Show Code</summary>

```php
$sampling_range = (int) apply_filters( 'amp_css_transient_monitoring_sampling_range', self::DEFAULT_SAMPLING_RANGE );
```

</details>
