## Filter `amp_extract_image_dimensions_batch`

```php
apply_filters( 'amp_extract_image_dimensions_batch', $extracted_dimensions );
```

Filters the dimensions extracted from image URLs.

### Arguments

* `array $extracted_dimensions` - Extracted dimensions, initially mapping images URLs to false.

### Source

:link: [includes/utils/class-amp-image-dimension-extractor.php:69](/includes/utils/class-amp-image-dimension-extractor.php#L69)

<details>
<summary>Show Code</summary>

```php
$extracted_dimensions = apply_filters( 'amp_extract_image_dimensions_batch', $extracted_dimensions );
```

</details>
