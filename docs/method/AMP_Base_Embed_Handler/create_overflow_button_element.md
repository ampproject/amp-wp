## Method `AMP_Base_Embed_Handler::create_overflow_button_element()`

```php
protected function create_overflow_button_element( Document $dom, $text = null );
```

Create overflow button element.

### Arguments

* `\AmpProject\Dom\Document $dom` - Document.
* `string $text` - Button text (optional).

### Return value

`\AmpProject\Dom\Element` - Button element.

### Source

:link: [includes/embeds/class-amp-base-embed-handler.php:214](/includes/embeds/class-amp-base-embed-handler.php#L214-L223)

<details>
<summary>Show Code</summary>

```php
protected function create_overflow_button_element( Document $dom, $text = null ) {
	if ( ! $text ) {
		$text = __( 'See more', 'amp' );
	}
	$overflow = $dom->createElement( Tag::BUTTON );
	$overflow->setAttributeNode( $dom->createAttribute( Attribute::OVERFLOW ) );
	$overflow->setAttribute( Attribute::TYPE, 'button' );
	$overflow->textContent = $text;
	return $overflow;
}
```

</details>
