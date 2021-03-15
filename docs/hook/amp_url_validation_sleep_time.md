## Filter `amp_url_validation_sleep_time`

```php
apply_filters( 'amp_url_validation_sleep_time',  );
```

Filters the length of time to sleep between validating URLs.

### Arguments

* `int ` - The number of seconds. Default 1. Setting to 0 or a negative numbers disables all throttling.

### Source

:link: [src/Validation/URLValidationCron.php:145](/src/Validation/URLValidationCron.php#L145)

<details>
<summary>Show Code</summary>

```php
return max( (int) apply_filters( 'amp_url_validation_sleep_time', self::DEFAULT_SLEEP_TIME ), 0 );
```

</details>
