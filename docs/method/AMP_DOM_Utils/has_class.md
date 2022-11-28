## Method `AMP_DOM_Utils::has_class()`

```php
static public function has_class( \DOMElement $element, $class );
```

Check whether a given element has a specific class.

### Arguments

* `\DOMElement $element` - Element to check the classes of.
* `string $class` - Class to check for.

### Return value

`bool` - Whether the element has the requested class.

### Source

:link: [includes/utils/class-amp-dom-utils.php:297](/includes/utils/class-amp-dom-utils.php#L297-L305)

<details>
<summary>Show Code</summary>

```php
public static function has_class( DOMElement $element, $class ) {
	if ( ! $element->hasAttribute( 'class' ) ) {
		return false;
	}
	$classes = $element->getAttribute( 'class' );
	return in_array( $class, preg_split( '/\s/', $classes ), true );
}
```

</details>
