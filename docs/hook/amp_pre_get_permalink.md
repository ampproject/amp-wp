## Filter `amp_pre_get_permalink`

```php
apply_filters( 'amp_pre_get_permalink', $url, $post_id );
```

Filters the AMP permalink to short-circuit normal generation.

Returning a string value in this filter will bypass the `get_permalink()` from being called and the `amp_get_permalink` filter will not apply.

### Arguments

* `false $url` - Short-circuited URL.
* `int $post_id` - Post ID.

### Source

:link: [src/PairedUrlStructure/LegacyReaderUrlStructure.php:44](/src/PairedUrlStructure/LegacyReaderUrlStructure.php#L44)

<details>
<summary>Show Code</summary>

```php
$pre_url = apply_filters( 'amp_pre_get_permalink', false, $post_id );
```

</details>
