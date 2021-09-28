## Method `AMP_DOM_Utils::get_content_from_dom_node()`

> :warning: This method is deprecated: Use Dom\Document-&gt;saveHtml( $node ) instead.

```php
static public function get_content_from_dom_node( Document $dom, $node );
```

Return valid HTML content extracted from the DOMNode passed as a parameter.

### Arguments

* `\AmpProject\Dom\Document $dom` - Represents an HTML document.
* `\DOMElement $node` - Represents an HTML element of the $dom from which to extract HTML content.

### Return value

`string` - Returns the HTML content represented in the DOMNode

### Source

:link: [includes/utils/class-amp-dom-utils.php:147](/includes/utils/class-amp-dom-utils.php#L147-L150)

<details>
<summary>Show Code</summary>

```php
public static function get_content_from_dom_node( Document $dom, $node ) {
	_deprecated_function( __METHOD__, '1.5.0', 'AmpProject\Dom\Document::saveHtml()' );
	return $dom->saveHTML( $node );
}
```

</details>
