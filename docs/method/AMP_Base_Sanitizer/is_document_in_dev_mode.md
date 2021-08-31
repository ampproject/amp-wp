## Method `AMP_Base_Sanitizer::is_document_in_dev_mode()`

> :warning: This method is deprecated: Use AmpProject\DevMode::isActiveForDocument( $document ) instead.

```php
protected function is_document_in_dev_mode();
```

Check whether the document of a given node is in dev mode.

### Return value

`bool` - Whether the document is in dev mode.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:433](/includes/sanitizers/class-amp-base-sanitizer.php#L433-L436)

<details>
<summary>Show Code</summary>

```php
protected function is_document_in_dev_mode() {
	_deprecated_function( 'AMP_Base_Sanitizer::is_document_in_dev_mode', '1.5', 'AmpProject\DevMode::isActiveForDocument' );
	return DevMode::isActiveForDocument( $this->dom );
}
```

</details>
