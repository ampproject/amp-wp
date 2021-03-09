## Method `AMP_DOM_Utils::is_node_empty()`

```php
static public function is_node_empty( $node );
```

Determines if a DOMElement&#039;s node is empty or not.

.

### Arguments

* `\DOMElement $node` - Represents an HTML element.

### Return value

`bool` - Returns true if the DOMElement has no child nodes and              the textContent property of the DOMElement is empty;              Otherwise it returns false.

### Source

:link: [includes/utils/class-amp-dom-utils.php:287](/includes/utils/class-amp-dom-utils.php#L287-L289)

<details>
<summary>Show Code</summary>

```php
public static function is_node_empty( $node ) {
	return false === $node->hasChildNodes() && empty( $node->textContent );
}
```

</details>
