## Method `AMP_DOM_Utils::get_content_from_dom()`

```php
static public function get_content_from_dom( Document $dom );
```

Return valid HTML *body* content extracted from the Dom\Document passed as a parameter.

### Arguments

* `\AmpProject\Dom\Document $dom` - Represents an HTML document from which to extract HTML content.

### Return value

`string` - Returns the HTML content of the body element represented in the Dom\Document.

### Source

:link: [includes/utils/class-amp-dom-utils.php:126](/includes/utils/class-amp-dom-utils.php#L126-L132)

<details>
<summary>Show Code</summary>

```php
public static function get_content_from_dom( Document $dom ) {
	return preg_replace(
		static::HTML_BODY_CONTENTS_PATTERN,
		'$1',
		$dom->saveHTML( $dom->body )
	);
}
```

</details>
