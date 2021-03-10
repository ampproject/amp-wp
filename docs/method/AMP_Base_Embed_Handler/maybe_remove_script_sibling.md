## Method `AMP_Base_Embed_Handler::maybe_remove_script_sibling()`

```php
protected function maybe_remove_script_sibling( \DOMElement $node, callable $match_callback );
```

Removes the node&#039;s nearest `&lt;script&gt;` sibling with a `src` attribute containing the base `src` URL provided.

### Arguments

* `\DOMElement $node` - The DOMNode to whose sibling is the script to be removed.
* `callable $match_callback` - Callback which is passed the script element to determine if it is a match.

### Source

:link: [includes/embeds/class-amp-base-embed-handler.php:165](/includes/embeds/class-amp-base-embed-handler.php#L165-L202)

<details>
<summary>Show Code</summary>

```php
protected function maybe_remove_script_sibling( DOMElement $node, callable $match_callback ) {
	$next_element_sibling = $node->nextSibling;
	while ( $next_element_sibling && ! $next_element_sibling instanceof DOMElement ) {
		$next_element_sibling = $next_element_sibling->nextSibling;
	}
	if ( ! $next_element_sibling instanceof DOMElement ) {
		return;
	}
	// Handle case where script is immediately following.
	if ( Tag::SCRIPT === $next_element_sibling->tagName && $match_callback( $next_element_sibling ) ) {
		$next_element_sibling->parentNode->removeChild( $next_element_sibling );
		return;
	}
	// Handle case where script is wrapped in paragraph by wpautop.
	if ( 'p' === $next_element_sibling->tagName ) {
		/** @var DOMElement[] $children_elements */
		$children_elements = array_values(
			array_filter(
				iterator_to_array( $next_element_sibling->childNodes ),
				static function ( DOMNode $child ) {
					return $child instanceof DOMElement;
				}
			)
		);
		if (
			1 === count( $children_elements )
			&&
			Tag::SCRIPT === $children_elements[0]->tagName
			&&
			$match_callback( $children_elements[0] )
		) {
			$next_element_sibling->parentNode->removeChild( $next_element_sibling );
		}
	}
}
```

</details>
