## Method `AMP_DOM_Utils::get_dom_from_content()`

```php
static public function get_dom_from_content( $content, $encoding = null );
```

Return a valid Dom\Document representing arbitrary HTML content passed as a parameter.

### Arguments

* `string $content` - Valid HTML content to be represented by a Dom\Document.
* `string $encoding` - Optional. Encoding to use for the content. Defaults to `get_bloginfo( &#039;charset&#039; )`.

### Return value

`\AmpProject\Dom\Document|false` - Returns a DOM document, or false if conversion failed.

### Source

:link: [includes/utils/class-amp-dom-utils.php:171](/includes/utils/class-amp-dom-utils.php#L171-L188)

<details>
<summary>Show Code</summary>

```php
public static function get_dom_from_content( $content, $encoding = null ) {
	// Detect encoding from the current WordPress installation.
	if ( null === $encoding ) {
		$encoding = get_bloginfo( 'charset' );
	}
	/*
	 * Wrap in dummy tags, since XML needs one parent node.
	 * It also makes it easier to loop through nodes.
	 * We can later use this to extract our nodes.
	 */
	$document = "<html><head></head><body>{$content}</body></html>";
	$options                              = Options::DEFAULTS;
	$options[ Document\Option::ENCODING ] = $encoding;
	return Document::fromHtml( $document, $options );
}
```

</details>
