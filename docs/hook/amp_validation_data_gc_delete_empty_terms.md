## Filter `amp_validation_data_gc_delete_empty_terms`

```php
apply_filters( 'amp_validation_data_gc_delete_empty_terms', $enabled );
```

Filters whether to delete empty terms during validation garbage collection.

### Arguments

* `bool $enabled` - Whether enabled. Default true.

### Source

:link: [src/BackgroundTask/ValidationDataGarbageCollection.php:100](/src/BackgroundTask/ValidationDataGarbageCollection.php#L100)

<details>
<summary>Show Code</summary>

```php
if ( apply_filters( 'amp_validation_data_gc_delete_empty_terms', true ) ) {
```

</details>
