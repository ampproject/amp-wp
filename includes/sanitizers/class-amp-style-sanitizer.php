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
	 * List of CSS styles in HTML content of DOMDocument ($this->dom).
	 *
	 * @since 0.4
	 * @var array[]
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
	 * Maximum number of bytes allowed for a keyframes style.
	 *
	 * @since 0.7
	 * @var int
	 */
	private $keyframes_max_size;

	/**
	 * Maximum number of bytes allowed for a AMP Custom style.
	 *
	 * @since 0.7
	 * @var int
	 */
	private $custom_max_size;

	/**
	 * Current CSS size.
	 *
	 * Sum of CSS located in $styles and $stylesheets.
	 *
	 * @var int
	 */
	private $current_custom_size = 0;

	/**
	 * The style[amp-custom] element.
	 *
	 * @var DOMElement
	 */
	private $amp_custom_style_element;

	/**
	 * Regex for allowed font stylesheet URL.
	 *
	 * @var string
	 */
	private $allowed_font_src_regex;

	/**
	 * Base URL for styles.
	 *
	 * Full URL with trailing slash.
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * URL of the content directory.
	 *
	 * @var string
	 */
	private $content_url;

	/**
	 * AMP_Base_Sanitizer constructor.
	 *
	 * @since 0.7
	 *
	 * @param DOMDocument $dom  Represents the HTML document to sanitize.
	 * @param array       $args Args.
	 */
	public function __construct( DOMDocument $dom, array $args = array() ) {
		parent::__construct( $dom, $args );

		$spec_name = 'style[amp-keyframes]';
		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'style' ) as $spec_rule ) {
			if ( isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && $spec_name === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$this->keyframes_max_size = $spec_rule[ AMP_Rule_Spec::CDATA ]['max_bytes'];
				break;
			}
		}

		$spec_name = 'style amp-custom';
		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'style' ) as $spec_rule ) {
			if ( isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && $spec_name === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$this->custom_max_size = $spec_rule[ AMP_Rule_Spec::CDATA ]['max_bytes'];
				break;
			}
		}

		$spec_name = 'link rel=stylesheet for fonts'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'link' ) as $spec_rule ) {
			if ( isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && $spec_name === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$this->allowed_font_src_regex = '@^(' . $spec_rule[ AMP_Rule_Spec::ATTR_SPEC_LIST ]['href']['value_regex'] . ')$@';
				break;
			}
		}

		$guessurl = site_url();
		if ( ! $guessurl ) {
			$guessurl = wp_guess_url();
		}
		$this->base_url    = $guessurl;
		$this->content_url = WP_CONTENT_URL;
	}

	/**
	 * Get list of CSS styles in HTML content of DOMDocument ($this->dom).
	 *
	 * @since 0.4
	 *
	 * @return array[] Mapping CSS selectors to array of properties, or mapping of keys starting with 'stylesheet:' with value being the stylesheet.
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
		return array_merge( $this->stylesheets, parent::get_stylesheets() );
	}

	/**
	 * Sanitize CSS styles within the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.4
	 */
	public function sanitize() {
		$elements = array();

		// Do nothing if inline styles are allowed.
		if ( ! empty( $this->args['allow_dirty_styles'] ) ) {
			return;
		}

		/*
		 * Note that xpath is used to query the DOM so that the link and style elements will be
		 * in document order. DOMNode::compareDocumentPosition() is not yet implemented.
		 */
		$xpath = new DOMXPath( $this->dom );

		$lower_case = 'translate( %s, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz" )'; // In XPath 2.0 this is lower-case().
		$predicates = array(
			sprintf( '( self::style and not( @amp-boilerplate ) and ( not( @type ) or %s = "text/css" ) )', sprintf( $lower_case, '@type' ) ),
			sprintf( '( self::link and @href and %s = "stylesheet" )', sprintf( $lower_case, '@rel' ) ),
		);

		foreach ( $xpath->query( '//*[ ' . implode( ' or ', $predicates ) . ' ]' ) as $element ) {
			$elements[] = $element;
		}

		/**
		 * Element.
		 *
		 * @var DOMElement $element
		 */
		foreach ( $elements as $element ) {
			$node_name = strtolower( $element->nodeName );
			if ( 'style' === $node_name ) {
				$this->process_style_element( $element );
			} elseif ( 'link' === $node_name ) {
				$this->process_link_element( $element );
			}
		}

		$elements = array();
		foreach ( $xpath->query( '//*[ @style ]' ) as $element ) {
			$elements[] = $element;
		}
		foreach ( $elements as $element ) {
			$this->collect_inline_styles( $element );
		}
		$this->did_convert_elements = true;

		// Now make sure the amp-custom style is in the DOM and populated, if we're working with the document element.
		if ( ! empty( $this->args['use_document_element'] ) ) {
			if ( ! $this->amp_custom_style_element ) {
				$this->amp_custom_style_element = $this->dom->createElement( 'style' );
				$this->amp_custom_style_element->setAttribute( 'amp-custom', '' );
				$head = $this->dom->getElementsByTagName( 'head' )->item( 0 );
				if ( ! $head ) {
					$head = $this->dom->createElement( 'head' );
					$this->dom->documentElement->insertBefore( $head, $this->dom->documentElement->firstChild );
				}
				$head->appendChild( $this->amp_custom_style_element );
			}

			$css = implode( '', $this->get_stylesheets() );

			/*
			 * Let the style[amp-custom] be populated with the concatenated CSS.
			 * !important: Updating the contents of this style element by setting textContent is not
			 * reliable across PHP/libxml versions, so this is why the children are removed and the
			 * text node is then explicitly added containing the CSS.
			 */
			while ( $this->amp_custom_style_element->firstChild ) {
				$this->amp_custom_style_element->removeChild( $this->amp_custom_style_element->firstChild );
			}
			$this->amp_custom_style_element->appendChild( $this->dom->createTextNode( $css ) );
		}
	}

	/**
	 * Generates an enqueued style's fully-qualified file path.
	 *
	 * @since 0.7
	 * @see WP_Styles::_css_href()
	 *
	 * @param string $src The source URL of the enqueued style.
	 * @return string|WP_Error Style's absolute validated filesystem path, or WP_Error when error.
	 */
	public function get_validated_css_file_path( $src ) {
		$needs_base_url = (
			! is_bool( $src )
			&&
			! preg_match( '|^(https?:)?//|', $src )
			&&
			! ( $this->content_url && 0 === strpos( $src, $this->content_url ) )
		);
		if ( $needs_base_url ) {
			$src = $this->base_url . $src;
		}

		// Strip query and fragment from URL.
		$src = preg_replace( ':[\?#].*$:', '', $src );

		if ( ! preg_match( '/\.(css|less|scss|sass)$/i', $src ) ) {
			/* translators: %s is stylesheet URL */
			return new WP_Error( 'amp_css_bad_file_extension', sprintf( __( 'Skipped stylesheet which does not have recognized CSS file extension (%s).', 'amp' ), $src ) );
		}

		$includes_url = includes_url( '/' );
		$content_url  = content_url( '/' );
		$admin_url    = get_admin_url( null, '/' );
		$css_path     = null;
		if ( 0 === strpos( $src, $content_url ) ) {
			$css_path = WP_CONTENT_DIR . substr( $src, strlen( $content_url ) - 1 );
		} elseif ( 0 === strpos( $src, $includes_url ) ) {
			$css_path = ABSPATH . WPINC . substr( $src, strlen( $includes_url ) - 1 );
		} elseif ( 0 === strpos( $src, $admin_url ) ) {
			$css_path = ABSPATH . 'wp-admin' . substr( $src, strlen( $admin_url ) - 1 );
		}

		if ( ! $css_path || false !== strpos( '../', $css_path ) || 0 !== validate_file( $css_path ) || ! file_exists( $css_path ) ) {
			/* translators: %s is stylesheet URL */
			return new WP_Error( 'amp_css_path_not_found', sprintf( __( 'Unable to locate filesystem path for stylesheet %s.', 'amp' ), $src ) );
		}

		return $css_path;
	}

	/**
	 * Process style element.
	 *
	 * @param DOMElement $element Style element.
	 */
	private function process_style_element( DOMElement $element ) {
		if ( $element->hasAttribute( 'amp-keyframes' ) ) {
			$validity = $this->validate_amp_keyframe( $element );
			if ( is_wp_error( $validity ) ) {
				$this->remove_invalid_child( $element, array(
					'message' => $validity->get_error_message(),
				) );
			}
			return;
		}

		$rules = trim( $element->textContent );
		$rules = $this->remove_illegal_css( $rules, $element );

		// Remove if surpasses max size.
		$length = strlen( $rules );
		if ( $this->current_custom_size + $length > $this->custom_max_size ) {
			$this->remove_invalid_child( $element, array(
				'message' => __( 'Too much CSS enqueued.', 'amp' ),
			) );
			return;
		}

		$this->stylesheets[ md5( $rules ) ] = $rules;
		$this->current_custom_size         += $length;

		if ( $element->hasAttribute( 'amp-custom' ) ) {
			if ( ! $this->amp_custom_style_element ) {
				$this->amp_custom_style_element = $element;
			} else {
				$element->parentNode->removeChild( $element ); // There can only be one. #highlander.
			}
		} else {

			// Remove from DOM since we'll be adding it to amp-custom.
			$element->parentNode->removeChild( $element );
		}
	}

	/**
	 * Process link element.
	 *
	 * @param DOMElement $element Link element.
	 */
	private function process_link_element( DOMElement $element ) {
		$href = $element->getAttribute( 'href' );

		// Allow font URLs.
		if ( $this->allowed_font_src_regex && preg_match( $this->allowed_font_src_regex, $href ) ) {
			return;
		}

		$css_file_path = $this->get_validated_css_file_path( $href );
		if ( is_wp_error( $css_file_path ) ) {
			$this->remove_invalid_child( $element, array(
				'message' => $css_file_path->get_error_message(),
			) );
			return;
		}

		// Load the CSS from the filesystem.
		$rules  = "\n/* $href */\n";
		$rules .= file_get_contents( $css_file_path ); // phpcs:ignore -- It's a local filesystem path not a remote request.

		$rules = $this->remove_illegal_css( $rules, $element );

		$media = $element->getAttribute( 'media' );
		if ( $media && 'all' !== $media ) {
			$rules = sprintf( '@media %s { %s }', $media, $rules );
		}

		// Remove if surpasses max size.
		$length = strlen( $rules );
		if ( $this->current_custom_size + $length > $this->custom_max_size ) {
			$this->remove_invalid_child( $element, array(
				'message' => __( 'Too much CSS enqueued.', 'amp' ),
			) );
			return;
		}

		$this->current_custom_size += $length;
		$this->stylesheets[ $href ] = $rules;

		// Remove now that styles have been processed.
		$element->parentNode->removeChild( $element );
	}

	/**
	 * Remove illegal CSS from the stylesheet.
	 *
	 * @since 0.7
	 *
	 * @todo This needs proper CSS parser and to take an alternative approach to removing !important by extracting
	 * the rule into a separate style rule with a very specific selector.
	 * @param string     $stylesheet Stylesheet.
	 * @param DOMElement $element    Element where the stylesheet came from.
	 * @return string Scrubbed stylesheet.
	 */
	private function remove_illegal_css( $stylesheet, $element ) {
		$stylesheet = preg_replace( '/\s*!important/', '', $stylesheet, -1, $important_count ); // Note this has to also replace inside comments to be valid.
		if ( $important_count > 0 && ! empty( $this->args['validation_error_callback'] ) ) {
			call_user_func( $this->args['validation_error_callback'], array(
				'code' => 'css_important_removed',
				'node' => $element,
			) );
		}
		$stylesheet = preg_replace( '/overflow(-[xy])?\s*:\s*(auto|scroll)\s*;?\s*/', '', $stylesheet, -1, $overlow_count );
		if ( $overlow_count > 0 && ! empty( $this->args['validation_error_callback'] ) ) {
			call_user_func( $this->args['validation_error_callback'], array(
				'code' => 'css_overflow_property_removed',
				'node' => $element,
			) );
		}
		return $stylesheet;
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
		if ( 'body' !== $style->parentNode->nodeName ) {
			return new WP_Error( 'mandatory_body_child', __( 'amp-keyframes is not child of body element.', 'amp' ) );
		}

		if ( $this->keyframes_max_size && strlen( $style->textContent ) > $this->keyframes_max_size ) {
			return new WP_Error( 'max_bytes', __( 'amp-keyframes is too large', 'amp' ) );
		}

		// This logic could be in AMP_Tag_And_Attribute_Sanitizer, but since it only applies to amp-keyframes it seems unnecessary.
		$next_sibling = $style->nextSibling;
		while ( $next_sibling ) {
			if ( $next_sibling instanceof DOMElement ) {
				return new WP_Error( 'mandatory_last_child', __( 'amp-keyframes is not last element in body.', 'amp' ) );
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
	 * @since 0.7 Modified to use element passed by XPath query.
	 *
	 * @note Uses recursion to traverse down the tree of DOMDocument nodes.
	 *
	 * @param DOMElement $element Node.
	 */
	private function collect_inline_styles( $element ) {
		$value = $element->getAttribute( 'style' );
		if ( ! $value ) {
			return;
		}
		$class = $element->getAttribute( 'class' );

		$properties = $this->process_style( $value );

		if ( ! empty( $properties ) ) {
			$class_name = $this->generate_class_name( $properties );
			$new_class  = trim( $class . ' ' . $class_name );

			$selector = '.' . $class_name;
			$length   = strlen( sprintf( '%s { %s }', $selector, join( '; ', $properties ) . ';' ) );

			if ( $this->current_custom_size + $length > $this->custom_max_size ) {
				$this->remove_invalid_attribute( $element, 'style', array(
					'message' => __( 'Too much CSS.', 'amp' ),
				) );
				return;
			}

			$element->setAttribute( 'class', $new_class );
			$this->styles[ $selector ] = $properties;
		}
		$element->removeAttribute( 'style' );
	}

	/**
	 * Sanitize and convert individual styles.
	 *
	 * @since 0.4
	 *
	 * @param string $css Style string.
	 * @return array Style properties.
	 */
	private function process_style( $css ) {

		// Normalize whitespace.
		$css = str_replace( array( "\n", "\r", "\t" ), '', $css );

		/*
		 * Use preg_split to break up rules by `;` but only if the
		 * semi-colon is not inside parens (like a data-encoded image).
		 */
		$styles = preg_split( '/\s*;\s*(?![^(]*\))/', trim( $css, '; ' ) );
		$styles = array_filter( $styles );

		// Normalize the order of the styles.
		sort( $styles );

		$processed_styles = array();

		// Normalize whitespace and filter rules.
		foreach ( $styles as $index => $rule ) {
			$tuple = preg_split( '/\s*:\s*/', $rule, 2 );
			if ( 2 !== count( $tuple ) ) {
				continue;
			}

			list( $property, $value ) = $this->filter_style( $tuple[0], $tuple[1] );
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
		/*
		 * Remove overflow if value is `auto` or `scroll`; not allowed in AMP
		 *
		 * @todo This removal needs to be reported.
		 * @see https://www.ampproject.org/docs/reference/spec.html#properties
		 */
		if ( preg_match( '#^overflow(-[xy])?$#i', $property ) && preg_match( '#^(auto|scroll)$#i', $value ) ) {
			return array( false, false );
		}

		if ( 'width' === $property ) {
			$property = 'max-width';
		}

		/*
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
