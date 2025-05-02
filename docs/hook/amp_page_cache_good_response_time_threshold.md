## Filter `amp_page_cache_good_response_time_threshold`

> :warning: This filter is deprecated: 2.5.0

```php
apply_filters( 'amp_page_cache_good_response_time_threshold', $threshold );
```

Filters the threshold below which a response time is considered good.

### Arguments

* `int $threshold` - Threshold in milliseconds.

### Source

:link: [src/Admin/SiteHealth.php:375](/src/Admin/SiteHealth.php#L375)

<details>
<summary>Show Code</summary>

```php
return (int) apply_filters( 'amp_page_cache_good_response_time_threshold', 600 );
```

</details>
