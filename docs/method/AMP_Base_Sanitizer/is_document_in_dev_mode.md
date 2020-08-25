## Method `AMP_Base_Sanitizer::is_document_in_dev_mode()`

> :warning: This function is deprecated: Use AmpProject\DevMode::isActiveForDocument( $document ) instead.

```php
protected function is_document_in_dev_mode();
```

Check whether the document of a given node is in dev mode.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:421](../../includes/sanitizers/class-amp-base-sanitizer.php#L421-L424)

<details>
<summary>Show Code</summary>

```php
protected function is_document_in_dev_mode() {
	_deprecated_function( 'AMP_Base_Sanitizer::is_document_in_dev_mode', '1.5', 'AmpProject\DevMode::isActiveForDocument' );
	return DevMode::isActiveForDocument( $this->dom );
}
```

</details>
