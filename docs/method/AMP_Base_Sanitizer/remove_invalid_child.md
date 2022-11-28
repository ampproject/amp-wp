## Method `AMP_Base_Sanitizer::remove_invalid_child()`

```php
public function remove_invalid_child( $node, $validation_error = array() );
```

Removes an invalid child of a node.

Also, calls the mutation callback for it. This tracks all the nodes that were removed.

### Arguments

* `\DOMNode|\DOMElement $node` - The node to remove.
* `array $validation_error` - Validation error details.

### Return value

`bool` - Whether the node should have been removed, that is, that the node was sanitized for validity.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:571](/includes/sanitizers/class-amp-base-sanitizer.php#L571-L592)

<details>
<summary>Show Code</summary>

```php
public function remove_invalid_child( $node, $validation_error = [] ) {
	if ( DevMode::isExemptFromValidation( $node ) ) {
		return false;
	}
	if ( ValidationExemption::is_amp_unvalidated_for_node( $node ) || ValidationExemption::is_px_verified_for_node( $node ) ) {
		return false;
	}
	$should_remove = $this->should_sanitize_validation_error( $validation_error, compact( 'node' ) );
	if ( $should_remove ) {
		if ( null === $node->parentNode ) {
			// Node no longer exists.
			return $should_remove;
		}
		$node->parentNode->removeChild( $node );
	} else {
		ValidationExemption::mark_node_as_amp_unvalidated( $node );
	}
	return $should_remove;
}
```

</details>
