## Method `AMP_Base_Sanitizer::has_dev_mode_exemption()`

```php
protected function has_dev_mode_exemption( \DOMNode $node );
```

Check whether a node is exempt from validation during dev mode.

### Arguments

* `\DOMNode $node` - Node to check.

### Source

[includes/sanitizers/class-amp-base-sanitizer.php:435](https://github.com/ampproject/amp-wp/blob/develop/includes/sanitizers/class-amp-base-sanitizer.php#L435-L438)

<details>
<summary>Show Code</summary>
```php
protected function has_dev_mode_exemption( DOMNode $node ) {
	_deprecated_function( 'AMP_Base_Sanitizer::has_dev_mode_exemption', '1.5', 'AmpProject\DevMode::hasExemptionForNode' );
	return DevMode::hasExemptionForNode( $node );
}
```
</details>
