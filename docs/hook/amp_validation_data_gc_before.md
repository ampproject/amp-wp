## Filter `amp_validation_data_gc_before`

```php
apply_filters( 'amp_validation_data_gc_before', $before );
```

Filters the date before which validated URLs will be garbage collected.

### Arguments

* `string|array $before` - Date before which to find amp_validated_url posts to delete. Default &#039;1 week ago&#039;.                             Accepts strtotime()-compatible string, or array of &#039;year&#039;, &#039;month&#039;, &#039;day&#039; values.

### Source

:link: [src/BackgroundTask/ValidationDataGarbageCollection.php:89](/src/BackgroundTask/ValidationDataGarbageCollection.php#L89)

<details>
<summary>Show Code</summary>

```php
$before = apply_filters( 'amp_validation_data_gc_before', '1 week ago' );
```

</details>
