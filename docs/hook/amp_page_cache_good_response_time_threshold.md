## Filter `amp_page_cache_good_response_time_threshold`

```php
apply_filters( 'amp_page_cache_good_response_time_threshold', $threshold );
```

Filters the threshold below which a response time is considered good.

### Arguments

* `int $threshold` - Threshold in milliseconds.

### Source

:link: [src/Admin/SiteHealth.php:347](/src/Admin/SiteHealth.php#L347)

<details>
<summary>Show Code</summary>

```php
return (int) apply_filters( 'amp_page_cache_good_response_time_threshold', 600 );
```

</details>
