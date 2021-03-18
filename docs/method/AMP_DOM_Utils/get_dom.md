## Method `AMP_DOM_Utils::get_dom()`

> :warning: This method is deprecated: Use AmpProject\Dom\Document::fromHtml( $html, $encoding ) instead.

```php
static public function get_dom( $document, $encoding = null );
```

Return a valid Dom\Document representing HTML document passed as a parameter.

### Arguments

* `string $document` - Valid HTML document to be represented by a Dom\Document.
* `string $encoding` - Optional. Encoding to use for the content.

### Return value

`\AmpProject\Dom\Document|false` - Returns Dom\Document, or false if conversion failed.

### Source

:link: [includes/utils/class-amp-dom-utils.php:66](/includes/utils/class-amp-dom-utils.php#L66-L69)

<details>
<summary>Show Code</summary>

```php
public static function get_dom( $document, $encoding = null ) {
	_deprecated_function( __METHOD__, '1.5.0', 'AmpProject\Dom\Document::fromHtml()' );
	return Document::fromHtml( $document, $encoding );
}
```

</details>
