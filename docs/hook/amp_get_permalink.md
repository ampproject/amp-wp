## Filter `amp_get_permalink`

```php
apply_filters( 'amp_get_permalink', $amp_url, $post_id );
```

Filters AMP permalink.

### Arguments

* `string $amp_url` - AMP URL.
* `int $post_id` - Post ID.

### Source

:link: [src/PairedUrlStructure/LegacyReaderUrlStructure.php:81](/src/PairedUrlStructure/LegacyReaderUrlStructure.php#L81)

<details>
<summary>Show Code</summary>

```php
$amp_url = apply_filters( 'amp_get_permalink', $amp_url, $post_id );
```

</details>
