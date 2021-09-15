## Filter `amp_featured_image_minimum_width`

```php
apply_filters( 'amp_featured_image_minimum_width', $featured_image_minimum_width );
```

Filters the minimum width required for a featured image.

### Arguments

* `int $featured_image_minimum_width` - The minimum width of the image, defaults to 1200.                                          Returning a number less than or equal to zero disables the minimum constraint.

### Source

:link: [includes/admin/class-amp-post-meta-box.php:329](/includes/admin/class-amp-post-meta-box.php#L329)

<details>
<summary>Show Code</summary>

```php
$featured_image_minimum_width = (int) apply_filters( 'amp_featured_image_minimum_width', $default_width );
```

</details>
