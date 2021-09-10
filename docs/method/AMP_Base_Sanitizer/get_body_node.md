## Method `AMP_Base_Sanitizer::get_body_node()`

> :warning: This method is deprecated: Use $this-&gt;dom-&gt;body instead.

```php
protected function get_body_node();
```

Get HTML body as DOMElement from Dom\Document received by the constructor.

### Return value

`\DOMElement` - The body element.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:224](/includes/sanitizers/class-amp-base-sanitizer.php#L224-L227)

<details>
<summary>Show Code</summary>

```php
protected function get_body_node() {
	_deprecated_function( 'Use $this->dom->body instead', '1.5.0' );
	return $this->dom->body;
}
```

</details>
