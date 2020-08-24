## Method `AMP_Base_Sanitizer::is_document_in_dev_mode()`

```php
protected function is_document_in_dev_mode();
```

Check whether the document of a given node is in dev mode.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:420](https://github.com/ampproject/amp-wp/blob/develop/includes/sanitizers/class-amp-base-sanitizer.php#L420-L423)

<details>
<summary>Show Code</summary>

```php
protected function is_document_in_dev_mode() {
	_deprecated_function( 'AMP_Base_Sanitizer::is_document_in_dev_mode', '1.5', 'AmpProject\DevMode::isActiveForDocument' );
	return DevMode::isActiveForDocument( $this->dom );
}
```

</details>
