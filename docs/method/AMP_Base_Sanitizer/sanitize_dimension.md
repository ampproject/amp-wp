## Method `AMP_Base_Sanitizer::sanitize_dimension()`

```php
public function sanitize_dimension( $value, $dimension );
```

Sanitizes a CSS dimension specifier while being sensitive to dimension context.

### Arguments

* `string $value` - A valid CSS dimension specifier; e.g. 50, 50px, 50%. Can be &#039;auto&#039; for width.
* `string $dimension` - Dimension, either &#039;width&#039; or &#039;height&#039;.

