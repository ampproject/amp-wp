## Method `AMP_DOM_Utils::copy_attributes()`

```php
static public function copy_attributes( $attributes, \DOMElement $from, \DOMElement $to, $default_separator = ',' );
```

Copy one or more attributes from one element to the other.

### Arguments

* `array|string $attributes` - Attribute name or array of attribute names to copy.
* `\DOMElement $from` - DOM element to copy the attributes from.
* `\DOMElement $to` - DOM element to copy the attributes to.
* `string $default_separator` - Default separator to use for multiple values if the attribute is not known.

### Source

:link: [includes/utils/class-amp-dom-utils.php:491](/includes/utils/class-amp-dom-utils.php#L491-L510)

<details>
<summary>Show Code</summary>

```php
public static function copy_attributes( $attributes, DOMElement $from, DOMElement $to, $default_separator = ',' ) {
	foreach ( (array) $attributes as $attribute ) {
		if ( $from->hasAttribute( $attribute ) ) {
			$values = $from->getAttribute( $attribute );
			if ( $to->hasAttribute( $attribute ) ) {
				switch ( $attribute ) {
					case 'on':
						$values = self::merge_amp_actions( $to->getAttribute( $attribute ), $values );
						break;
					case 'class':
						$values = $to->getAttribute( $attribute ) . ' ' . $values;
						break;
					default:
						$values = $to->getAttribute( $attribute ) . $default_separator . $values;
				}
			}
			$to->setAttribute( $attribute, $values );
		}
	}
}
```

</details>
