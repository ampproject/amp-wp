## Method `AMP_Content::sanitize()`

```php
private function sanitize( $content );
```

Sanitize.

### Arguments

* `string $content` - Content.

### Source

:link: [includes/templates/class-amp-content.php:214](../../includes/templates/class-amp-content.php#L214-L223)

<details>
<summary>Show Code</summary>

```php
private function sanitize( $content ) {
	$dom = AMP_DOM_Utils::get_dom_from_content( $content );
	$results = AMP_Content_Sanitizer::sanitize_document( $dom, $this->sanitizer_classes, $this->args );
	$this->add_scripts( $results['scripts'] );
	$this->add_stylesheets( $results['stylesheets'] );
	return AMP_DOM_Utils::get_content_from_dom( $dom );
}
```

</details>
