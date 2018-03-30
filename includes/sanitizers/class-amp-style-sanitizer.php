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
	 * XPath.
	 *
	 * @var DOMXPath
	 */
	private $xpath;

	/**
	 * Amount of time that was spent parsing CSS.
	 *
	 * @var float
	 */
	private $parse_css_duration = 0.0;

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
		$this->xpath       = new DOMXPath( $dom );
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
		$xpath = $this->xpath;

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

		if ( $this->parse_css_duration > 0.0 ) {
			AMP_Response_Headers::send_server_timing( 'amp_parse_css', $this->parse_css_duration, 'AMP Parse CSS' );
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

		$stylesheet = trim( $element->textContent );
		if ( $stylesheet ) {
			$stylesheet = $this->process_stylesheet( $stylesheet, $element );
		}

		// Remove if surpasses max size.
		$length = strlen( $stylesheet );
		if ( $this->current_custom_size + $length > $this->custom_max_size ) {
			$this->remove_invalid_child( $element, array(
				'message' => __( 'Too much CSS enqueued.', 'amp' ),
			) );
			return;
		}

		$hash = md5( $stylesheet );

		$this->stylesheets[ $hash ] = $stylesheet;
		$this->current_custom_size += $length;

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
		$stylesheet = file_get_contents( $css_file_path ); // phpcs:ignore -- It's a local filesystem path not a remote request.
		if ( false === $stylesheet ) {
			$this->remove_invalid_child( $element, array(
				'message' => __( 'Unable to load stylesheet from filesystem.', 'amp' ),
			) );
			return;
		}

		// Honor the link's media attribute.
		$media = $element->getAttribute( 'media' );
		if ( $media && 'all' !== $media ) {
			$stylesheet = sprintf( '@media %s { %s }', $media, $stylesheet );
		}

		$stylesheet = $this->process_stylesheet( $stylesheet, $element );

		// Skip if surpasses max size.
		$length = strlen( $stylesheet );
		if ( $this->current_custom_size + $length > $this->custom_max_size ) {
			$this->remove_invalid_child( $element, array(
				'message' => __( 'Too much CSS enqueued.', 'amp' ),
			) );
			return;
		}
		$hash = md5( $stylesheet );

		$this->stylesheets[ $hash ] = $stylesheet;
		$this->current_custom_size += $length;

		// Remove now that styles have been processed.
		$element->parentNode->removeChild( $element );
	}

	/**
	 * Process stylesheet.
	 *
	 * Sanitized invalid CSS properties and rules, removes rules which do not
	 * apply to the current document, and compresses the CSS to remove whitespace and comments.
	 *
	 * @since 1.0
	 * @todo Add flag for whether to do tree shaking.
	 *
	 * @param string             $stylesheet Stylesheet.
	 * @param DOMElement|DOMAttr $node       Element (link/style) or style attribute where the stylesheet came from.
	 * @param array              $args       {
	 *     Args.
	 *
	 *     @type bool $convert_width_to_max_width Convert width to max-width.
	 * }
	 * @return string Processed stylesheet.
	 */
	private function process_stylesheet( $stylesheet, $node, $args = array() ) {
		$start_time = microtime( true );

		$args = array_merge(
			array(
				'convert_width_to_max_width' => false,
			),
			$args
		);

		$cache_key        = md5( $stylesheet );
		$cached_processed = wp_cache_get( $cache_key, 'amp-stylesheet' );
		if ( $cached_processed ) {
			if ( ! empty( $this->args['validation_error_callback'] ) ) {
				foreach ( $cached_processed['validation_errors'] as $validation_error ) {
					call_user_func( $this->args['validation_error_callback'], array_merge( $validation_error, compact( 'node' ) ) );
				}
			}
			return $cached_processed['stylesheet'];
		}

		$parser_settings = Sabberworm\CSS\Settings::create()->withMultibyteSupport( false );
		$css_parser      = new Sabberworm\CSS\Parser( $stylesheet, $parser_settings );
		$css_document    = $css_parser->parse();

		// @todo Fetch an @import.
		// @todo Disallow anything except @font-face, @keyframes, @media, @supports.
		/**
		 * Rulesets.
		 *
		 * @var Sabberworm\CSS\RuleSet\RuleSet[] $rulesets
		 * @var Sabberworm\CSS\Rule\Rule $rule
		 */
		$rulesets          = $css_document->getAllRuleSets();
		$validation_errors = array();
		foreach ( $rulesets as $ruleset ) {
			/**
			 * Properties.
			 *
			 * @var Sabberworm\CSS\Rule\Rule[] $properties
			 */

			// Remove properties that have illegal values. See <https://www.ampproject.org/docs/fundamentals/spec#properties>.
			// @todo Limit transition to opacity, transform and -vendorPrefix-transform. See https://www.ampproject.org/docs/design/responsive/style_pages#restricted-styles.
			$properties = $ruleset->getRules( 'overflow-' );
			foreach ( $properties as $property ) {
				if ( in_array( $property->getValue(), array( 'auto', 'scroll' ), true ) ) {
					$validation_errors[] = array(
						'code'           => 'illegal_css_property_value',
						'property_name'  => $property->getRule(),
						'property_value' => $property->getValue(),
					);
					$ruleset->removeRule( $property->getRule() );
				}
			}

			// Remove important qualifiers. See <https://www.ampproject.org/docs/fundamentals/spec#important>.
			// @todo Try to convert into something else, like https://www.npmjs.com/package/replace-important.
			$properties = $ruleset->getRules();
			foreach ( $properties as $property ) {
				if ( $property->getIsImportant() ) {
					$validation_errors[] = array(
						'code'           => 'illegal_css_important_qualifier',
						'property_name'  => $property->getRule(),
						'property_value' => $property->getValue(),
					);
					$property->setIsImportant( false );
				}
			}

			// Convert width to max-width when requested. See <https://github.com/Automattic/amp-wp/issues/494>.
			if ( $args['convert_width_to_max_width'] ) {
				$properties = $ruleset->getRules( 'width' );
				foreach ( $properties as $property ) {
					$width_property = new \Sabberworm\CSS\Rule\Rule( 'max-width' );
					$width_property->setValue( $property->getValue() );
					$ruleset->removeRule( $property );
					$ruleset->addRule( $width_property, $property );
				}
			}

			// Remove the ruleset if it is now empty.
			if ( 0 === count( $ruleset->getRules() ) ) {
				$css_document->remove( $ruleset );
			}
			// @todo Sort??
			// @todo Delete rules with selectors for -amphtml- class and i-amphtml- tags.
		}

		$output_format = Sabberworm\CSS\OutputFormat::createCompact();
		$stylesheet    = $css_document->render( $output_format );

		if ( ! empty( $this->args['validation_error_callback'] ) ) {
			foreach ( $validation_errors as $validation_error ) {
				call_user_func( $this->args['validation_error_callback'], array_merge( $validation_error, compact( 'node' ) ) );
			}
		}

		$this->parse_css_duration += ( microtime( true ) - $start_time );

		// @todo We need to cache an object that allows us to identify the rules that can be deleted.
		wp_cache_set(
			$cache_key,
			compact( 'stylesheet', 'validation_errors' ),
			'amp-stylesheet'
		);

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
		$style_attribute = $element->getAttributeNode( 'style' );
		if ( ! $style_attribute || ! trim( $style_attribute->nodeValue ) ) {
			return;
		}

		// @todo Use hash from resulting processed CSS so that we can potentially re-use? We need to use the hash of the original rules as the cache key.
		$class  = 'amp-wp-' . substr( md5( $style_attribute->nodeValue ), 0, 7 );
		$rule   = sprintf( '.%s { %s }', $class, $style_attribute->nodeValue );
		$hash   = md5( $rule );
		$rule   = $this->process_stylesheet( $rule, $style_attribute, array(
			'convert_width_to_max_width' => true,
		) );
		$length = strlen( $rule );

		$element->removeAttribute( 'style' );

		if ( 0 === $length ) {
			return;
		}

		if ( $this->current_custom_size + $length > $this->custom_max_size ) {
			$this->remove_invalid_attribute( $element, $style_attribute, array(
				'message' => __( 'Too much CSS.', 'amp' ),
			) );
			return;
		}

		$this->current_custom_size += $length;
		$this->stylesheets[ $hash ] = $rule;

		if ( $element->hasAttribute( 'class' ) ) {
			$element->setAttribute( 'class', $element->getAttribute( 'class' ) . ' ' . $class );
		} else {
			$element->setAttribute( 'class', $class );
		}
	}
}
