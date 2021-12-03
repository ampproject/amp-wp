<?php
/**
 * Class AMP_Base_Sanitizer
 *
 * @package AMP
 */

use AmpProject\AmpWP\ValidationExemption;
use AmpProject\CssLength;
use AmpProject\DevMode;
use AmpProject\Dom\Document;

/**
 * Class AMP_Base_Sanitizer
 *
 * @since 0.2
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
	protected $DEFAULT_ARGS = [];

	/**
	 * DOM.
	 *
	 * @var Document An AmpProject\Document representation of an HTML document.
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
	 *      @type bool $should_locate_sources
	 *      @type callable $validation_error_callback
	 * }
	 */
	protected $args;

	/**
	 * Flag to be set in child class' sanitize() method indicating if the
	 * HTML contained in the Dom\Document has been sanitized yet or not.
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
	 * @param Document $dom Represents the HTML document to sanitize.
	 * @param array    $args {
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
	public function __construct( $dom, $args = [] ) {
		$this->dom  = $dom;
		$this->args = array_merge( $this->DEFAULT_ARGS, $args );

		if ( ! empty( $this->args['use_document_element'] ) ) {
			$this->root_element = $this->dom->documentElement;
		} else {
			$this->root_element = $this->dom->body;
		}
	}

	/**
	 * Add filters to manipulate output during output buffering before the DOM is constructed.
	 *
	 * Add actions and filters before the page is rendered so that the sanitizer can fix issues during output buffering.
	 * This provides an alternative to manipulating the DOM in the sanitize method. This is a static function because
	 * it is invoked before the class is instantiated, as the DOM is not available yet. This method is only called
	 * when 'amp' theme support is present. It is conceptually similar to the AMP_Base_Embed_Handler class's register_embed
	 * method.
	 *
	 * @since 1.0
	 * @see \AMP_Base_Embed_Handler::register_embed()
	 *
	 * @param array $args Args.
	 */
	public static function add_buffering_hooks( $args = [] ) {} // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

	/**
	 * Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
	 *
	 * @see AMP_Style_Sanitizer::ampify_ruleset_selectors()
	 *
	 * @return array Mapping.
	 */
	public function get_selector_conversion_mapping() {
		return [];
	}

	/**
	 * Determine whether the resulting AMP element uses a "light" shadow DOM.
	 *
	 * Sometimes AMP components serve as wrappers for native elements, like `amp-img` for `img`. When this
	 * is the case, authors sometimes will want to style the shadow element (such as to set object-fit). Normally if a
	 * selector contains `img` then the style sanitizer will always convert this to `amp-img` (and `amp-anim`), which
	 * may break the author's intended selector target. So when using a sanitizer's selector conversion mapping to
	 * rewrite non-AMP to AMP selectors, it will first check to see if the selector already mentions an AMP tag and if
	 * so it will skip the conversions for that selector. In this way, an `amp-img img` selector will not get converted
	 * into `amp-img amp-img`. The selector mapping also is involved when doing tree shaking. In the case of the
	 * selector `amp-img img`, the tree shaker would normally strip out this selector because no `img` may be present
	 * in the page as it is added by the AMP runtime (unless noscript fallbacks have been added, and this also
	 * disregards data-hero images which are added later by AMP Optimizer). So in order to prevent such selectors from
	 * being stripped out, it's important to include the `amp-img` selector among the `dynamic_element_selectors` so
	 * that the `img` in the `amp-img img` selector is ignored for the purposes of tree shaking. This method is used
	 * to indicate which sanitizers are involved in such element conversions. If this method returns true, then the
	 * keys in the selector conversion mapping should be used as `dynamic_element_selectors`.
	 *
	 * In other words, this method indicates whether keys in the conversion mapping are ancestors of elements which are
	 * created at runtime. This method is only relevant when the `get_selector_conversion_mapping()` method returns a
	 * mapping.
	 *
	 * @since 2.2
	 * @see AMP_Style_Sanitizer::ampify_ruleset_selectors()
	 * @see AMP_Base_Sanitizer::get_selector_conversion_mapping()
	 *
	 * @return bool Whether light DOM is used.
	 */
	public function has_light_shadow_dom() {
		return true;
	}

	/**
	 * Get arg.
	 *
	 * @since 2.2
	 *
	 * @param string $key Arg key.
	 * @return mixed Args.
	 */
	public function get_arg( $key ) {
		if ( array_key_exists( $key, $this->args ) ) {
			return $this->args[ $key ];
		}
		return null;
	}

	/**
	 * Get args.
	 *
	 * @since 2.2
	 *
	 * @return array Args.
	 */
	public function get_args() {
		return $this->args;
	}

	/**
	 * Update args.
	 *
	 * Merges the supplied args with the existing args.
	 *
	 * @since 2.2
	 *
	 * @param array $args Args.
	 */
	public function update_args( $args ) {
		$this->args = array_merge( $this->args, $args );
	}

	/**
	 * Run logic before any sanitizers are run.
	 *
	 * After the sanitizers are instantiated but before calling sanitize on each of them, this
	 * method is called with list of all the instantiated sanitizers.
	 *
	 * @param AMP_Base_Sanitizer[] $sanitizers Sanitizers.
	 */
	public function init( $sanitizers ) {} // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

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
		return [];
	}

	/**
	 * Return array of values that would be valid as an HTML `style` attribute.
	 *
	 * @since 0.4
	 * @codeCoverageIgnore
	 * @deprecated As of 1.0, use get_stylesheets().
	 * @internal
	 *
	 * @return array[][] Mapping of CSS selectors to arrays of properties.
	 */
	public function get_styles() {
		return [];
	}

	/**
	 * Get stylesheets.
	 *
	 * @since 0.7
	 * @return array Values are the CSS stylesheets. Keys are MD5 hashes of the stylesheets.
	 */
	public function get_stylesheets() {
		$stylesheets = [];

		foreach ( $this->get_styles() as $selector => $properties ) {
			$stylesheet = sprintf( '%s { %s }', $selector, implode( '; ', $properties ) . ';' );

			$stylesheets[ md5( $stylesheet ) ] = $stylesheet;
		}

		return $stylesheets;
	}

	/**
	 * Get HTML body as DOMElement from Dom\Document received by the constructor.
	 *
	 * @codeCoverageIgnore
	 * @deprecated Use $this->dom->body instead.
	 * @return DOMElement The body element.
	 */
	protected function get_body_node() {
		_deprecated_function( 'Use $this->dom->body instead', '1.5.0' );
		return $this->dom->body;
	}

	/**
	 * Sanitizes a CSS dimension specifier while being sensitive to dimension context.
	 *
	 * @param string $value     A valid CSS dimension specifier; e.g. 50, 50px, 50%. Can be 'auto' for width.
	 * @param string $dimension Dimension, either 'width' or 'height'.
	 *
	 * @return float|int|string Returns a numeric dimension value, 'auto', or an empty string.
	 */
	public function sanitize_dimension( $value, $dimension ) {

		// Allows 0 to be used as valid dimension.
		if ( null === $value ) {
			return '';
		}

		// Accepts both integers and floats & prevents negative values.
		if ( is_numeric( $value ) ) {
			return max( 0, (float) $value );
		}

		if ( AMP_String_Utils::endswith( $value, '%' ) && 'width' === $dimension ) {
			if ( '100%' === $value ) {
				return 'auto';
			} elseif ( isset( $this->args['content_max_width'] ) ) {
				$percentage = absint( $value ) / 100;
				return round( $percentage * $this->args['content_max_width'] );
			}
		}

		$length = new CssLength( $value );
		$length->validate( 'width' === $dimension, false );
		if ( $length->isValid() ) {
			if ( $length->isAuto() ) {
				return 'auto';
			}
			return $length->getNumeral() . ( $length->getUnit() === 'px' ? '' : $length->getUnit() );
		}

		return '';
	}

	/**
	 * Determine if an attribute value is empty.
	 *
	 * @param string|null $value Attribute value.
	 * @return bool True if empty, false if not.
	 */
	public function is_empty_attribute_value( $value ) {
		return ! isset( $value ) || '' === $value;
	}

	/**
	 * Sets the layout, and possibly the 'height' and 'width' attributes.
	 *
	 * @param array $attributes {
	 *      Attributes.
	 *
	 *      @type string     $bottom
	 *      @type int|string $height
	 *      @type string     $layout
	 *      @type string     $left
	 *      @type string     $position
	 *      @type string     $right
	 *      @type string     $style
	 *      @type string     $top
	 *      @type int|string $width
	 * }
	 * @return array Attributes.
	 */
	public function set_layout( $attributes ) {
		if ( isset( $attributes['layout'] ) && ( 'fill' === $attributes['layout'] || 'flex-item' !== $attributes['layout'] ) ) {
			return $attributes;
		}

		// Special-case handling for inline style that should be transformed into layout=fill.
		if ( ! empty( $attributes['style'] ) ) {
			$styles = $this->parse_style_string( $attributes['style'] );

			// Apply fill layout if top, left, bottom, right are used.
			if ( isset( $styles['position'], $styles['top'], $styles['left'], $styles['bottom'], $styles['right'] )
				&& 'absolute' === $styles['position']
				&& 0 === (int) $styles['top']
				&& 0 === (int) $styles['left']
				&& 0 === (int) $styles['bottom']
				&& 0 === (int) $styles['right']
				&& ( ! isset( $attributes['width'] ) || '100%' === $attributes['width'] )
				&& ( ! isset( $attributes['height'] ) || '100%' === $attributes['height'] )
			) {
				unset( $attributes['style'], $styles['position'], $styles['top'], $styles['left'], $styles['bottom'], $styles['right'] );
				if ( ! empty( $styles ) ) {
					$attributes['style'] = $this->reassemble_style_string( $styles );
				}
				$attributes['layout'] = 'fill';
				unset( $attributes['height'], $attributes['width'] );
				return $attributes;
			}

			// Apply fill layout if top, left, width, height are used.
			if ( isset( $styles['position'], $styles['top'], $styles['left'], $styles['width'], $styles['height'] )
				&& 'absolute' === $styles['position']
				&& 0 === (int) $styles['top']
				&& 0 === (int) $styles['left']
				&& '100%' === (string) $styles['width']
				&& '100%' === (string) $styles['height']
			) {
				unset( $attributes['style'], $styles['position'], $styles['top'], $styles['left'], $styles['width'], $styles['height'] );
				if ( ! empty( $styles ) ) {
					$attributes['style'] = $this->reassemble_style_string( $styles );
				}
				$attributes['layout'] = 'fill';
				unset( $attributes['height'], $attributes['width'] );
				return $attributes;
			}

			// Apply fill layout if width & height are 100%.
			if (
				( isset( $styles['position'] ) && 'absolute' === $styles['position'] )
				&& (
					( isset( $attributes['width'] ) && '100%' === $attributes['width'] )
					||
					( isset( $styles['width'] ) && '100%' === $styles['width'] )
				)
				&& (
					( isset( $attributes['height'] ) && '100%' === $attributes['height'] )
					||
					( isset( $styles['height'] ) && '100%' === $styles['height'] )
				)
			) {
				unset( $attributes['style'], $attributes['width'], $attributes['height'] );
				unset( $styles['position'], $styles['width'], $styles['height'] );
				if ( ! empty( $styles ) ) {
					$attributes['style'] = $this->reassemble_style_string( $styles );
				}
				$attributes['layout'] = 'fill';
				return $attributes;
			}

			// Make sure the width and height styles are copied to the width and height attributes since AMP will ultimately inline them as styles.
			$pending_attributes = [];
			$seen_units         = [];
			foreach ( wp_array_slice_assoc( $styles, [ 'height', 'width' ] ) as $dimension => $value ) {
				$value = $this->sanitize_dimension( $value, $dimension );
				if ( '' === $value ) {
					continue;
				}

				if ( 'auto' !== $value ) {
					$this_unit = preg_replace( '/^.*\d/', '', $value );
					if ( ! $this_unit ) {
						$this_unit = 'px';
					}
					$seen_units[ $this_unit ] = true;
				}

				$pending_attributes[ $dimension ] = $value;
			}
			if ( $pending_attributes && count( $seen_units ) <= 1 ) {
				$attributes = array_merge( $attributes, $pending_attributes );
			}
		}

		if ( isset( $attributes['width'], $attributes['height'] ) && '100%' === $attributes['width'] && '100%' === $attributes['height'] ) {
			unset( $attributes['width'], $attributes['height'] );
			$attributes['layout'] = 'fill';
		} else {
			if ( empty( $attributes['height'] ) ) {
				unset( $attributes['width'] );
				$attributes['height'] = self::FALLBACK_HEIGHT;
			}
			if ( empty( $attributes['width'] ) || '100%' === $attributes['width'] || 'auto' === $attributes['width'] ) {
				$attributes['layout'] = 'fixed-height';
				$attributes['width']  = 'auto';
			}
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
		$protocol = strtok( $src, ':' ); // @todo What about relative URLs? This should use wp_parse_url( $src, PHP_URL_SCHEME )
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
	 * Check whether the document of a given node is in dev mode.
	 *
	 * @since 1.3
	 *
	 * @deprecated Use AmpProject\DevMode::isActiveForDocument( $document ) instead.
	 *
	 * @return bool Whether the document is in dev mode.
	 */
	protected function is_document_in_dev_mode() {
		_deprecated_function( 'AMP_Base_Sanitizer::is_document_in_dev_mode', '1.5', 'AmpProject\DevMode::isActiveForDocument' );
		return DevMode::isActiveForDocument( $this->dom );
	}

	/**
	 * Check whether a node is exempt from validation during dev mode.
	 *
	 * @since 1.3
	 *
	 * @deprecated Use AmpProject\DevMode::hasExemptionForNode( $node ) instead.
	 *
	 * @param DOMNode $node Node to check.
	 * @return bool Whether the node should be exempt during dev mode.
	 */
	protected function has_dev_mode_exemption( DOMNode $node ) {
		_deprecated_function( 'AMP_Base_Sanitizer::has_dev_mode_exemption', '1.5', 'AmpProject\DevMode::hasExemptionForNode' );
		return DevMode::hasExemptionForNode( $node );
	}

	/**
	 * Check whether a certain node should be exempt from validation.
	 *
	 * @deprecated Use AmpProject\DevMode::isExemptFromValidation( $node ) instead.
	 *
	 * @param DOMNode $node Node to check.
	 * @return bool Whether the node should be exempt from validation.
	 */
	protected function is_exempt_from_validation( DOMNode $node ) {
		_deprecated_function( 'AMP_Base_Sanitizer::is_exempt_from_validation', '1.5', 'AmpProject\DevMode::isExemptFromValidation' );
		return DevMode::isExemptFromValidation( $node );
	}

	/**
	 * Removes an invalid child of a node.
	 *
	 * Also, calls the mutation callback for it.
	 * This tracks all the nodes that were removed.
	 *
	 * @since 0.7
	 *
	 * @param DOMNode|DOMElement $node             The node to remove.
	 * @param array              $validation_error Validation error details.
	 * @return bool Whether the node should have been removed, that is, that the node was sanitized for validity.
	 */
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
	 * @param array          $validation_error Validation error details.
	 * @param array          $attr_spec        Attribute spec.
	 * @return bool Whether the node should have been removed, that is, that the node was sanitized for validity.
	 */
	public function remove_invalid_attribute( $element, $attribute, $validation_error = [], $attr_spec = [] ) {
		if ( DevMode::isExemptFromValidation( $element ) ) {
			return false;
		}

		if ( is_string( $attribute ) ) {
			$node = $element->getAttributeNode( $attribute );
		} else {
			$node = $attribute;
		}

		// Catch edge condition (no known possible way to reach).
		if ( ! ( $node instanceof DOMAttr ) || $element !== $node->parentNode ) {
			return false;
		}

		if ( ValidationExemption::is_amp_unvalidated_for_node( $node ) || ValidationExemption::is_px_verified_for_node( $node ) ) {
			return false;
		}

		$should_remove = $this->should_sanitize_validation_error( $validation_error, compact( 'node' ) );
		if ( $should_remove ) {
			$allow_empty  = ! empty( $attr_spec[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] );
			$is_href_attr = ( isset( $attr_spec[ AMP_Rule_Spec::VALUE_URL ] ) && 'href' === $node->nodeName );
			if ( $allow_empty && ! $is_href_attr ) {
				$node->nodeValue = '';
			} else {
				$element->removeAttributeNode( $node );
			}
		} else {
			ValidationExemption::mark_node_as_amp_unvalidated( $node );
		}

		return $should_remove;
	}

	/**
	 * Check whether or not sanitization should occur in response to validation error.
	 *
	 * @since 1.0
	 *
	 * @param array $validation_error Validation error.
	 * @param array $data             Data including the node.
	 * @return bool Whether to sanitize.
	 */
	public function should_sanitize_validation_error( $validation_error, $data = [] ) {
		if ( empty( $this->args['validation_error_callback'] ) || ! is_callable( $this->args['validation_error_callback'] ) ) {
			return true;
		}
		$validation_error = $this->prepare_validation_error( $validation_error, $data );
		return false !== call_user_func( $this->args['validation_error_callback'], $validation_error, $data );
	}

	/**
	 * Prepare validation error.
	 *
	 * @param array $error {
	 *     Error.
	 *
	 *     @type string $code Error code.
	 * }
	 * @param array $data {
	 *     Data.
	 *
	 *     @type DOMElement|DOMNode $node The removed node.
	 * }
	 * @return array Error.
	 */
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

	/**
	 * Cleans up artifacts after the removal of an attribute node.
	 *
	 * @since 1.3
	 *
	 * @param DOMElement $element   The node for which the attribute was removed.
	 * @param DOMAttr    $attribute The attribute that was removed.
	 */
	protected function clean_up_after_attribute_removal( $element, $attribute ) {
		static $attributes_tied_to_href = [ 'target', 'download', 'rel', 'rev', 'hreflang', 'type' ];

		if ( 'href' === $attribute->nodeName ) {
			/*
			 * "The target, download, rel, rev, hreflang, and type attributes must be omitted
			 * if the href attribute is not present."
			 * See: https://www.w3.org/TR/2016/REC-html51-20161101/textlevel-semantics.html#the-a-element
			 */
			foreach ( $attributes_tied_to_href as $attribute_to_remove ) {
				if ( $element->hasAttribute( $attribute_to_remove ) ) {
					$element->removeAttribute( $attribute_to_remove );
				}
			}
		}
	}

	/**
	 * Get data-amp-* values from the parent node 'figure' added by editor block.
	 *
	 * @param DOMElement $node Base node.
	 * @return array AMP data array.
	 */
	public function get_data_amp_attributes( $node ) {
		$attributes = [];

		// Editor blocks add 'figure' as the parent node for images. If this node has data-amp-layout then we should add this as the layout attribute.
		$parent_node = $node->parentNode;
		if ( $parent_node instanceof DOMELement && 'figure' === $parent_node->tagName ) {
			$parent_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $parent_node );
			if ( isset( $parent_attributes['data-amp-layout'] ) ) {
				$attributes['layout'] = $parent_attributes['data-amp-layout'];
			}
			if ( isset( $parent_attributes['data-amp-noloading'] ) && true === filter_var( $parent_attributes['data-amp-noloading'], FILTER_VALIDATE_BOOLEAN ) ) {
				$attributes['noloading'] = $parent_attributes['data-amp-noloading'];
			}
		}

		return $attributes;
	}

	/**
	 * Set AMP attributes.
	 *
	 * @param array $attributes Array of attributes.
	 * @param array $amp_data Array of AMP attributes.
	 * @return array Updated attributes.
	 */
	public function filter_data_amp_attributes( $attributes, $amp_data ) {
		if ( isset( $amp_data['layout'] ) ) {
			$attributes['data-amp-layout'] = $amp_data['layout'];
		}
		if ( isset( $amp_data['noloading'] ) ) {
			$attributes['data-amp-noloading'] = '';
		}
		return $attributes;
	}

	/**
	 * Set attributes to node's parent element according to layout.
	 *
	 * @param DOMElement $node Node.
	 * @param array      $new_attributes Attributes array.
	 * @param string     $layout Layout.
	 * @return array New attributes.
	 */
	public function filter_attachment_layout_attributes( $node, $new_attributes, $layout ) {

		// The width has to be unset / auto in case of fixed-height.
		if ( 'fixed-height' === $layout && $node->parentNode instanceof DOMElement ) {
			if ( ! isset( $new_attributes['height'] ) ) {
				$new_attributes['height'] = self::FALLBACK_HEIGHT;
			}
			$new_attributes['width'] = 'auto';
			$node->parentNode->setAttribute( 'style', 'height: ' . $new_attributes['height'] . 'px; width: auto;' );

			// The parent element should have width/height set and position set in case of 'fill'.
		} elseif ( 'fill' === $layout && $node->parentNode instanceof DOMElement ) {
			if ( ! isset( $new_attributes['height'] ) ) {
				$new_attributes['height'] = self::FALLBACK_HEIGHT;
			}
			$node->parentNode->setAttribute( 'style', 'position:relative; width: 100%; height: ' . $new_attributes['height'] . 'px;' );
			unset( $new_attributes['width'], $new_attributes['height'] );
		} elseif ( 'responsive' === $layout && $node->parentNode instanceof DOMElement ) {
			$node->parentNode->setAttribute( 'style', 'position:relative; width: 100%; height: auto' );
		} elseif ( 'fixed' === $layout ) {
			if ( ! isset( $new_attributes['height'] ) ) {
				$new_attributes['height'] = self::FALLBACK_HEIGHT;
			}
		}

		return $new_attributes;
	}

	/**
	 * Parse a style string into an associative array of style attributes.
	 *
	 * @param string $style_string Style string to parse.
	 * @return string[] Associative array of style attributes.
	 */
	protected function parse_style_string( $style_string ) {
		// We need to turn the style string into an associative array of styles first.
		$style_string = trim( $style_string, " \t\n\r\0\x0B;" );
		$elements     = preg_split( '/(\s*:\s*|\s*;\s*)/', $style_string );

		if ( 0 !== count( $elements ) % 2 ) {
			// Style string was malformed, try to process as good as possible by stripping the last element.
			array_pop( $elements );
		}

		$chunks = array_chunk( $elements, 2 );

		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_columnFound -- WP Core provides a polyfill.
		return array_combine( array_column( $chunks, 0 ), array_column( $chunks, 1 ) );
	}

	/**
	 * Reassemble a style string that can be used in a 'style' attribute.
	 *
	 * @param array $styles Associative array of styles to reassemble into a string.
	 * @return string Reassembled style string.
	 */
	protected function reassemble_style_string( $styles ) {
		if ( ! is_array( $styles ) ) {
			return '';
		}

		// Discard empty values first.
		$styles = array_filter( $styles );

		return array_reduce(
			array_keys( $styles ),
			static function ( $style_string, $style_name ) use ( $styles ) {
				if ( ! empty( $style_string ) ) {
					$style_string .= ';';
				}

				return $style_string . "{$style_name}:{$styles[ $style_name ]}";
			},
			''
		);
	}

	/**
	 * Get data that is returned in validate responses.
	 *
	 * The array returned is merged with the overall validate response data.
	 *
	 * @see \AMP_Validation_Manager::get_validate_response_data()
	 * @return array Validate response data.
	 */
	public function get_validate_response_data() {
		return [];
	}
}
