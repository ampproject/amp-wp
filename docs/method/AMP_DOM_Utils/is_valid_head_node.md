## Method `AMP_DOM_Utils::is_valid_head_node()`

> :warning: This method is deprecated: Use AmpProject\Dom\Document-&gt;isValidHeadNode() instead.

```php
static public function is_valid_head_node( \DOMNode $node );
```

Determine whether a node can be in the head.

### Arguments

* `\DOMNode $node` - Node.

### Return value

`bool` - Whether valid head node.

### Source

:link: [includes/utils/class-amp-dom-utils.php:82](/includes/utils/class-amp-dom-utils.php#L82-L85)

<details>
<summary>Show Code</summary>

```php
public static function is_valid_head_node( DOMNode $node ) {
	_deprecated_function( __METHOD__, '1.5.0', 'AmpProject\Dom\Document->isValidHeadNode()' );
	return Document::fromNode( $node )->isValidHeadNode( $node );
}
```

</details>
