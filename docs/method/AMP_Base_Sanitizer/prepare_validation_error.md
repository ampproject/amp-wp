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

:link: [includes/sanitizers/class-amp-base-sanitizer.php:578](/includes/sanitizers/class-amp-base-sanitizer.php#L578-L670)

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
		$is_inline_script = ( 'script' === $node->nodeName && ! $node->hasAttribute( 'src' ) );
		$is_inline_style  = ( 'style' === $node->nodeName && ! $node->hasAttribute( 'amp-custom' ) && ! $node->hasAttribute( 'amp-keyframes' ) );
		if ( $is_inline_script || $is_inline_style ) {
			$text_content = $node->textContent;
			if ( $is_inline_script ) {
				// For inline scripts, normalize string and number literals to prevent nonces, random numbers, and timestamps
				// from generating endless number of validation errors.
				$error['text'] = preg_replace(
					[
						// Regex credit to <https://stackoverflow.com/a/5696141/93579>.
						'/"[^"\\\\\n]*(?:\\\\.[^"\\\\\n]*)*"/s',
						'/\'[^\'\\\\\n]*(?:\\\\.[^\'\\\\\n]*)*\'/s',
						'/(\b|-)\d+\.\d+\b/',
						'/(\b|-)\d+\b/',
					],
					[
						'__DOUBLE_QUOTED_STRING__',
						'__SINGLE_QUOTED_STRING__',
						'__FLOAT__',
						'__INT__',
					],
					$text_content
				);
			} elseif ( $is_inline_style ) {
				// Include stylesheet text except for amp-custom and amp-keyframes since it is large and since it should
				// already be detailed in the stylesheets metabox.
				$error['text'] = $text_content;
			}
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
