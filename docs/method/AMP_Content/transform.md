## Method `AMP_Content::transform()`

```php
private function transform();
```

Transform.

### Source

:link: [includes/templates/class-amp-content.php:127](../../includes/templates/class-amp-content.php#L127-L139)

<details>
<summary>Show Code</summary>

```php
private function transform() {
	$content = $this->content;
	// First, embeds + the_content filter.
	/** This filter is documented in wp-includes/post-template.php */
	$content = apply_filters( 'the_content', $content );
	$this->unregister_embed_handlers( $this->embed_handlers );
	// Then, sanitize to strip and/or convert non-amp content.
	$content = $this->sanitize( $content );
	$this->amp_content = $content;
}
```

</details>
