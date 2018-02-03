<?php
/**
 * Class AMP_Style_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Style_Sanitizer
 *
 * @todo This needs to also run on the CSS that is gathered for amp-custom.
 * Collects inline styles and outputs them in the amp-custom stylesheet.
 */
class AMP_Style_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Styles.
	 *
	 * @var string[] List of CSS styles in HTML content of DOMDocument ($this->dom).
	 *
	 * @since 0.4
	 */
	private $styles = array();

	/**
	 * Stylesheets.
	 *
	 * Values are the CSS stylesheets. Keys are MD5 hashes of the stylesheets
	 *
	 * @since 0.7
	 * @var string[]
	 */
	private $stylesheets = array();

	/**
	 * Get list of CSS styles in HTML content of DOMDocument ($this->dom).
	 *
	 * @since 0.4
	 *
	 * @return string[] Mapping CSS selectors to array of properties, or mapping of keys starting with 'stylesheet:' with value being the stylesheet.
	 */
	public function get_styles() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}
		return $this->styles;
	}

	/**
	 * Get stylesheets.
	 *
	 * @since 0.7
	 * @returns array Values are the CSS stylesheets. Keys are MD5 hashes of the stylesheets.
	 */
	public function get_stylesheets() {
		return array_merge( parent::get_stylesheets(), $this->stylesheets );
	}

	/**
	 * Sanitize CSS styles within the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.4
	 */
	public function sanitize() {
		$body = $this->root_element;

		$this->collect_style_elements();

		$this->collect_styles_recursive( $body );
		$this->did_convert_elements = true;
	}

	/**
	 * Collect and sanitize all style elements.
	 */
	public function collect_style_elements() {
		$style_elements  = $this->dom->getElementsByTagName( 'style' );
		$nodes_to_remove = array();

		foreach ( $style_elements as $style_element ) {
			/**
			 * Style element.
			 *
			 * @var DOMElement $style_element
			 */

			if ( 'head' === $style_element->parentNode->nodeName && ( $style_element->hasAttribute( 'amp-boilerplate' ) || $style_element->hasAttribute( 'amp-custom' ) ) ) {
				continue;
			}

			if ( 'body' === $style_element->parentNode->nodeName && $style_element->hasAttribute( 'amp-keyframes' ) ) {
				$validity = $this->validate_amp_keyframe( $style_element );
				if ( true === $validity ) {
					continue;
				}
			}

			$nodes_to_remove[] = $style_element;

			// @todo This should perhaps be done in document order to ensure proper cascade.
			$rules = trim( $style_element->textContent );

			// @todo This needs proper CSS parser, and de-duplication with \AMP_Style_Sanitizer::filter_style().
			$rules = preg_replace( '/\s*!important\s*(?=\s*;|})/', '', $rules );
			$rules = preg_replace( '/overflow\s*:\s*(auto|scroll)\s*;?\s*/', '', $rules );

			$this->stylesheets[ md5( $rules ) ] = $rules;
		}

		foreach ( $nodes_to_remove as $node_to_remove ) {
			$node_to_remove->parentNode->removeChild( $node_to_remove );
		}
	}

	/**
	 * Validate amp-keyframe style.
	 *
	 * @since 0.7
	 * @link https://github.com/ampproject/amphtml/blob/b685a0780a7f59313666225478b2b79b463bcd0b/validator/validator-main.protoascii#L1002-L1043
	 *
	 * @param DOMElement $style Style element.
	 * @return true|WP_Error Validity.
	 */
	private function validate_amp_keyframe( $style ) {
		if ( strlen( $style->textContent ) > 500000 ) {
			return new WP_Error( 'max_bytes' );
		}

		// This logic could be in AMP_Tag_And_Attribute_Sanitizer, but since it only applies to amp-keyframes it seems unnecessary.
		$next_sibling = $style->nextSibling;
		while ( $next_sibling ) {
			if ( $next_sibling instanceof DOMElement ) {
				return new WP_Error( 'mandatory_last_child' );
			}
			$next_sibling = $next_sibling->nextSibling;
		}

		// @todo Also add validation of the CSS spec itself.
		return true;
	}

	/**
	 * Collect and store all CSS style attributes.
	 *
	 * Collects the CSS styles from within the HTML contained in this instance's DOMDocument.
	 *
	 * @see Retrieve array of styles using $this->get_styles() after calling this method.
	 *
	 * @since 0.4
	 *
	 * @note Uses recursion to traverse down the tree of DOMDocument nodes.
	 * @todo This could use XPath to more efficiently find all elements with style attributes.
	 *
	 * @param DOMNode $node Node.
	 */
	private function collect_styles_recursive( $node ) {
		if ( XML_ELEMENT_NODE !== $node->nodeType ) {
			return;
		}

		if ( $node->hasAttributes() && $node instanceof DOMElement ) {
			$style = $node->getAttribute( 'style' );
			$class = $node->getAttribute( 'class' );

			if ( $style ) {
				$style = $this->process_style( $style );
				if ( ! empty( $style ) ) {
					$class_name = $this->generate_class_name( $style );
					$new_class  = trim( $class . ' ' . $class_name );

					$node->setAttribute( 'class', $new_class );
					$this->styles[ '.' . $class_name ] = $style;
				}
				$node->removeAttribute( 'style' );
			}
		}

		$length = $node->childNodes->length;
		for ( $i = $length - 1; $i >= 0; $i -- ) {
			$child_node = $node->childNodes->item( $i );
			$this->collect_styles_recursive( $child_node );
		}
	}

	/**
	 * Sanitize and convert individual styles.
	 *
	 * @since 0.4
	 *
	 * @param string $string Style string.
	 * @return array
	 */
	private function process_style( $string ) {

		/**
		 * Filter properties
		 */
		$string = safecss_filter_attr( esc_html( $string ) );

		if ( ! $string ) {
			return array();
		}

		/*
		 * safecss returns a string but we want individual rules.
		 * Use preg_split to break up rules by `;` but only if the
		 * semi-colon is not inside parens (like a data-encoded image).
		 */
		$styles = array_map( 'trim', preg_split( '/;(?![^(]*\))/', $string ) );

		// Normalize the order of the styles.
		sort( $styles );

		$processed_styles = array();

		// Normalize whitespace and filter rules.
		foreach ( $styles as $index => $rule ) {
			$arr2 = array_map( 'trim', explode( ':', $rule, 2 ) );
			if ( 2 !== count( $arr2 ) ) {
				continue;
			}

			list( $property, $value ) = $this->filter_style( $arr2[0], $arr2[1] );
			if ( empty( $property ) || empty( $value ) ) {
				continue;
			}

			$processed_styles[ $index ] = "{$property}:{$value}";
		}

		return $processed_styles;
	}

	/**
	 * Filter individual CSS name/value pairs.
	 *
	 *   - Remove overflow if value is `auto` or `scroll`
	 *   - Change `width` to `max-width`
	 *   - Remove !important
	 *
	 * @since 0.4
	 *
	 * @param string $property Property.
	 * @param string $value    Value.
	 * @return array
	 */
	private function filter_style( $property, $value ) {

		/**
		 * Remove overflow if value is `auto` or `scroll`; not allowed in AMP
		 *
		 * @todo This removal needs to be reported.
		 * @see https://www.ampproject.org/docs/reference/spec.html#properties
		 */
		if ( preg_match( '#^overflow#i', $property ) && preg_match( '#^(auto|scroll)$#i', $value ) ) {
			return array( false, false );
		}

		if ( 'width' === $property ) {
			$property = 'max-width';
		}

		/**
		 * Remove `!important`; not allowed in AMP
		 *
		 * @todo This removal needs to be reported.
		 */
		if ( false !== strpos( $value, 'important' ) ) {
			$value = preg_replace( '/\s*\!\s*important$/', '', $value );
		}

		return array( $property, $value );
	}

	/**
	 * Generate a unique class name
	 *
	 * Use the md5() of the $data parameter
	 *
	 * @since 0.4
	 *
	 * @param string $data Data.
	 * @return string Class name.
	 */
	private function generate_class_name( $data ) {
		$string = maybe_serialize( $data );
		return 'amp-wp-inline-' . md5( $string );
	}
}
