## Method `AMP_Base_Sanitizer::clean_up_after_attribute_removal()`

```php
protected function clean_up_after_attribute_removal( $element, $attribute );
```

Cleans up artifacts after the removal of an attribute node.

### Arguments

* `\DOMElement $element` - The node for which the attribute was removed.
* `\DOMAttr $attribute` - The attribute that was removed.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:649](/includes/sanitizers/class-amp-base-sanitizer.php#L649-L664)

<details>
<summary>Show Code</summary>

```php
protected function clean_up_after_attribute_removal( $element, $attribute ) {
	static $attributes_tied_to_href = [ 'target', 'download', 'rel', 'rev', 'hreflang', 'type' ];
	if ( 'href' === $attribute->nodeName ) {
		/*
		 * "The target, download, rel, rev, hreflang, and type attributes must be omitted
		 * if the href attribute is not present."
		 * See: https://www.w3.org/TR/2016/REC-html51-20161101/textlevel-semantics.html#the-a-element
		 */
		foreach ( $attributes_tied_to_href as $attribute_to_remove ) {
			if ( $element->hasAttribute( $attribute_to_remove ) ) {
				$element->removeAttribute( $attribute_to_remove );
			}
		}
	}
}
```

</details>
