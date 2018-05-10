<?php
/**
 * Class AMP_Base_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Base_Sanitizer
 */
abstract class AMP_Base_Sanitizer {

	/**
	 * Value used with the height attribute in an $attributes parameter is empty.
	 *
	 * @since 0.3.3
	 *
	 * @const int
	 */
	const FALLBACK_HEIGHT = 400;

	/**
	 * Placeholder for default args, to be set in child classes.
	 *
	 * @since 0.2
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = array();

	/**
	 * DOM.
	 *
	 * @var DOMDocument A standard PHP representation of an HTML document in object form.
	 *
	 * @since 0.2
	 */
	protected $dom;

	/**
	 * Array of flags used to control sanitization.
	 *
	 * @var array {
	 *      @type int $content_max_width
	 *      @type bool $add_placeholder
	 *      @type bool $use_document_element
	 *      @type bool $require_https_src
	 *      @type string[] $amp_allowed_tags
	 *      @type string[] $amp_globally_allowed_attributes
	 *      @type string[] $amp_layout_allowed_attributes
	 *      @type array $amp_allowed_tags
	 *      @type array $amp_globally_allowed_attributes
	 *      @type array $amp_layout_allowed_attributes
	 *      @type array $amp_bind_placeholder_prefix
	 *      @type bool $allow_dirty_styles
	 *      @type bool $allow_dirty_scripts
	 *      @type bool $disable_invalid_removal
	 *      @type callable $remove_invalid_callback
	 * }
	 */
	protected $args;

	/**
	 * Flag to be set in child class' sanitize() method indicating if the
	 * HTML contained in the DOMDocument has been sanitized yet or not.
	 *
	 * @since 0.2
	 *
	 * @var bool
	 */
	protected $did_convert_elements = false;

	/**
	 * The root element used for sanitization. Either html or body.
	 *
	 * @var DOMElement
	 */
	protected $root_element;

	/**
	 * AMP_Base_Sanitizer constructor.
	 *
	 * @since 0.2
	 *
	 * @param DOMDocument $dom Represents the HTML document to sanitize.
	 * @param array       $args {
	 *      Args.
	 *
	 *      @type int $content_max_width
	 *      @type bool $add_placeholder
	 *      @type bool $require_https_src
	 *      @type string[] $amp_allowed_tags
	 *      @type string[] $amp_globally_allowed_attributes
	 *      @type string[] $amp_layout_allowed_attributes
	 * }
	 */
	public function __construct( $dom, $args = array() ) {
		$this->dom  = $dom;
		$this->args = array_merge( $this->DEFAULT_ARGS, $args );

		if ( ! empty( $this->args['use_document_element'] ) ) {
			$this->root_element = $this->dom->documentElement;
		} else {
			$this->root_element = $this->dom->getElementsByTagName( 'body' )->item( 0 );
		}
	}

	/**
	 * Sanitize the HTML contained in the DOMDocument received by the constructor
	 */
	abstract public function sanitize();

	/**
	 * Return array of values that would be valid as an HTML `script` element.
	 *
	 * Array keys are AMP element names and array values are their respective
	 * Javascript URLs from https://cdn.ampproject.org
	 *
	 * @since 0.2
	 *
	 * @return string[] Returns component name as array key and JavaScript URL as array value,
	 *                  respectively. Will return an empty array if sanitization has yet to be run
	 *                  or if it did not find any HTML elements to convert to AMP equivalents.
	 */
	public function get_scripts() {
		return array();
	}

	/**
	 * Return array of values that would be valid as an HTML `style` attribute.
	 *
	 * @since 0.4
	 *
	 * @return array[][] Mapping of CSS selectors to arrays of properties.
	 */
	public function get_styles() {
		return array();
	}

	/**
	 * Get stylesheets.
	 *
	 * @since 0.7
	 * @returns array Values are the CSS stylesheets. Keys are MD5 hashes of the stylesheets.
	 */
	public function get_stylesheets() {
		$stylesheets = array();

		foreach ( $this->get_styles() as $selector => $properties ) {
			$stylesheet = sprintf( '%s { %s }', $selector, join( '; ', $properties ) . ';' );

			$stylesheets[ md5( $stylesheet ) ] = $stylesheet;
		}

		return $stylesheets;
	}

	/**
	 * Get HTML body as DOMElement from DOMDocument received by the constructor.
	 *
	 * @deprecated Just reference $root_element instead.
	 * @return DOMElement The body element.
	 */
	protected function get_body_node() {
		return $this->dom->getElementsByTagName( 'body' )->item( 0 );
	}

	/**
	 * Sanitizes a CSS dimension specifier while being sensitive to dimension context.
	 *
	 * @param string $value A valid CSS dimension specifier; e.g. 50, 50px, 50%.
	 * @param string $dimension 'width' or ignored. 'width' only affects $values ending in '%'.
	 *
	 * @return float|int|string Returns a numeric dimension value, or an empty string.
	 */
	public function sanitize_dimension( $value, $dimension ) {

		// Allows 0 to be used as valid dimension.
		if ( null === $value ) {
			return '';
		}

		// Accepts both integers and floats & prevents negative values.
		if ( is_numeric( $value ) ) {
			return max( 0, floatval( $value ) );
		}

		if ( AMP_String_Utils::endswith( $value, 'px' ) ) {
			return absint( $value );
		}

		if ( AMP_String_Utils::endswith( $value, '%' ) ) {
			if ( 'width' === $dimension && isset( $this->args['content_max_width'] ) ) {
				$percentage = absint( $value ) / 100;
				return round( $percentage * $this->args['content_max_width'] );
			}
		}

		return '';
	}

