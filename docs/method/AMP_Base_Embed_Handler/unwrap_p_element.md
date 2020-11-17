## Method `AMP_Base_Embed_Handler::unwrap_p_element()`

```php
protected function unwrap_p_element( \DOMElement $node );
```

Replace an element&#039;s parent with itself if the parent is a &lt;p&gt; tag which has no attributes and has no other children.

This usually happens while `wpautop()` processes the element.

### Arguments

* `\DOMElement $node` - Node.

### Source

:link: [includes/embeds/class-amp-base-embed-handler.php:140](/includes/embeds/class-amp-base-embed-handler.php#L140-L153)

<details>
<summary>Show Code</summary>

```php
protected function unwrap_p_element( DOMElement $node ) {
	$parent_node = $node->parentNode;
	if (
		$parent_node instanceof DOMElement
		&&
		'p' === $parent_node->tagName
		&&
		false === $parent_node->hasAttributes()
		&&
		1 === count( $this->get_child_elements( $parent_node ) )
	) {
		$parent_node->parentNode->replaceChild( $node, $parent_node );
	}
}
```

</details>
