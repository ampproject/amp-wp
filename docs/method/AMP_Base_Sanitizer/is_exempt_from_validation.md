## Method `AMP_Base_Sanitizer::is_exempt_from_validation()`

> :warning: This method is deprecated: Use AmpProject\DevMode::isExemptFromValidation( $node ) instead.

```php
protected function is_exempt_from_validation( \DOMNode $node );
```

Check whether a certain node should be exempt from validation.

### Arguments

* `\DOMNode $node` - Node to check.

### Return value

`bool` - Whether the node should be exempt from validation.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:461](/includes/sanitizers/class-amp-base-sanitizer.php#L461-L464)

<details>
<summary>Show Code</summary>

```php
protected function is_exempt_from_validation( DOMNode $node ) {
	_deprecated_function( 'AMP_Base_Sanitizer::is_exempt_from_validation', '1.5', 'AmpProject\DevMode::isExemptFromValidation' );
	return DevMode::isExemptFromValidation( $node );
}
```

</details>
