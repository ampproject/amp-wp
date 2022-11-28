## Filter `amp_validation_data_gc_url_count`

```php
apply_filters( 'amp_validation_data_gc_url_count', $count );
```

Filters the count of eligible validated URLs that should be garbage collected.

If this is filtered to be zero or less, then garbage collection is disabled.

### Arguments

* `int $count` - Validated URL count. Default 100.

### Source

:link: [src/BackgroundTask/ValidationDataGarbageCollection.php:76](/src/BackgroundTask/ValidationDataGarbageCollection.php#L76)

<details>
<summary>Show Code</summary>

```php
$count = apply_filters( 'amp_validation_data_gc_url_count', 100 );
```

</details>
