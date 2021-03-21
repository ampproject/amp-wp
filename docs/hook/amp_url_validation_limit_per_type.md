## Filter `amp_url_validation_limit_per_type`

```php
apply_filters( 'amp_url_validation_limit_per_type', $url_validation_number_per_type );
```

Filters the number of URLs per content type to check during each run of the validation cron task.

### Arguments

* `int $url_validation_number_per_type` - The number of URLs. Defaults to 1. Filtering to -1 will result in all being returned.

### Source

:link: [src/Validation/URLScanningContext.php:81](/src/Validation/URLScanningContext.php#L81)

<details>
<summary>Show Code</summary>

```php
$url_validation_limit_per_type = (int) apply_filters( 'amp_url_validation_limit_per_type', $this->limit_per_type );
```

</details>
