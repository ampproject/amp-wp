## Method `AMP_Base_Sanitizer::has_dev_mode_exemption()`

> :warning: This method is deprecated: Use AmpProject\DevMode::hasExemptionForNode( $node ) instead.

```php
protected function has_dev_mode_exemption( \DOMNode $node );
```

Check whether a node is exempt from validation during dev mode.

### Arguments

* `\DOMNode $node` - Node to check.

### Return value

`bool` - Whether the node should be exempt during dev mode.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:448](/includes/sanitizers/class-amp-base-sanitizer.php#L448-L451)

<details>
<summary>Show Code</summary>

```php
protected function has_dev_mode_exemption( DOMNode $node ) {
	_deprecated_function( 'AMP_Base_Sanitizer::has_dev_mode_exemption', '1.5', 'AmpProject\DevMode::hasExemptionForNode' );
	return DevMode::hasExemptionForNode( $node );
}
```

</details>
