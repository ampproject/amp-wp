## Method `AMP_Base_Sanitizer::filter_attachment_layout_attributes()`

```php
public function filter_attachment_layout_attributes( $node, $new_attributes, $layout );
```

Set attributes to node&#039;s parent element according to layout.

### Arguments

* `\DOMElement $node` - Node.
* `array $new_attributes` - Attributes array.
* `string $layout` - Layout.

### Return value

`array` - New attributes.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:735](/includes/sanitizers/class-amp-base-sanitizer.php#L735-L761)

<details>
<summary>Show Code</summary>

```php
public function filter_attachment_layout_attributes( $node, $new_attributes, $layout ) {
	// The width has to be unset / auto in case of fixed-height.
	if ( 'fixed-height' === $layout && $node->parentNode instanceof DOMElement ) {
		if ( ! isset( $new_attributes['height'] ) ) {
			$new_attributes['height'] = self::FALLBACK_HEIGHT;
		}
		$new_attributes['width'] = 'auto';
		$node->parentNode->setAttribute( 'style', 'height: ' . $new_attributes['height'] . 'px; width: auto;' );
		// The parent element should have width/height set and position set in case of 'fill'.
	} elseif ( 'fill' === $layout && $node->parentNode instanceof DOMElement ) {
		if ( ! isset( $new_attributes['height'] ) ) {
			$new_attributes['height'] = self::FALLBACK_HEIGHT;
		}
		$node->parentNode->setAttribute( 'style', 'position:relative; width: 100%; height: ' . $new_attributes['height'] . 'px;' );
		unset( $new_attributes['width'], $new_attributes['height'] );
	} elseif ( 'responsive' === $layout && $node->parentNode instanceof DOMElement ) {
		$node->parentNode->setAttribute( 'style', 'position:relative; width: 100%; height: auto' );
	} elseif ( 'fixed' === $layout ) {
		if ( ! isset( $new_attributes['height'] ) ) {
			$new_attributes['height'] = self::FALLBACK_HEIGHT;
		}
	}
	return $new_attributes;
}
```

</details>