	/**
	 * Sets the layout, and possibly the 'height' and 'width' attributes.
	 *
	 * @param string[] $attributes {
	 *      Attributes.
	 *
	 *      @type int $height
	 *      @type int $width
	 *      @type string $sizes
	 *      @type string $class
	 *      @type string $layout
	 * }
	 * @return string[]
	 */
	public function set_layout( $attributes ) {
		if ( empty( $attributes['height'] ) ) {
			unset( $attributes['width'] );
			$attributes['height'] = self::FALLBACK_HEIGHT;
		}
		if ( empty( $attributes['width'] ) ) {
			$attributes['layout'] = 'fixed-height';
		}

		return $attributes;
	}

	/**
	 * Adds or appends key and value to list of attributes
	 *
	 * Adds key and value to list of attributes, or if the key already exists in the array
	 * it concatenates to existing attribute separator by a space or other supplied separator.
	 *
	 * @param string[] $attributes {
	 *      Attributes.
	 *
	 *      @type int $height
	 *      @type int $width
	 *      @type string $sizes
	 *      @type string $class
	 *      @type string $layout
	 * }
	 * @param string   $key       Valid associative array index to add.
	 * @param string   $value     Value to add or append to array indexed at the key.
	 * @param string   $separator Optional; defaults to space but some other separator if needed.
	 */
	public function add_or_append_attribute( &$attributes, $key, $value, $separator = ' ' ) {
		if ( isset( $attributes[ $key ] ) ) {
			$attributes[ $key ] = trim( $attributes[ $key ] . $separator . $value );
		} else {
			$attributes[ $key ] = $value;
		}
	}

	/**
	 * Decide if we should remove a src attribute if https is required.
	 *
	 * If not required, the implementing class may want to try and force https instead.
	 *
	 * @param string  $src         URL to convert to HTTPS if forced, or made empty if $args['require_https_src'].
	 * @param boolean $force_https Force setting of HTTPS if true.
	 * @return string URL which may have been updated with HTTPS, or may have been made empty.
	 */
	public function maybe_enforce_https_src( $src, $force_https = false ) {
		$protocol = strtok( $src, ':' );
		if ( 'https' !== $protocol ) {
			// Check if https is required.
			if ( isset( $this->args['require_https_src'] ) && true === $this->args['require_https_src'] ) {
				// Remove the src. Let the implementing class decide what do from here.
				$src = '';
			} elseif ( ( ! isset( $this->args['require_https_src'] ) || false === $this->args['require_https_src'] )
				&& true === $force_https ) {
				// Don't remove the src, but force https instead.
				$src = set_url_scheme( $src, 'https' );
			}
		}

		return $src;
	}

	/**
	 * Removes an invalid child of a node.
	 *
	 * Also, calls the mutation callback for it.
	 * This tracks all the nodes that were removed.
	 *
	 * @since 0.7
	 *
	 * @param DOMNode|DOMElement $node The node to remove.
	 * @param array              $args Additional args to pass to validation error callback.
	 *
	 * @return void
	 */
	public function remove_invalid_child( $node, $args = array() ) {
		if ( isset( $this->args['validation_error_callback'] ) ) {
			call_user_func( $this->args['validation_error_callback'],
				array_merge( compact( 'node' ), $args )
			);
		}
		if ( empty( $this->args['disable_invalid_removal'] ) ) {
			$node->parentNode->removeChild( $node );
		}
	}

	/**
	 * Removes an invalid attribute of a node.
	 *
	 * Also, calls the mutation callback for it.
	 * This tracks all the attributes that were removed.
	 *
	 * @since 0.7
	 *
	 * @param DOMElement     $element   The node for which to remove the attribute.
	 * @param DOMAttr|string $attribute The attribute to remove from the element.
	 * @param array          $args      Additional args to pass to validation error callback.
	 * @return void
	 */
	public function remove_invalid_attribute( $element, $attribute, $args = array() ) {
		if ( isset( $this->args['validation_error_callback'] ) ) {
			if ( is_string( $attribute ) ) {
				$attribute = $element->getAttributeNode( $attribute );
			}
			if ( $attribute ) {
				call_user_func( $this->args['validation_error_callback'],
					array_merge(
						array(
							'node' => $attribute,
						),
						$args
					)
				);
				if ( empty( $this->args['disable_invalid_removal'] ) ) {
					$element->removeAttributeNode( $attribute );
				}
			}
		} elseif ( empty( $this->args['disable_invalid_removal'] ) ) {
			if ( is_string( $attribute ) ) {
				$element->removeAttribute( $attribute );
			} else {
				$element->removeAttributeNode( $attribute );
			}
		}
	}
}
