## Filter `amp_extract_image_dimensions_get_user_agent`

```php
apply_filters( 'amp_extract_image_dimensions_get_user_agent', $user_agent );
```

Filters the user agent for onbtaining the image dimensions.

### Arguments

* `string $user_agent` - User agent.

### Source

:link: [includes/utils/class-amp-image-dimension-extractor.php:250](/includes/utils/class-amp-image-dimension-extractor.php#L250)

<details>
<summary>Show Code</summary>

```php
$client->setUserAgent( apply_filters( 'amp_extract_image_dimensions_get_user_agent', self::get_default_user_agent() ) );
```

</details>
