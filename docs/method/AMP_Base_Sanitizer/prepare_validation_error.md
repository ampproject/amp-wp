## Method `AMP_Base_Sanitizer::prepare_validation_error()`

```php
public function prepare_validation_error( array $error = array(), array $data = array() );
```

Prepare validation error.

### Arguments

* `array $error` - {     Error.     @type string $code Error code. }
* `array $data` - {     Data.     @type DOMElement|DOMNode $node The removed node. }

### Return value

`array` - Error.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:567](/includes/sanitizers/class-amp-base-sanitizer.php#L567-L639)

<details>
<summary>Show Code</summary>

```php
public function prepare_validation_error( array $error = [], array $data = [] ) {
	$node = null;
	if ( isset( $data['node'] ) && $data['node'] instanceof DOMNode ) {
		$node = $data['node'];
		$error['node_name'] = $node->nodeName;
		if ( $node->parentNode ) {
			$error['parent_name'] = $node->parentNode->nodeName;
		}
	}
	if ( $node instanceof DOMElement ) {
		if ( ! isset( $error['code'] ) ) {
			$error['code'] = AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_TAG;
		}
		if ( ! isset( $error['type'] ) ) {
			// @todo Also include javascript: protocol for URL errors.
			$error['type'] = 'script' === $node->nodeName ? AMP_Validation_Error_Taxonomy::JS_ERROR_TYPE : AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE;
		}
		// @todo Change from node_attributes to element_attributes to harmonize the two.
		if ( ! isset( $error['node_attributes'] ) ) {
			$error['node_attributes'] = [];
			foreach ( $node->attributes as $attribute ) {
				$error['node_attributes'][ $attribute->nodeName ] = $attribute->nodeValue;
			}
		}
		// Capture element contents.
		if (
			( 'script' === $node->nodeName && ! $node->hasAttribute( 'src' ) )
			||
			// Include stylesheet text except for amp-custom and amp-keyframes since it is large and since it should
			// already be detailed in the stylesheets metabox.
			( 'style' === $node->nodeName && ! $node->hasAttribute( 'amp-custom' ) && ! $node->hasAttribute( 'amp-keyframes' ) )
		) {
			$error['text'] = $node->textContent;
		}
		// Suppress 'ver' param from enqueued scripts and styles.
		if ( 'script' === $node->nodeName && isset( $error['node_attributes']['src'] ) && false !== strpos( $error['node_attributes']['src'], 'ver=' ) ) {
			$error['node_attributes']['src'] = add_query_arg( 'ver', '__normalized__', $error['node_attributes']['src'] );
		} elseif ( 'link' === $node->nodeName && isset( $error['node_attributes']['href'] ) && false !== strpos( $error['node_attributes']['href'], 'ver=' ) ) {
			$error['node_attributes']['href'] = add_query_arg( 'ver', '__normalized__', $error['node_attributes']['href'] );
		}
	} elseif ( $node instanceof DOMAttr ) {
		if ( ! isset( $error['code'] ) ) {
			$error['code'] = AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_ATTR;
		}
		if ( ! isset( $error['type'] ) ) {
			// If this is an attribute that begins with on, like onclick, it should be a js_error.
			$error['type'] = preg_match( '/^on\w+/', $node->nodeName ) ? AMP_Validation_Error_Taxonomy::JS_ERROR_TYPE : AMP_Validation_Error_Taxonomy::HTML_ATTRIBUTE_ERROR_TYPE;
		}
		if ( ! isset( $error['element_attributes'] ) ) {
			$error['element_attributes'] = [];
			if ( $node->parentNode && $node->parentNode->hasAttributes() ) {
				foreach ( $node->parentNode->attributes as $attribute ) {
					$error['element_attributes'][ $attribute->nodeName ] = $attribute->nodeValue;
				}
			}
		}
	} elseif ( $node instanceof DOMProcessingInstruction ) {
		$error['text'] = trim( $node->data, '?' );
	}
	if ( ! isset( $error['node_type'] ) ) {
		$error['node_type'] = $node->nodeType;
	}
	return $error;
}
```

</details>
