## Filter `amp_validation_error_sanitized`

```php
apply_filters( 'amp_validation_error_sanitized', $sanitized, $error );
```

Filters whether the validation error should be sanitized.

Returning true this indicates that the validation error is acceptable and should not be considered a blocker to render AMP. Returning null means that the default status should be used.
 Note that the $node is not passed here to ensure that the filter can be applied on validation errors that have been stored. Likewise, the $sources are also omitted because these are only available during an explicit validation request and so they are not suitable for plugins to vary sanitization by.

### Arguments

* `null|bool $sanitized` - Whether sanitized; this is initially null, and changing it to bool causes the validation error to be forced.
* `array $error` - Validation error being sanitized.

### Source

:link: [includes/validation/class-amp-validation-error-taxonomy.php:522](/includes/validation/class-amp-validation-error-taxonomy.php#L522)

<details>
<summary>Show Code</summary>

```php
$sanitized = apply_filters( 'amp_validation_error_sanitized', null, $error );
```

</details>
