## Hook `amp_validation_error_default_sanitized`


Filters whether sanitization is accepted for a newly-encountered validation error .

This only applies to validation errors that have not been encountered before. To override the sanitization status of existing validation errors, use the `amp_validation_error_sanitized` filter.

### Source

:link: [includes/validation/class-amp-validation-manager.php:339](../../includes/validation/class-amp-validation-manager.php#L339)

<details>
<summary>Show Code</summary>

```php
return apply_filters( 'amp_validation_error_default_sanitized', $accepted, $error );
```

</details>
