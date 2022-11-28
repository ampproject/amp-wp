## Method `AMP_DOM_Utils::get_element_id()`

> :warning: This method is deprecated: Use AmpProject\Dom\Document::getElementId() instead.

```php
static public function get_element_id( $element, $prefix = 'amp-wp-id' );
```

Get the ID for an element.

If the element does not have an ID, create one first.

### Arguments

* `\DOMElement|\AmpProject\Dom\Element $element` - Element to get the ID for.
* `string $prefix` - Optional. Defaults to &#039;amp-wp-id&#039;.

### Return value

`string` - ID to use.

### Source

:link: [includes/utils/class-amp-dom-utils.php:321](/includes/utils/class-amp-dom-utils.php#L321-L329)

<details>
<summary>Show Code</summary>

```php
public static function get_element_id( $element, $prefix = 'amp-wp-id' ) {
	_deprecated_function(
		'AMP_DOM_Utils::get_element_id',
		'1.5.1',
		'Use AmpProject\Amp\Dom\Document::getElementId() instead'
	);
	return Document::fromNode( $element )->getElementId( $element, $prefix );
}
```

</details>
