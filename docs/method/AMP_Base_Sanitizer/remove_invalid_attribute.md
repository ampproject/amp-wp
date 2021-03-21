## Method `AMP_Base_Sanitizer::remove_invalid_attribute()`

```php
public function remove_invalid_attribute( $element, $attribute, $validation_error = array(), $attr_spec = array() );
```

Removes an invalid attribute of a node.

Also, calls the mutation callback for it. This tracks all the attributes that were removed.

### Arguments

* `\DOMElement $element` - The node for which to remove the attribute.
* `\DOMAttr|string $attribute` - The attribute to remove from the element.
* `array $validation_error` - Validation error details.
* `array $attr_spec` - Attribute spec.

### Return value

`bool` - Whether the node should have been removed, that is, that the node was sanitized for validity.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:505](/includes/sanitizers/class-amp-base-sanitizer.php#L505-L533)

<details>
<summary>Show Code</summary>

```php
public function remove_invalid_attribute( $element, $attribute, $validation_error = [], $attr_spec = [] ) {
	if ( DevMode::isExemptFromValidation( $element ) ) {
		return false;
	}
	if ( is_string( $attribute ) ) {
		$node = $element->getAttributeNode( $attribute );
	} else {
		$node = $attribute;
	}
	// Catch edge condition (no known possible way to reach).
	if ( ! ( $node instanceof DOMAttr ) || $element !== $node->parentNode ) {
		return false;
	}
	$should_remove = $this->should_sanitize_validation_error( $validation_error, compact( 'node' ) );
	if ( $should_remove ) {
		$allow_empty  = ! empty( $attr_spec[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] );
		$is_href_attr = ( isset( $attr_spec[ AMP_Rule_Spec::VALUE_URL ] ) && 'href' === $node->nodeName );
		if ( $allow_empty && ! $is_href_attr ) {
			$node->nodeValue = '';
		} else {
			$element->removeAttributeNode( $node );
		}
	}
	return $should_remove;
}
```

</details>
