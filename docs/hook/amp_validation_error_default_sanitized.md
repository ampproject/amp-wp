## Filter `amp_validation_error_default_sanitized`

```php
apply_filters( 'amp_validation_error_default_sanitized', $accepted, $error );
```

Filters whether sanitization is accepted for a newly-encountered validation error .

This only applies to validation errors that have not been encountered before. To override the sanitization status of existing validation errors, use the `amp_validation_error_sanitized` filter.

### Arguments

* `bool $accepted` - Default accepted.
* `array|null $error` - Validation error. May be null when asking if accepting sanitization is enabled by default.

### Source

:link: [includes/validation/class-amp-validation-manager.php:296](/includes/validation/class-amp-validation-manager.php#L296)

<details>
<summary>Show Code</summary>

```php
return apply_filters( 'amp_validation_error_default_sanitized', $accepted, $error );
```

</details>
