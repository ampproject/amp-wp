<?php
/**
 * Class AMP_Style_Sanitizer
 *
 * @package AMP
 */

use \Sabberworm\CSS\RuleSet\DeclarationBlock;
use \Sabberworm\CSS\CSSList\CSSList;
use \Sabberworm\CSS\Property\Selector;
use \Sabberworm\CSS\RuleSet\RuleSet;
use \Sabberworm\CSS\Rule\Rule;
use \Sabberworm\CSS\Property\AtRule;
use \Sabberworm\CSS\CSSList\KeyFrame;
use \Sabberworm\CSS\RuleSet\AtRuleSet;
use \Sabberworm\CSS\Property\Import;
use \Sabberworm\CSS\CSSList\AtRuleBlockList;
use \Sabberworm\CSS\Value\RuleValueList;
use \Sabberworm\CSS\Value\URL;

/**
 * Class AMP_Style_Sanitizer
 *
 * Collects inline styles and outputs them in the amp-custom stylesheet.
 */
class AMP_Style_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Array of flags used to control sanitization.
	 *
	 * @var array {
	 *      @type string   $remove_unused_rules        Enum 'never', 'sometimes' (default), 'always'. If total CSS is greater than max_bytes, whether to strip selectors (and then empty rules) when they are not found to be used in doc. A validation error will be emitted when stripping happens since it is not completely safe in the case of dynamic content.
	 *      @type string[] $dynamic_element_selectors  Selectors for elements (or their ancestors) which contain dynamic content; selectors containing these will not be filtered.
	 *      @type bool     $use_document_element       Whether the root of the document should be used rather than the body.
	 *      @type bool     $require_https_src          Require HTTPS URLs.
	 *      @type bool     $allow_dirty_styles         Allow dirty styles. This short-circuits the sanitize logic; it is used primarily in Customizer preview.
	 *      @type callable $validation_error_callback  Function to call when a validation error is encountered.
	 * }
	 */
	protected $args;

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = array(
		'remove_unused_rules'       => 'sometimes',
		'dynamic_element_selectors' => array(
			'amp-list',
			'amp-live-list',
			'[submit-error]',
			'[submit-success]',
		),
	);

	/**
	 * Stylesheets.
	 *
	 * Values are the CSS stylesheets. Keys are MD5 hashes of the stylesheets,
	 *
	 * @since 0.7
	 * @var string[]
	 */
	private $stylesheets = array();

	/**
	 * List of stylesheet parts prior to selector/rule removal (tree shaking).
	 *
	 * Keys are MD5 hashes of stylesheets.
	 *
	 * @since 1.0
	 * @var array[] {
	 *     @type array              $stylesheet Array of stylesheet chunked, with declaration blocks being represented as arrays.
	 *     @type DOMElement|DOMAttr $node       Origin for styles.
	 *     @type array              $sources    Sources for the node.
	 *     @type bool               $keyframes  Whether an amp-keyframes.
	 * }
	 */
	private $pending_stylesheets = array();

	/**
	 * Spec for style[amp-custom] cdata.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $style_custom_cdata_spec;

	/**
	 * The style[amp-custom] element.
	 *
	 * @var DOMElement
	 */
	private $amp_custom_style_element;

	/**
	 * Spec for style[amp-keyframes] cdata.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $style_keyframes_cdata_spec;

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
	 * Class names used in document.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $used_class_names = array();

	/**
	 * Tag names used in document.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $used_tag_names = array();

	/**
	 * XPath.
	 *
	 * @since 1.0
	 * @var DOMXPath
	 */
	private $xpath;

	/**
	 * Amount of time that was spent parsing CSS.
	 *
	 * @since 1.0
	 * @var float
	 */
	private $parse_css_duration = 0.0;

	/**
	 * Placeholders for calc() values that are temporarily removed from CSS since they cause parse errors.
	 *
	 * @since 1.0
	 * @see AMP_Style_Sanitizer::add_calc_placeholders()
	 *
	 * @var array
	 */
	private $calc_placeholders = array();

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

		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'style' ) as $spec_rule ) {
			if ( ! isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) ) {
				continue;
			}
			if ( 'style[amp-keyframes]' === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$this->style_keyframes_cdata_spec = $spec_rule[ AMP_Rule_Spec::CDATA ];
			} elseif ( 'style amp-custom' === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$this->style_custom_cdata_spec = $spec_rule[ AMP_Rule_Spec::CDATA ];
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
	 * @deprecated As of 1.0, use get_stylesheets().
	 *
	 * @return array[] Mapping CSS selectors to array of properties, or mapping of keys starting with 'stylesheet:' with value being the stylesheet.
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
		return $this->stylesheets;
	}

	/**
	 * Get list of all the class names used in the document, including those used in [class] attributes.
	 *
	 * @since 1.0
	 * @return array Used class names.
	 */
	private function get_used_class_names() {
		if ( empty( $this->used_class_names ) ) {
			$classes = ' ';
			foreach ( $this->xpath->query( '//*/@class' ) as $class_attribute ) {
				$classes .= ' ' . $class_attribute->nodeValue;
			}

			// Find all [class] attributes and capture the contents of any single- or double-quoted strings.
			foreach ( $this->xpath->query( '//*/@' . AMP_DOM_Utils::get_amp_bind_placeholder_prefix() . 'class' ) as $bound_class_attribute ) {
				if ( preg_match_all( '/([\'"])([^\1]*?)\1/', $bound_class_attribute->nodeValue, $matches ) ) {
					$classes .= ' ' . implode( ' ', $matches[2] );
				}
			}

			$this->used_class_names = array_unique( array_filter( preg_split( '/\s+/', trim( $classes ) ) ) );
		}
		return $this->used_class_names;
	}


	/**
	 * Get list of all the tag names used in the document.
	 *
	 * @since 1.0
	 * @return array Used tag names.
	 */
	private function get_used_tag_names() {
		if ( empty( $this->used_tag_names ) ) {
			$used_tag_names = array();
			foreach ( $this->dom->getElementsByTagName( '*' ) as $el ) {
				$used_tag_names[ $el->tagName ] = true;
			}
			$this->used_tag_names = array_keys( $used_tag_names );
		}
		return $this->used_tag_names;
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

		$this->parse_css_duration = 0.0;

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

		// If 'width' attribute is present for 'col' tag, convert to proper CSS rule.
		foreach ( $this->dom->getElementsByTagName( 'col' ) as $col ) {
			$width_attr = $col->getAttribute( 'width' );
			if ( ! empty( $width_attr ) && ( false === strpos( $width_attr, '*' ) ) ) {
				$width_style = 'width: ' . $width_attr;
				if ( is_numeric( $width_attr ) ) {
					$width_style .= 'px';
				}
				if ( $col->hasAttribute( 'style' ) ) {
					$col->setAttribute( 'style', $width_style . ';' . $col->getAttribute( 'style' ) );
				} else {
					$col->setAttribute( 'style', $width_style );
				}
				$col->removeAttribute( 'width' );
			}
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

		$this->finalize_styles();

		$this->did_convert_elements = true;

		if ( $this->parse_css_duration > 0.0 ) {
			AMP_Response_Headers::send_server_timing( 'amp_parse_css', $this->parse_css_duration, 'AMP Parse CSS' );
		}
	}

	/**
	 * Generate a URL's fully-qualified file path.
	 *
	 * @since 0.7
	 * @see WP_Styles::_css_href()
	 *
	 * @param string   $url The file URL.
	 * @param string[] $allowed_extensions Allowed file extensions.
	 * @return string|WP_Error Style's absolute validated filesystem path, or WP_Error when error.
	 */
	public function get_validated_url_file_path( $url, $allowed_extensions = array() ) {
		$needs_base_url = (
			! is_bool( $url )
			&&
			! preg_match( '|^(https?:)?//|', $url )
			&&
			! ( $this->content_url && 0 === strpos( $url, $this->content_url ) )
		);
		if ( $needs_base_url ) {
			$url = $this->base_url . $url;
		}

		$remove_url_scheme = function( $schemed_url ) {
			return preg_replace( '#^\w+:(?=//)#', '', $schemed_url );
		};

		// Strip URL scheme, query, and fragment.
		$url = $remove_url_scheme( preg_replace( ':[\?#].*$:', '', $url ) );

		$includes_url = $remove_url_scheme( includes_url( '/' ) );
		$content_url  = $remove_url_scheme( content_url( '/' ) );
		$admin_url    = $remove_url_scheme( get_admin_url( null, '/' ) );

		$allowed_hosts = array(
			wp_parse_url( $includes_url, PHP_URL_HOST ),
			wp_parse_url( $content_url, PHP_URL_HOST ),
			wp_parse_url( $admin_url, PHP_URL_HOST ),
		);

		$url_host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! in_array( $url_host, $allowed_hosts, true ) ) {
			/* translators: %s is the file URL */
			return new WP_Error( 'disallowed_external_file_url', sprintf( __( 'Skipped file which does not have a recognized local host (%s).', 'amp' ), $url_host ) );
		}

		// Validate file extensions.
		if ( ! empty( $allowed_extensions ) ) {
			$pattern = sprintf( '/\.(%s)$/i', implode( '|', $allowed_extensions ) );
			if ( ! preg_match( $pattern, $url ) ) {
				/* translators: %s is the file URL */
				return new WP_Error( 'disallowed_file_extension', sprintf( __( 'Skipped file which does not have an allowed file extension (%s).', 'amp' ), $url ) );
			}
		}

		$file_path = null;
		if ( 0 === strpos( $url, $content_url ) ) {
			$file_path = WP_CONTENT_DIR . substr( $url, strlen( $content_url ) - 1 );
		} elseif ( 0 === strpos( $url, $includes_url ) ) {
			$file_path = ABSPATH . WPINC . substr( $url, strlen( $includes_url ) - 1 );
		} elseif ( 0 === strpos( $url, $admin_url ) ) {
			$file_path = ABSPATH . 'wp-admin' . substr( $url, strlen( $admin_url ) - 1 );
		}

		if ( ! $file_path || false !== strpos( '../', $file_path ) || 0 !== validate_file( $file_path ) || ! file_exists( $file_path ) ) {
			/* translators: %s is file URL */
			return new WP_Error( 'file_path_not_found', sprintf( __( 'Unable to locate filesystem path for %s.', 'amp' ), $url ) );
		}

		return $file_path;
	}

	/**
	 * Process style element.
	 *
	 * @param DOMElement $element Style element.
	 */
	private function process_style_element( DOMElement $element ) {

		// @todo Any @keyframes rules could be removed from amp-custom and instead added to amp-keyframes.
		$is_keyframes = $element->hasAttribute( 'amp-keyframes' );
		$stylesheet   = trim( $element->textContent );
		$cdata_spec   = $is_keyframes ? $this->style_keyframes_cdata_spec : $this->style_custom_cdata_spec;
		if ( $stylesheet ) {

			$stylesheet = $this->process_stylesheet( $stylesheet, $element, array(
				'allowed_at_rules'   => $cdata_spec['css_spec']['allowed_at_rules'],
				'property_whitelist' => $cdata_spec['css_spec']['allowed_declarations'],
				'validate_keyframes' => $cdata_spec['css_spec']['validate_keyframes'],
			) );

			$pending_stylesheet = array(
				'keyframes'  => $is_keyframes,
				'stylesheet' => $stylesheet,
				'node'       => $element,
			);
			if ( ! empty( $this->args['validation_error_callback'] ) ) {
				$pending_stylesheet['sources'] = AMP_Validation_Utils::locate_sources( $element ); // Needed because node is removed below.
			}
			$this->pending_stylesheets[] = $pending_stylesheet;
		}

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

		// Allow font URLs, including protocol-less URLs and recognized URLs that use HTTP instead of HTTPS.
		$normalized_font_href = preg_replace( '#^(http:)?(?=//)#', 'https:', $href );
		if ( $this->allowed_font_src_regex && preg_match( $this->allowed_font_src_regex, $normalized_font_href ) ) {
			if ( $href !== $normalized_font_href ) {
				$element->setAttribute( 'href', $normalized_font_href );
			}
			return;
		}

		$css_file_path = $this->get_validated_url_file_path( $href, array( 'css', 'less', 'scss', 'sass' ) );
		if ( is_wp_error( $css_file_path ) ) {
			$this->remove_invalid_child( $element, array(
				'code'    => $css_file_path->get_error_code(),
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

		$stylesheet = $this->process_stylesheet( $stylesheet, $element, array(
			'allowed_at_rules'   => $this->style_custom_cdata_spec['css_spec']['allowed_at_rules'],
			'property_whitelist' => $this->style_custom_cdata_spec['css_spec']['allowed_declarations'],
			'stylesheet_url'     => $href,
			'stylesheet_path'    => $css_file_path,
		) );

		$pending_stylesheet = array(
			'keyframes'  => false,
			'stylesheet' => $stylesheet,
			'node'       => $element,
		);
		if ( ! empty( $this->args['validation_error_callback'] ) ) {
			$pending_stylesheet['sources'] = AMP_Validation_Utils::locate_sources( $element ); // Needed because node is removed below.
		}
		$this->pending_stylesheets[] = $pending_stylesheet;

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
	 *
	 * @param string             $stylesheet Stylesheet.
	 * @param DOMElement|DOMAttr $node       Element (link/style) or style attribute where the stylesheet came from.
	 * @param array              $options {
	 *     Options.
	 *
	 *     @type bool     $class_selector_tree_shaking Whether to perform tree shaking to delete rules that reference class names not extant in the current document.
	 *     @type string[] $property_whitelist          Exclusively-allowed properties.
	 *     @type string[] $property_blacklist          Disallowed properties.
	 *     @type string   $stylesheet_url              Original URL for stylesheet when originating via link (or @import?).
	 *     @type string   $stylesheet_path             Original filesystem path for stylesheet when originating via link (or @import?).
	 *     @type array    $allowed_at_rules            Allowed @-rules.
	 *     @type bool     $validate_keyframes          Whether keyframes should be validated.
	 * }
	 * @return array Processed stylesheet parts.
	 */
	private function process_stylesheet( $stylesheet, $node, $options = array() ) {
		$cache_impacting_options = wp_array_slice_assoc(
			$options,
			array( 'property_whitelist', 'property_blacklist', 'stylesheet_url', 'allowed_at_rules' )
		);

		$cache_key = md5( $stylesheet . serialize( $cache_impacting_options ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize

		$cache_group = 'amp-parsed-stylesheet-v4';
		if ( wp_using_ext_object_cache() ) {
			$parsed = wp_cache_get( $cache_key, $cache_group );
		} else {
			$parsed = get_transient( $cache_key . $cache_group );
		}
		if ( ! $parsed || ! isset( $parsed['stylesheet'] ) || ! is_array( $parsed['stylesheet'] ) ) {
			$parsed = $this->parse_stylesheet( $stylesheet, $options );
			if ( wp_using_ext_object_cache() ) {
				wp_cache_set( $cache_key, $parsed, $cache_group );
			} else {
				// The expiration is to ensure transient doesn't stick around forever since no LRU flushing like with external object cache.
				set_transient( $cache_key . $cache_group, $parsed, MONTH_IN_SECONDS );
			}
		}

		if ( ! empty( $this->args['validation_error_callback'] ) && ! empty( $parsed['validation_errors'] ) ) {
			foreach ( $parsed['validation_errors'] as $validation_error ) {
				call_user_func( $this->args['validation_error_callback'], array_merge( $validation_error, compact( 'node' ) ) );
			}
		}

		return $parsed['stylesheet'];
	}

	/**
	 * Parse stylesheet.
	 *
	 * @since 1.0
	 *
	 * @param string $stylesheet_string Stylesheet.
	 * @param array  $options           Options. See definition in \AMP_Style_Sanitizer::process_stylesheet().
	 * @return array {
	 *    Parsed stylesheet.
	 *
	 *    @type array $stylesheet        Stylesheet parts, where arrays are tuples for declaration blocks.
	 *    @type array $validation_errors Validation errors.
	 * }
	 */
	private function parse_stylesheet( $stylesheet_string, $options = array() ) {
		$start_time = microtime( true );

		$options = array_merge(
			array(
				'allowed_at_rules'   => array(),
				'property_blacklist' => array(
					// See <https://www.ampproject.org/docs/design/responsive/style_pages#disallowed-styles>.
					'behavior',
					'-moz-binding',
				),
				'property_whitelist' => array(),
				'validate_keyframes' => false,
				'stylesheet_url'     => null,
				'stylesheet_path'    => null,
			),
			$options
		);

		// Find calc() functions and replace with placeholders since PHP-CSS-Parser can't handle them.
		$stylesheet_string = $this->add_calc_placeholders( $stylesheet_string );

		$stylesheet        = array();
		$validation_errors = array();
		try {
			$parser_settings = Sabberworm\CSS\Settings::create();
			$css_parser      = new Sabberworm\CSS\Parser( $stylesheet_string, $parser_settings );
			$css_document    = $css_parser->parse();

			if ( ! empty( $options['stylesheet_url'] ) ) {
				$this->real_path_urls(
					array_filter(
						$css_document->getAllValues(),
						function ( $value ) {
							return $value instanceof URL;
						}
					),
					$options['stylesheet_url']
				);
			}

			$validation_errors = $this->process_css_list( $css_document, $options );

			$output_format = Sabberworm\CSS\OutputFormat::createCompact();
			$output_format->setSemicolonAfterLastRule( false );

			$before_declaration_block          = '/*AMP_WP_BEFORE_DECLARATION_BLOCK*/';
			$between_selectors                 = '/*AMP_WP_BETWEEN_SELECTORS*/';
			$after_declaration_block_selectors = '/*AMP_WP_BEFORE_DECLARATION_SELECTORS*/';
			$after_declaration_block           = '/*AMP_WP_AFTER_DECLARATION*/';

			$output_format->set( 'BeforeDeclarationBlock', $before_declaration_block );
			$output_format->set( 'SpaceBeforeSelectorSeparator', $between_selectors );
			$output_format->set( 'AfterDeclarationBlockSelectors', $after_declaration_block_selectors );
			$output_format->set( 'AfterDeclarationBlock', $after_declaration_block );

			$stylesheet_string = $css_document->render( $output_format );

			$pattern  = '#';
			$pattern .= '(' . preg_quote( $before_declaration_block, '#' ) . ')';
			$pattern .= '(.+?)';
			$pattern .= preg_quote( $after_declaration_block_selectors, '#' );
			$pattern .= '(.+?)';
			$pattern .= preg_quote( $after_declaration_block, '#' );
			$pattern .= '#s';

			$split_stylesheet = preg_split( $pattern, $stylesheet_string, -1, PREG_SPLIT_DELIM_CAPTURE );
			$length           = count( $split_stylesheet );
			for ( $i = 0; $i < $length; $i++ ) {
				if ( $before_declaration_block === $split_stylesheet[ $i ] ) {
					$selectors   = explode( $between_selectors . ',', $split_stylesheet[ ++$i ] );
					$declaration = $split_stylesheet[ ++$i ];

					$selectors_parsed = array();
					foreach ( $selectors as $selector ) {
						$selectors_parsed[ $selector ] = array();

						// Remove :not() and pseudo selectors to eliminate false negatives, such as with `body:not(.title-tagline-hidden) .site-branding-text`.
						$reduced_selector = preg_replace( '/:[a-zA-Z0-9_-]+(\(.+?\))?/', '', $selector );

						// Remove attribute selectors to eliminate false negative, such as with `.social-navigation a[href*="example.com"]:before`.
						$reduced_selector = preg_replace( '/\[\w.*?\]/', '', $reduced_selector );

						$reduced_selector = preg_replace_callback(
							'/\.([a-zA-Z0-9_-]+)/',
							function( $matches ) use ( $selector, &$selectors_parsed ) {
								$selectors_parsed[ $selector ]['classes'][] = $matches[1];
								return '';
							},
							$reduced_selector
						);
						$reduced_selector = preg_replace_callback(
							'/#([a-zA-Z0-9_-]+)/',
							function( $matches ) use ( $selector, &$selectors_parsed ) {
								$selectors_parsed[ $selector ]['ids'][] = $matches[1];
								return '';
							},
							$reduced_selector
						);

						if ( preg_match_all( '/[a-zA-Z0-9_-]+/', $reduced_selector, $matches ) ) {
							$selectors_parsed[ $selector ]['tags'] = $matches[0];
						}
					}

					// Restore calc() functions that were replaced with placeholders.
					if ( ! empty( $this->calc_placeholders ) ) {
						$declaration = str_replace(
							array_keys( $this->calc_placeholders ),
							array_values( $this->calc_placeholders ),
							$declaration
						);
					}

					$stylesheet[] = array(
						$selectors_parsed,
						$declaration,
					);
				} else {
					$stylesheet[] = $split_stylesheet[ $i ];
				}
			}

			// Reset calc placeholders.
			$this->calc_placeholders = array();
		} catch ( Exception $exception ) {
			$validation_errors[] = array(
				'code'    => 'css_parse_error',
				'message' => $exception->getMessage(),
			);
		}

		$this->parse_css_duration += ( microtime( true ) - $start_time );

		return compact( 'stylesheet', 'validation_errors' );
	}

	/**
	 * Add placeholders for calc() functions which the PHP-CSS-Parser doesn't handle them properly yet.
	 *
	 * @since 1.0
	 * @link https://github.com/sabberworm/PHP-CSS-Parser/issues/79
	 *
	 * @param string $css CSS.
	 * @return string CSS with calc() functions replaced with placeholders.
	 */
	private function add_calc_placeholders( $css ) {
		$offset = 0;
		while ( preg_match( '/(?:-\w+-)?\bcalc\(/', $css, $matches, PREG_OFFSET_CAPTURE, $offset ) ) {
			$match_string = $matches[0][0];
			$match_offset = $matches[0][1];
			$css_length   = strlen( $css );
			$open_parens  = 1;
			$start_offset = $match_offset + strlen( $match_string );
			$final_offset = $start_offset;
			for ( ; $final_offset < $css_length; $final_offset++ ) {
				if ( '(' === $css[ $final_offset ] ) {
					$open_parens++;
				} elseif ( ')' === $css[ $final_offset ] ) {
					$open_parens--;
				} elseif ( ';' === $css[ $final_offset ] || '}' === $css[ $final_offset ] ) {
					break; // Stop looking since clearly came to the end of the property. Unbalanced parentheses.
				}

				// Found the end of the calc() function, so replace it with a placeholder function.
				if ( 0 === $open_parens ) {
					$matched_calc = substr( $css, $match_offset, $final_offset - $match_offset + 1 );
					$placeholder  = sprintf( '-wp-calc-placeholder(%d)', count( $this->calc_placeholders ) );

					// Store the placeholder function so the original calc() can be put in its place.
					$this->calc_placeholders[ $placeholder ] = $matched_calc;

					// Update the CSS to replace the matched calc() with the placeholder function.
					$css = substr( $css, 0, $match_offset ) . $placeholder . substr( $css, $final_offset + 1 );

					// Update offset based on difference of length of placeholder vs original matched calc().
					$final_offset += strlen( $placeholder ) - strlen( $matched_calc );
					break;
				}
			}

			// Start matching at the next byte after the match.
			$offset = $final_offset + 1;
		}
		return $css;
	}

	/**
	 * Process CSS list.
	 *
	 * @since 1.0
	 *
	 * @param CSSList $css_list CSS List.
	 * @param array   $options Options.
	 * @return array Validation errors.
	 */
	private function process_css_list( CSSList $css_list, $options ) {
		$validation_errors = array();

		foreach ( $css_list->getContents() as $css_item ) {
			if ( $css_item instanceof DeclarationBlock && empty( $options['validate_keyframes'] ) ) {
				$validation_errors = array_merge(
					$validation_errors,
					$this->process_css_declaration_block( $css_item, $css_list, $options )
				);
			} elseif ( $css_item instanceof AtRuleBlockList ) {
				if ( in_array( $css_item->atRuleName(), $options['allowed_at_rules'], true ) ) {
					$validation_errors = array_merge(
						$validation_errors,
						$this->process_css_list( $css_item, $options )
					);
				} else {
					$validation_errors[] = array(
						'code'    => 'illegal_css_at_rule',
						/* translators: %s is the CSS at-rule name. */
						'message' => sprintf( __( 'CSS @%s rules are currently disallowed.', 'amp' ), $css_item->atRuleName() ),
					);
					$css_list->remove( $css_item );
				}
			} elseif ( $css_item instanceof Import ) {
				$validation_errors[] = array(
					'code'    => 'illegal_css_import_rule',
					'message' => __( 'CSS @import is currently disallowed.', 'amp' ),
				);
				$css_list->remove( $css_item );
			} elseif ( $css_item instanceof AtRuleSet ) {
				if ( in_array( $css_item->atRuleName(), $options['allowed_at_rules'], true ) ) {
					$validation_errors = array_merge(
						$validation_errors,
						$this->process_css_declaration_block( $css_item, $css_list, $options )
					);
				} else {
					$validation_errors[] = array(
						'code'    => 'illegal_css_at_rule',
						/* translators: %s is the CSS at-rule name. */
						'message' => sprintf( __( 'CSS @%s rules are currently disallowed.', 'amp' ), $css_item->atRuleName() ),
					);
					$css_list->remove( $css_item );
				}
			} elseif ( $css_item instanceof KeyFrame ) {
				if ( in_array( 'keyframes', $options['allowed_at_rules'], true ) ) {
					$validation_errors = array_merge(
						$validation_errors,
						$this->process_css_keyframes( $css_item, $options )
					);
				} else {
					$validation_errors[] = array(
						'code'    => 'illegal_css_at_rule',
						/* translators: %s is the CSS at-rule name. */
						'message' => sprintf( __( 'CSS @%s rules are currently disallowed.', 'amp' ), $css_item->atRuleName() ),
					);
				}
			} elseif ( $css_item instanceof AtRule ) {
				$validation_errors[] = array(
					'code'    => 'illegal_css_at_rule',
					/* translators: %s is the CSS at-rule name. */
					'message' => sprintf( __( 'CSS @%s rules are currently disallowed.', 'amp' ), $css_item->atRuleName() ),
				);
				$css_list->remove( $css_item );
			} else {
				$validation_errors[] = array(
					'code'    => 'unrecognized_css',
					'message' => __( 'Unrecognized CSS removed.', 'amp' ),
				);
				$css_list->remove( $css_item );
			}
		}
		return $validation_errors;
	}

	/**
	 * Convert URLs in to non-relative real-paths.
	 *
	 * @param URL[]  $urls           URLs.
	 * @param string $stylesheet_url Stylesheet URL.
	 */
	private function real_path_urls( $urls, $stylesheet_url ) {
		$base_url = preg_replace( ':[^/]+(\?.*)?(#.*)?$:', '', $stylesheet_url );
		if ( empty( $base_url ) ) {
			return;
		}
		foreach ( $urls as $url ) {
			$url_string = $url->getURL()->getString();
			if ( 'data:' === substr( $url_string, 0, 5 ) ) {
				continue;
			}

			$parsed_url = wp_parse_url( $url_string );
			if ( ! empty( $parsed_url['host'] ) || empty( $parsed_url['path'] ) || '/' === substr( $parsed_url['path'], 0, 1 ) ) {
				continue;
			}

			$relative_url = preg_replace( '#^\./#', '', $url->getURL()->getString() );

			$real_url = $base_url . $relative_url;
			do {
				$real_url = preg_replace( '#[^/]+/../#', '', $real_url, -1, $count );
			} while ( 0 !== $count );

			$url->getURL()->setString( $real_url );
		}
	}

	/**
	 * Process CSS rule set.
	 *
	 * @since 1.0
	 * @link https://www.ampproject.org/docs/design/responsive/style_pages#disallowed-styles
	 * @link https://www.ampproject.org/docs/design/responsive/style_pages#restricted-styles
	 *
	 * @param RuleSet $ruleset  Ruleset.
	 * @param CSSList $css_list CSS List.
	 * @param array   $options  Options.
	 *
	 * @return array Validation errors.
	 */
	private function process_css_declaration_block( RuleSet $ruleset, CSSList $css_list, $options ) {
		$validation_errors = array();

		// Remove disallowed properties.
		if ( ! empty( $options['property_whitelist'] ) ) {
			$properties = $ruleset->getRules();
			foreach ( $properties as $property ) {
				$vendorless_property_name = preg_replace( '/^-\w+-/', '', $property->getRule() );
				if ( ! in_array( $vendorless_property_name, $options['property_whitelist'], true ) ) {
					$validation_errors[] = array(
						'code'           => 'illegal_css_property',
						'property_name'  => $property->getRule(),
						'property_value' => $property->getValue(),
					);
					$ruleset->removeRule( $property->getRule() );
				}
			}
		} else {
			foreach ( $options['property_blacklist'] as $illegal_property_name ) {
				$properties = $ruleset->getRules( $illegal_property_name );
				foreach ( $properties as $property ) {
					$validation_errors[] = array(
						'code'           => 'illegal_css_property',
						'property_name'  => $property->getRule(),
						'property_value' => $property->getValue(),
					);
					$ruleset->removeRule( $property->getRule() );
				}
			}
		}

		if ( $ruleset instanceof AtRuleSet && 'font-face' === $ruleset->atRuleName() ) {
			$this->process_font_face_at_rule( $ruleset, $options );
		}

		$validation_errors = array_merge(
			$validation_errors,
			$this->transform_important_qualifiers( $ruleset, $css_list )
		);

		// Remove the ruleset if it is now empty.
		if ( 0 === count( $ruleset->getRules() ) ) {
			$css_list->remove( $ruleset );
		}
		// @todo Delete rules with selectors for -amphtml- class and i-amphtml- tags.
		return $validation_errors;
	}

	/**
	 * Process @font-face by making src URLs non-relative and converting data: URLs into (assumed) file URLs.
	 *
	 * @since 1.0
	 *
	 * @param AtRuleSet $ruleset Ruleset for @font-face.
	 * @param array     $options Options.
	 */
	private function process_font_face_at_rule( AtRuleSet $ruleset, $options ) {
		$src_properties = $ruleset->getRules( 'src' );
		if ( empty( $src_properties ) ) {
			return;
		}

		foreach ( $src_properties as $src_property ) {
			$value = $src_property->getValue();
			if ( ! ( $value instanceof RuleValueList ) ) {
				continue;
			}

			/*
			 * The CSS Parser parses a src such as:
			 *
			 *    url(data:application/font-woff;...) format('woff'),
			 *    url('Genericons.ttf') format('truetype'),
			 *    url('Genericons.svg#genericonsregular') format('svg')
			 *
			 * As a list of components consisting of:
			 *
			 *    URL,
			 *    RuleValueList( CSSFunction, URL ),
			 *    RuleValueList( CSSFunction, URL ),
			 *    CSSFunction
			 *
			 * Clearly the components here are not logically grouped. So the first step is to fix the order.
			 */
			$sources = array();
			foreach ( $value->getListComponents() as $component ) {
				if ( $component instanceof RuleValueList ) {
					$subcomponents = $component->getListComponents();
					$subcomponent  = array_shift( $subcomponents );
					if ( $subcomponent ) {
						if ( empty( $sources ) ) {
							$sources[] = array( $subcomponent );
						} else {
							$sources[ count( $sources ) - 1 ][] = $subcomponent;
						}
					}
					foreach ( $subcomponents as $subcomponent ) {
						$sources[] = array( $subcomponent );
					}
				} else {
					if ( empty( $sources ) ) {
						$sources[] = array( $component );
					} else {
						$sources[ count( $sources ) - 1 ][] = $component;
					}
				}
			}

			/**
			 * Source URL lists.
			 *
			 * @var URL[] $source_file_urls
			 * @var URL[] $source_data_urls
			 */
			$source_file_urls = array();
			$source_data_urls = array();
			foreach ( $sources as $i => $source ) {
				if ( $source[0] instanceof URL ) {
					if ( 'data:' === substr( $source[0]->getURL()->getString(), 0, 5 ) ) {
						$source_data_urls[ $i ] = $source[0];
					} else {
						$source_file_urls[ $i ] = $source[0];
					}
				}
			}

			// Convert data: URLs into regular URLs, assuming there will be a file present (e.g. woff fonts in core themes).
			if ( empty( $source_file_urls ) ) {
				continue;
			}
			$source_file_url = current( $source_file_urls );
			foreach ( $source_data_urls as $i => $data_url ) {
				$mime_type = strtok( substr( $data_url->getURL()->getString(), 5 ), ';' );
				if ( ! $mime_type ) {
					continue;
				}
				$extension   = preg_replace( ':.+/(.+-)?:', '', $mime_type );
				$guessed_url = preg_replace(
					':(?<=\.)\w+(\?.*)?(#.*)?$:', // Match the file extension in the URL.
					$extension,
					$source_file_url->getURL()->getString(),
					1,
					$count
				);
				if ( 1 !== $count ) {
					continue;
				}

				// Ensure file exists.
				$path = $this->get_validated_url_file_path( $guessed_url );
				if ( is_wp_error( $path ) ) {
					continue;
				}

				$data_url->getURL()->setString( $guessed_url );
				break;
			}
		}
	}

	/**
	 * Process CSS keyframes.
	 *
	 * @since 1.0
	 * @link https://www.ampproject.org/docs/design/responsive/style_pages#restricted-styles.
	 * @link https://github.com/ampproject/amphtml/blob/b685a0780a7f59313666225478b2b79b463bcd0b/validator/validator-main.protoascii#L1002-L1043
	 * @todo Tree shaking could be extended to keyframes, to omit a keyframe if it is not referenced by any rule.
	 *
	 * @param KeyFrame $css_list Ruleset.
	 * @param array    $options  Options.
	 * @return array Validation errors.
	 */
	private function process_css_keyframes( KeyFrame $css_list, $options ) {
		$validation_errors = array();
		if ( ! empty( $options['property_whitelist'] ) ) {
			foreach ( $css_list->getContents() as $rules ) {
				if ( ! ( $rules instanceof DeclarationBlock ) ) {
					$validation_errors[] = array(
						'code'    => 'unrecognized_css',
						'message' => __( 'Unrecognized CSS removed.', 'amp' ),
					);
					$css_list->remove( $rules );
					continue;
				}

				$validation_errors = array_merge(
					$validation_errors,
					$this->transform_important_qualifiers( $rules, $css_list )
				);

				$properties = $rules->getRules();
				foreach ( $properties as $property ) {
					$vendorless_property_name = preg_replace( '/^-\w+-/', '', $property->getRule() );
					if ( ! in_array( $vendorless_property_name, $options['property_whitelist'], true ) ) {
						$validation_errors[] = array(
							'code'           => 'illegal_css_property',
							'property_name'  => $property->getRule(),
							'property_value' => $property->getValue(),
						);
						$rules->removeRule( $property->getRule() );
					}
				}
			}
		}
		return $validation_errors;
	}

	/**
	 * Replace !important qualifiers with more specific rules.
	 *
	 * @since 1.0
	 * @see https://www.npmjs.com/package/replace-important
	 * @see https://www.ampproject.org/docs/fundamentals/spec#important
	 *
	 * @param RuleSet|DeclarationBlock $ruleset  Rule set.
	 * @param CSSList                  $css_list CSS List.
	 * @return array Validation errors.
	 */
	private function transform_important_qualifiers( RuleSet $ruleset, CSSList $css_list ) {
		$validation_errors    = array();
		$allow_transformation = (
			$ruleset instanceof DeclarationBlock
			&&
			! ( $css_list instanceof KeyFrame )
		);

		$properties = $ruleset->getRules();
		$importants = array();
		foreach ( $properties as $property ) {
			if ( $property->getIsImportant() ) {
				$property->setIsImportant( false );

				// An !important doesn't make sense for rulesets that don't have selectors.
				if ( $allow_transformation ) {
					$importants[] = $property;
					$ruleset->removeRule( $property->getRule() );
				} else {
					$validation_errors[] = array(
						'code'    => 'illegal_css_important',
						'message' => __( 'Illegal CSS !important qualifier.', 'amp' ),
					);
				}
			}
		}
		if ( ! $allow_transformation || empty( $importants ) ) {
			return $validation_errors;
		}

		$important_ruleset = clone $ruleset;
		$important_ruleset->setSelectors( array_map(
			/**
			 * Modify selectors to be more specific to roughly match the effect of !important.
			 *
			 * @link https://github.com/ampproject/ampstart/blob/4c21d69afdd07b4c60cd190937bda09901955829/tools/replace-important/lib/index.js#L88-L109
			 *
			 * @param Selector $old_selector Original selector.
			 * @return Selector The new more-specific selector.
			 */
			function( Selector $old_selector ) {
				$specific = ':not(#_)'; // Here "_" is just a short single-char ID.

				$selector_mod = str_repeat( $specific, floor( $old_selector->getSpecificity() / 100 ) );
				if ( $old_selector->getSpecificity() % 100 > 0 ) {
					$selector_mod .= $specific;
				}
				if ( $old_selector->getSpecificity() % 10 > 0 ) {
					$selector_mod .= $specific;
				}

				$new_selector = $old_selector->getSelector();

				// Amend the selector mod to the first element in selector if it is already the root; otherwise add new root ancestor.
				if ( preg_match( '/^\s*(html|:root)\b/i', $new_selector, $matches ) ) {
					$new_selector = substr( $new_selector, 0, strlen( $matches[0] ) ) . $selector_mod . substr( $new_selector, strlen( $matches[0] ) );
				} else {
					$new_selector = sprintf( ':root%s %s', $selector_mod, $new_selector );
				}
				return new Selector( $new_selector );
			},
			$ruleset->getSelectors()
		) );
		$important_ruleset->setRules( $importants );
		$css_list->append( $important_ruleset ); // @todo It would be preferable if the important ruleset were inserted adjacent to the original rule.

		return $validation_errors;
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

		$class = 'amp-wp-' . substr( md5( $style_attribute->nodeValue ), 0, 7 );
		$root  = ':root' . str_repeat( ':not(#_)', 5 ); // @todo The correctness of using "5" should be validated.
		$rule  = sprintf( '%s .%s { %s }', $root, $class, $style_attribute->nodeValue );

		$stylesheet = $this->process_stylesheet( $rule, $style_attribute, array(
			'allowed_at_rules'   => array(),
			'property_whitelist' => $this->style_custom_cdata_spec['css_spec']['allowed_declarations'],
		) );

		if ( empty( $stylesheet ) ) {
			$element->removeAttribute( 'style' );
			return;
		}

		$pending_stylesheet = array(
			'stylesheet' => $stylesheet,
			'node'       => $element,
			'keyframes'  => false,
		);
		if ( ! empty( $this->args['validation_error_callback'] ) ) {
			$pending_stylesheet['sources'] = AMP_Validation_Utils::locate_sources( $element ); // Needed because node is removed below.
		}

		$this->pending_stylesheets[] = $pending_stylesheet;

		$element->removeAttribute( 'style' );
		if ( $element->hasAttribute( 'class' ) ) {
			$element->setAttribute( 'class', $element->getAttribute( 'class' ) . ' ' . $class );
		} else {
			$element->setAttribute( 'class', $class );
		}
	}

	/**
	 * Finalize stylesheets for style[amp-custom] and style[amp-keyframes] elements.
	 *
	 * Concatenate all pending stylesheets, remove unused rules if necessary, and add to style elements in doc.
	 * Combine all amp-keyframe styles and add them to the end of the body.
	 *
	 * @since 1.0
	 * @see https://www.ampproject.org/docs/fundamentals/spec#keyframes-stylesheet
	 */
	private function finalize_styles() {

		$stylesheet_sets = array(
			'custom'    => array(
				'total_size'          => 0,
				'cdata_spec'          => $this->style_custom_cdata_spec,
				'pending_stylesheets' => array(),
				'final_stylesheets'   => array(),
				'remove_unused_rules' => $this->args['remove_unused_rules'],
			),
			'keyframes' => array(
				'total_size'          => 0,
				'cdata_spec'          => $this->style_keyframes_cdata_spec,
				'pending_stylesheets' => array(),
				'final_stylesheets'   => array(),
				'remove_unused_rules' => 'never', // Not relevant.
			),
		);

		// Divide pending stylesheet between custom and keyframes, and calculate size of each.
		while ( ! empty( $this->pending_stylesheets ) ) {
			$pending_stylesheet = array_shift( $this->pending_stylesheets );

			$set_name = ! empty( $pending_stylesheet['keyframes'] ) ? 'keyframes' : 'custom';
			$size     = 0;
			foreach ( $pending_stylesheet['stylesheet'] as $part ) {
				if ( is_string( $part ) ) {
					$size += strlen( $part );
				} elseif ( is_array( $part ) ) {
					$size += strlen( implode( ',', array_keys( $part[0] ) ) ); // Selectors.
					$size += strlen( $part[1] ); // Declaration block.
				}
			}
			$stylesheet_sets[ $set_name ]['total_size']           += $size;
			$stylesheet_sets[ $set_name ]['pending_stylesheets'][] = $pending_stylesheet;
		}

		// Process the pending stylesheets.
		foreach ( array_keys( $stylesheet_sets ) as $set_name ) {
			$stylesheet_sets[ $set_name ] = $this->finalize_stylesheet_set( $stylesheet_sets[ $set_name ] );
		}

		$this->stylesheets = $stylesheet_sets['custom']['final_stylesheets'];

		// If we're not working with the document element (e.g. for legacy post templates) then there is nothing left to do.
		if ( empty( $this->args['use_document_element'] ) ) {
			return;
		}

		// Add style[amp-custom] to document.
		if ( ! empty( $stylesheet_sets['custom']['final_stylesheets'] ) ) {

			// Ensure style[amp-custom] is present in the document.
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

			$css = implode( '', $stylesheet_sets['custom']['final_stylesheets'] );

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

			$included_size    = 0;
			$included_sources = array();
			foreach ( $stylesheet_sets['custom']['pending_stylesheets'] as $i => $pending_stylesheet ) {
				if ( ! ( $pending_stylesheet['node'] instanceof DOMElement ) ) {
					continue;
				}
				$message = $pending_stylesheet['node']->nodeName;
				if ( $pending_stylesheet['node']->getAttribute( 'id' ) ) {
					$message .= '#' . $pending_stylesheet['node']->getAttribute( 'id' );
				}
				if ( $pending_stylesheet['node']->getAttribute( 'class' ) ) {
					$message .= '.' . $pending_stylesheet['node']->getAttribute( 'class' );
				}
				foreach ( $pending_stylesheet['node']->attributes as $attribute ) {
					if ( 'id' !== $attribute->nodeName || 'class' !== $attribute->nodeName ) {
						$message .= sprintf( '[%s=%s]', $attribute->nodeName, $attribute->nodeValue );
					}
				}
				$message .= sprintf(
					/* translators: %d is number of bytes */
					_n( ' (%d byte)', ' (%d bytes)', $pending_stylesheet['size'], 'amp' ),
					$pending_stylesheet['size']
				);

				if ( ! empty( $pending_stylesheet['included'] ) ) {
					$included_sources[] = $message;
					$included_size     += $pending_stylesheet['size'];
				} else {
					$excluded_sources[] = $message;
				}
			}
			$comment = '';
			if ( ! empty( $included_sources ) ) {
				$comment .= esc_html__( 'The style[amp-custom] element is populated with:', 'amp' ) . "\n- " . implode( "\n- ", $included_sources ) . "\n";
				/* translators: %d is number of bytes */
				$comment .= sprintf( esc_html__( 'Total size: %d bytes', 'amp' ), $included_size ) . "\n";
			}
			if ( ! empty( $excluded_sources ) ) {
				if ( $comment ) {
					$comment .= "\n";
				}
				$comment .= esc_html__( 'The following stylesheets are too large to be included in style[amp-custom]:', 'amp' ) . "\n- " . implode( "\n- ", $excluded_sources ) . "\n";
			}
			if ( $comment ) {
				$this->amp_custom_style_element->parentNode->insertBefore(
					$this->dom->createComment( "\n$comment" ),
					$this->amp_custom_style_element
				);
			}
		}

		// Add style[amp-keyframes] to document.
		if ( ! empty( $stylesheet_sets['keyframes']['final_stylesheets'] ) ) {
			$body = $this->dom->getElementsByTagName( 'body' )->item( 0 );
			if ( ! $body ) {
				if ( ! empty( $this->args['validation_error_callback'] ) ) {
					call_user_func( $this->args['validation_error_callback'], array(
						'code'    => 'missing_body_element',
						'message' => __( 'amp-keyframes must be last child of body element.', 'amp' ),
					) );
				}
			} else {
				$style_element = $this->dom->createElement( 'style' );
				$style_element->setAttribute( 'amp-keyframes', '' );
				$style_element->appendChild( $this->dom->createTextNode( implode( '', $stylesheet_sets['keyframes']['final_stylesheets'] ) ) );
				$body->appendChild( $style_element );
			}
		}
	}

	/**
	 * Finalize a stylesheet set (amp-custom or amp-keyframes).
	 *
	 * @since 1.0
	 *
	 * @param array $stylesheet_set Stylesheet set.
	 * @return array Finalized stylesheet set.
	 */
	private function finalize_stylesheet_set( $stylesheet_set ) {
		$is_too_much_css   = $stylesheet_set['total_size'] > $stylesheet_set['cdata_spec']['max_bytes'];
		$should_tree_shake = (
			'always' === $stylesheet_set['remove_unused_rules'] || (
				$is_too_much_css
				&&
				'sometimes' === $stylesheet_set['remove_unused_rules']
			)
		);

		if ( $is_too_much_css && $should_tree_shake && ! empty( $this->args['validation_error_callback'] ) ) {
			call_user_func( $this->args['validation_error_callback'], array(
				'code'    => 'removed_unused_css_rules',
				'message' => __( 'Too much CSS is enqueued and so seemingly irrelevant rules have been removed.', 'amp' ),
			) );
		}

		$dynamic_selector_pattern = null;
		if ( $should_tree_shake && ! empty( $this->args['dynamic_element_selectors'] ) ) {
			$dynamic_selector_pattern = '#' . implode( '|', array_map(
				function( $selector ) {
					return preg_quote( $selector, '#' );
				},
				$this->args['dynamic_element_selectors']
			) ) . '#';
		}

		$stylesheet_set['processed_nodes'] = array();

		$final_size = 0;
		$dom        = $this->dom;
		foreach ( $stylesheet_set['pending_stylesheets'] as &$pending_stylesheet ) {
			$stylesheet = '';
			foreach ( $pending_stylesheet['stylesheet'] as $stylesheet_part ) {
				if ( is_string( $stylesheet_part ) ) {
					$stylesheet .= $stylesheet_part;
				} else {
					list( $selectors_parsed, $declaration_block ) = $stylesheet_part;
					if ( $should_tree_shake ) {
						$selectors = array();
						foreach ( $selectors_parsed as $selector => $parsed_selector ) {
							$should_include = (
								( $dynamic_selector_pattern && preg_match( $dynamic_selector_pattern, $selector ) )
								||
								(
									// If all class names are used in the doc.
									(
										empty( $parsed_selector['classes'] )
										||
										0 === count( array_diff( $parsed_selector['classes'], $this->get_used_class_names() ) )
									)
									&&
									// If all IDs are used in the doc.
									(
										empty( $parsed_selector['ids'] )
										||
										0 === count( array_filter( $parsed_selector['ids'], function( $id ) use ( $dom ) {
											return ! $dom->getElementById( $id );
										} ) )
									)
									&&
									// If tag names are present in the doc.
									(
										empty( $parsed_selector['tags'] )
										||
										0 === count( array_diff( $parsed_selector['tags'], $this->get_used_tag_names() ) )
									)
								)
							);
							if ( $should_include ) {
								$selectors[] = $selector;
							}
						}
					} else {
						$selectors = array_keys( $selectors_parsed );
					}
					if ( ! empty( $selectors ) ) {
						$stylesheet .= implode( ',', $selectors ) . $declaration_block;
					}
				}
			}
			$sheet_size                 = strlen( $stylesheet );
			$pending_stylesheet['size'] = $sheet_size;

			// Skip considering stylesheet if an identical one has already been processed.
			$hash = md5( $stylesheet );
			if ( isset( $stylesheet_set['final_stylesheets'][ $hash ] ) ) {
				$pending_stylesheet['included'] = true;
				continue;
			}

			// Report validation error if size is now too big.
			if ( $final_size + $sheet_size > $stylesheet_set['cdata_spec']['max_bytes'] ) {
				if ( ! empty( $this->args['validation_error_callback'] ) ) {
					$validation_error = array(
						'code'    => 'excessive_css',
						'message' => sprintf(
							/* translators: %d is the number of bytes over the limit */
							__( 'Too much CSS output (by %d bytes).', 'amp' ),
							( $final_size + $sheet_size ) - $stylesheet_set['cdata_spec']['max_bytes']
						),
						'node'    => $pending_stylesheet['node'],
					);
					if ( isset( $pending_stylesheet['sources'] ) ) {
						$validation_error['sources'] = $pending_stylesheet['sources'];
					}
					call_user_func( $this->args['validation_error_callback'], $validation_error );
				}
				$pending_stylesheet['included'] = false;
			} else {
				$final_size += $sheet_size;

				$stylesheet_set['final_stylesheets'][ $hash ] = $stylesheet;
				$pending_stylesheet['included']               = true;
			}
		}

		return $stylesheet_set;
	}
}
