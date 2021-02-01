## Filter `amp_featured_image_minimum_height`

```php
apply_filters( 'amp_featured_image_minimum_height', $featured_image_minimum_height );
```

Filters the minimum height required for a featured image.

### Arguments

* `int $featured_image_minimum_height` - The minimum height of the image, defaults to 675.                                           Returning a number less than or equal to zero disables the minimum constraint.

### Source

:link: [includes/admin/class-amp-post-meta-box.php:305](/includes/admin/class-amp-post-meta-box.php#L305)

<details>
<summary>Show Code</summary>

```php
$featured_image_minimum_height = (int) apply_filters( 'amp_featured_image_minimum_height', $default_height );
```

</details>
