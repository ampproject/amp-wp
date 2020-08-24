## Method `AMP_Base_Sanitizer::get_body_node()`

```php
protected function get_body_node();
```

Get HTML body as DOMElement from Dom\Document received by the constructor.

### Source

[includes/sanitizers/class-amp-base-sanitizer.php:212](https://github.com/ampproject/amp-wp/blob/develop/includes/sanitizers/class-amp-base-sanitizer.php#L212-L215)

<details>
<summary>Show Code</summary>
```php
protected function get_body_node() {
	_deprecated_function( 'Use $this->dom->body instead', '1.5.0' );
	return $this->dom->body;
}
```
</details>
