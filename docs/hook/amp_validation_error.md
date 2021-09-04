## Filter `amp_validation_error`

```php
apply_filters( 'amp_validation_error', $error, $context );
```

Filters the validation error array.

This allows plugins to add amend additional properties which can help with more accurately identifying a validation error beyond the name of the parent node and the element&#039;s attributes. The $sources are also omitted because these are only available during an explicit validation request and so they are not suitable for plugins to vary sanitization by. If looking to force a validation error to be ignored, use the &#039;amp_validation_error_sanitized&#039; filter instead of attempting to return an empty value with this filter (as that is not supported).

### Arguments

* `array $error` - Validation error to be printed.
* `array $context` - {     Context data for validation error sanitization.     @type DOMNode $node Node for which the validation error is being reported. May be null. }

### Source

:link: [includes/validation/class-amp-validation-manager.php:650](/includes/validation/class-amp-validation-manager.php#L650)

<details>
<summary>Show Code</summary>

```php
$error = apply_filters( 'amp_validation_error', $error, compact( 'node' ) );
```

</details>
