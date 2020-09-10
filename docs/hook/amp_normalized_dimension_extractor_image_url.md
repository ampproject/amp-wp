## Filter `amp_normalized_dimension_extractor_image_url`

```php
apply_filters( 'amp_normalized_dimension_extractor_image_url', $normalized_url, $url );
```

Apply filters on the normalized image URL for dimension extraction.

### Arguments

* `string $normalized_url` - Normalized image URL.
* `string $url` - Original image URL.

### Source

:link: [includes/utils/class-amp-image-dimension-extractor.php:132](/includes/utils/class-amp-image-dimension-extractor.php#L132)

<details>
<summary>Show Code</summary>

```php
$normalized_url = apply_filters( 'amp_normalized_dimension_extractor_image_url', $normalized_url, $url );
```

</details>
