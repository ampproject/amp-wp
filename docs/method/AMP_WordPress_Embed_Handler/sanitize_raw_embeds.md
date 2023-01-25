## Method `AMP_WordPress_Embed_Handler::sanitize_raw_embeds()`

```php
public function sanitize_raw_embeds( Document $dom );
```

Sanitize WordPress embed raw embeds.

### Arguments

* `\AmpProject\Dom\Document $dom` - Document.

### Return value

`void`

### Source

:link: [includes/embeds/class-amp-wordpress-embed-handler.php:63](/includes/embeds/class-amp-wordpress-embed-handler.php#L63-L103)

<details>
<summary>Show Code</summary>

```php
public function sanitize_raw_embeds( Document $dom ) {
	$embed_iframes = $dom->xpath->query( '//iframe[ @src and contains( concat( " ", normalize-space( @class ), " " ), " wp-embedded-content " ) ]', $dom->body );
	foreach ( $embed_iframes as $embed_iframe ) {
		/** @var Element $embed_iframe */
		// Remove embed script included when user copies HTML Embed code, per get_post_embed_html().
		$embed_script = $dom->xpath->query( './following-sibling::script[ contains( text(), "wp.receiveEmbedMessage" ) ]', $embed_iframe )->item( 0 );
		if ( $embed_script instanceof Element ) {
			$embed_script->parentNode->removeChild( $embed_script );
		}
		// If the post embed iframe got wrapped in a paragraph by `wpautop()`, unwrap it. This happens not with
		// the Embed block but it does with the [embed] shortcode.
		$is_wrapped_in_paragraph = (
			$embed_iframe->parentNode instanceof Element
			&&
			Tag::P === $embed_iframe->parentNode->tagName
		);
		// If the iframe is wrapped in a paragraph, but it's not the only node, then abort.
		if ( $is_wrapped_in_paragraph && 1 !== $embed_iframe->parentNode->childNodes->length ) {
			continue;
		}
		$embed_blockquote = $dom->xpath->query(
			'./preceding-sibling::blockquote[ contains( concat( " ", normalize-space( @class ), " " ), " wp-embedded-content " ) ]',
			$is_wrapped_in_paragraph ? $embed_iframe->parentNode : $embed_iframe
		)->item( 0 );
		if ( $embed_blockquote instanceof Element ) {
			// Note that unwrap_p_element() is not being used here because it will do nothing if the paragraph
			// happens to have an attribute on it, which is possible with the_content filters.
			if ( $is_wrapped_in_paragraph && $embed_iframe->parentNode->parentNode instanceof Element ) {
				$embed_iframe->parentNode->parentNode->replaceChild( $embed_iframe, $embed_iframe->parentNode );
			}
			$this->create_amp_wordpress_embed_and_replace_node( $dom, $embed_blockquote, $embed_iframe );
		}
	}
}
```

</details>
