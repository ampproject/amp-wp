## Method `AMP_DOM_Utils::get_amp_bind_placeholder_prefix()`

> :warning: This method is deprecated: Use AmpProject\Dom\Document::AMP_BIND_DATA_ATTR_PREFIX instead.

```php
static public function get_amp_bind_placeholder_prefix();
```

Get attribute prefix for converted amp-bind attributes.

This contains a random string to prevent HTML content containing this data- attribute originally from being mutated to contain an amp-bind attribute when attributes are restored.

### Return value

`string` - HTML5 data-* attribute name prefix for AMP binding attributes.

### Source

:link: [includes/utils/class-amp-dom-utils.php:102](/includes/utils/class-amp-dom-utils.php#L102-L105)

<details>
<summary>Show Code</summary>

```php
public static function get_amp_bind_placeholder_prefix() {
	_deprecated_function( __METHOD__, '1.2.1' );
	return Document::AMP_BIND_DATA_ATTR_PREFIX;
}
```

</details>
