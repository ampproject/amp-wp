<?php
/**
 * Class AMP_Style_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Style_Sanitizer
 *
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
	 * Get list of CSS styles in HTML content of DOMDocument ($this->dom).
	 *
	 * @since 0.4
	 *
	 * @return string[]
	 */
	public function get_styles() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}
		return $this->styles;
	}

	/**
	 * Sanitize CSS styles within the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.4
	 */
	public function sanitize() {
		$body = $this->get_body_node();
		$this->collect_styles_recursive( $body );
		$this->did_convert_elements = true;
	}

	/**
	 * Collect and store all CSS styles.
	 *
	 * Collects the CSS styles from within the HTML contained in this instance's DOMDocument.
	 *
	 * @see Retrieve array of styles using $this->get_styles() after calling this method.
	 *
	 * @since 0.4
	 *
	 * @note Uses recursion to traverse down the tree of DOMDocument nodes.
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
