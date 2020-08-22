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

