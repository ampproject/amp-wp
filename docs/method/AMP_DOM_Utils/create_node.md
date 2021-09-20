## Method `AMP_DOM_Utils::create_node()`

```php
static public function create_node( Document $dom, $tag, $attributes );
```

Create a new node w/attributes (a DOMElement) and add to the passed Dom\Document.

### Arguments

* `\AmpProject\Dom\Document $dom` - A representation of an HTML document to add the new node to.
* `string $tag` - A valid HTML element tag for the element to be added.
* `string[] $attributes` - One of more valid attributes for the new node.

### Return value

`\DOMElement|false` - The DOMElement for the given $tag, or false on failure

### Source

:link: [includes/utils/class-amp-dom-utils.php:163](/includes/utils/class-amp-dom-utils.php#L163-L168)

<details>
<summary>Show Code</summary>

```php
public static function create_node( Document $dom, $tag, $attributes ) {
	$node = $dom->createElement( $tag );
	self::add_attributes_to_node( $node, $attributes );
	return $node;
}
```

</details>
