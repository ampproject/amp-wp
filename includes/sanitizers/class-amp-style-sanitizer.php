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
use \Sabberworm\CSS\Property\AtRule;
use \Sabberworm\CSS\CSSList\KeyFrame;
use \Sabberworm\CSS\RuleSet\AtRuleSet;
use \Sabberworm\CSS\Property\Import;
use \Sabberworm\CSS\CSSList\AtRuleBlockList;
use \Sabberworm\CSS\Value\RuleValueList;
use \Sabberworm\CSS\Value\URL;
use \Sabberworm\CSS\CSSList\Document;

/**
 * Class AMP_Style_Sanitizer
 *
 * Collects inline styles and outputs them in the amp-custom stylesheet.
 */
class AMP_Style_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Error code for tree shaking.
	 *
	 * @var string
	 */
	const TREE_SHAKING_ERROR_CODE = 'removed_unused_css_rules';

	/**
	 * Error code for illegal at-rule.
	 *
	 * @var string
	 */
	const ILLEGAL_AT_RULE_ERROR_CODE = 'illegal_css_at_rule';

	/**
	 * Inline style selector's specificity multiplier, i.e. used to generate the number of ':not(#_)' placeholders.
	 *
	 * @var int
	 */
	const INLINE_SPECIFICITY_MULTIPLIER = 5; // @todo The correctness of using "5" should be validated.

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
	 *      @type bool     $should_locate_sources      Whether to locate the sources when reporting validation errors.
	 *      @type string   $parsed_cache_variant       Additional value by which to vary parsed cache.
	 *      @type bool     $accept_tree_shaking        Whether to accept tree-shaking by default and bypass a validation error.
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
		'should_locate_sources'     => false,
		'parsed_cache_variant'      => null,
		'accept_tree_shaking'       => false,
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
	 * THe HEAD element.
	 *
	 * @var DOMElement
	 */
	private $head;

	/**
	 * Current node being processed.
	 *
	 * @var DOMElement|DOMAttr
	 */
	private $current_node;

	/**
	 * Current sources for a given node.
	 *
	 * @var array
	 */
	private $current_sources;

	/**
	 * Log of the stylesheet URLs that have been imported to guard against infinite loops.
	 *
	 * @var array
	 */
	private $processed_imported_stylesheet_urls = array();

	/**
	 * List of font stylesheets that were @import'ed which should have been <link>'ed to instead.
	 *
	 * These font URLs will be cached with the parsed CSS and then converted into stylesheet links.
	 *
	 * @var array
	 */
	private $imported_font_urls = array();

	/**
	 * Mapping of HTML element selectors to AMP selector elements.
	 *
	 * @var array
	 */
	private $selector_mappings = array();

	/**
	 * Get error codes that can be raised during parsing of CSS.
	 *
	 * This is used to determine which validation errors should be taken into account
	 * when determining which validation errors should vary the parse cache.
	 *
	 * @return array
	 */
	public static function get_css_parser_validation_error_codes() {
		return array(
			'css_parse_error',
			'excessive_css',
			self::ILLEGAL_AT_RULE_ERROR_CODE,
			'illegal_css_important',
			'illegal_css_property',
			self::TREE_SHAKING_ERROR_CODE,
			'unrecognized_css',
			'disallowed_file_extension',
			'file_path_not_found',
		);
	}

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
	 * Run logic before any sanitizers are run.
	 *
	 * After the sanitizers are instantiated but before calling sanitize on each of them, this
	 * method is called with list of all the instantiated sanitizers.
	 *
	 * @param AMP_Base_Sanitizer[] $sanitizers Sanitizers.
	 */
	public function init( $sanitizers ) {
		parent::init( $sanitizers );

		foreach ( $sanitizers as $sanitizer ) {
			foreach ( $sanitizer->get_selector_conversion_mapping() as $html_selectors => $amp_selectors ) {
				if ( ! isset( $this->selector_mappings[ $html_selectors ] ) ) {
					$this->selector_mappings[ $html_selectors ] = $amp_selectors;
				} else {
					$this->selector_mappings[ $html_selectors ] = array_unique(
						array_merge( $this->selector_mappings[ $html_selectors ], $amp_selectors )
					);
				}

				// Prevent selectors like `amp-img img` getting deleted since `img` does not occur in the DOM.
				$this->args['dynamic_element_selectors'] = array_merge(
					$this->args['dynamic_element_selectors'],
					$this->selector_mappings[ $html_selectors ]
				);
			}
		}
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

		$this->head = $this->dom->getElementsByTagName( 'head' )->item( 0 );
		if ( ! $this->head ) {
			$this->head = $this->dom->createElement( 'head' );
			$this->dom->documentElement->insertBefore( $this->head, $this->dom->documentElement->firstChild );
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
			/**
			 * Col element.
			 *
			 * @var DOMElement $col
			 */
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
			AMP_HTTP::send_server_timing( 'amp_parse_css', $this->parse_css_duration, 'AMP Parse CSS' );
		}
	}

	/**
	 * Generate a URL's fully-qualified file path.
	 *
	 * @since 0.7
	 * @see WP_Styles::_css_href()
	 *
	 * @param string   $url The file URL.
	 * @param string[] $allowed_extensions Allowed file extensions for local files.
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

		// Validate file extensions.
		if ( ! empty( $allowed_extensions ) ) {
			$pattern = sprintf( '/\.(%s)$/i', implode( '|', $allowed_extensions ) );
			if ( ! preg_match( $pattern, $url ) ) {
				/* translators: %s: the file URL. */
				return new WP_Error( 'disallowed_file_extension', sprintf( __( 'File does not have an allowed file extension for filesystem access (%s).', 'amp' ), $url ) );
			}
		}

		if ( ! in_array( $url_host, $allowed_hosts, true ) ) {
			/* translators: %s: the file URL */
			return new WP_Error( 'external_file_url', sprintf( __( 'URL is located on an external domain: %s.', 'amp' ), $url_host ) );
		}

		$base_path = null;
		$file_path = null;
		if ( 0 === strpos( $url, $content_url ) ) {
			$base_path = WP_CONTENT_DIR;
			$file_path = substr( $url, strlen( $content_url ) - 1 );
		} elseif ( 0 === strpos( $url, $includes_url ) ) {
			$base_path = ABSPATH . WPINC;
			$file_path = substr( $url, strlen( $includes_url ) - 1 );
		} elseif ( 0 === strpos( $url, $admin_url ) ) {
			$base_path = ABSPATH . 'wp-admin';
			$file_path = substr( $url, strlen( $admin_url ) - 1 );
		}

		if ( ! $file_path || false !== strpos( $file_path, '../' ) || false !== strpos( $file_path, '..\\' ) ) {
			/* translators: %s: the file URL. */
			return new WP_Error( 'file_path_not_allowed', sprintf( __( 'Disallowed URL filesystem path for %s.', 'amp' ), $url ) );
		}
		if ( ! file_exists( $base_path . $file_path ) ) {
			/* translators: %s: the file URL. */
			return new WP_Error( 'file_path_not_found', sprintf( __( 'Unable to locate filesystem path for %s.', 'amp' ), $url ) );
		}

		return $base_path . $file_path;
	}

	/**
	 * Set the current node (and its sources when required).
	 *
	 * @since 1.0
	 * @param DOMElement|DOMAttr|null $node Current node, or null to reset.
	 */
	private function set_current_node( $node ) {
		if ( $this->current_node === $node ) {
			return;
		}

		$this->current_node = $node;
		if ( empty( $node ) ) {
			$this->current_sources = null;
		} elseif ( ! empty( $this->args['should_locate_sources'] ) ) {
			$this->current_sources = AMP_Validation_Manager::locate_sources( $node );
		}
	}

	/**
	 * Process style element.
	 *
	 * @param DOMElement $element Style element.
	 */
	private function process_style_element( DOMElement $element ) {
		$this->set_current_node( $element ); // And sources when needing to be located.

		// @todo Any @keyframes rules could be removed from amp-custom and instead added to amp-keyframes.
		$is_keyframes = $element->hasAttribute( 'amp-keyframes' );
		$stylesheet   = trim( $element->textContent );
		$cdata_spec   = $is_keyframes ? $this->style_keyframes_cdata_spec : $this->style_custom_cdata_spec;

		// Honor the style's media attribute.
		$media = $element->getAttribute( 'media' );
		if ( $media && 'all' !== $media ) {
			$stylesheet = sprintf( '@media %s { %s }', $media, $stylesheet );
		}

		$processed = $this->process_stylesheet( $stylesheet, array(
			'allowed_at_rules'   => $cdata_spec['css_spec']['allowed_at_rules'],
			'property_whitelist' => $cdata_spec['css_spec']['declaration'],
			'validate_keyframes' => $cdata_spec['css_spec']['validate_keyframes'],
		) );

		$this->pending_stylesheets[] = array_merge(
			array(
				'keyframes' => $is_keyframes,
				'node'      => $element,
				'sources'   => $this->current_sources,
			),
			wp_array_slice_assoc( $processed, array( 'stylesheet', 'imported_font_urls' ) )
		);

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

		$this->set_current_node( null );
	}

	/**
	 * Process link element.
	 *
	 * @param DOMElement $element Link element.
	 */
	private function process_link_element( DOMElement $element ) {
		$href = $element->getAttribute( 'href' );

		// Allow font URLs, including protocol-less URLs and recognized URLs that use HTTP instead of HTTPS.
		$normalized_url = preg_replace( '#^(http:)?(?=//)#', 'https:', $href );
		if ( $this->allowed_font_src_regex && preg_match( $this->allowed_font_src_regex, $normalized_url ) ) {
			if ( $href !== $normalized_url ) {
				$element->setAttribute( 'href', $normalized_url );
			}

			/*
			 * Make sure rel=preconnect link is present for Google Fonts stylesheet.
			 * Note that core themes normally do this already, per <https://core.trac.wordpress.org/ticket/37171>.
			 * But not always, per <https://core.trac.wordpress.org/ticket/44668>.
			 * This also ensures that other themes will get the preconnect link when
			 * they don't implement the resource hint.
			 */
			$needs_preconnect_link = (
				'https://fonts.googleapis.com/' === substr( $normalized_url, 0, 29 )
				&&
				0 === $this->xpath->query( '//link[ @rel = "preconnect" and @crossorigin and starts-with( @href, "https://fonts.gstatic.com" ) ]', $this->head )->length
			);
			if ( $needs_preconnect_link ) {
				$link = AMP_DOM_Utils::create_node( $this->dom, 'link', array(
					'rel'         => 'preconnect',
					'href'        => 'https://fonts.gstatic.com/',
					'crossorigin' => '',
				) );
				$this->head->insertBefore( $link ); // Note that \AMP_Theme_Support::ensure_required_markup() will put this in the optimal order.
			}
			return;
		}

		$css_file_path = $this->get_validated_url_file_path( $href, array( 'css', 'less', 'scss', 'sass' ) );

		if ( is_wp_error( $css_file_path ) && ( 'disallowed_file_extension' === $css_file_path->get_error_code() || 'external_file_url' === $css_file_path->get_error_code() ) ) {
			$contents = $this->fetch_external_stylesheet( $normalized_url );
			if ( is_wp_error( $contents ) ) {
				$this->remove_invalid_child( $element, array(
					'code'    => $css_file_path->get_error_code(),
					'message' => $css_file_path->get_error_message(),
					'type'    => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
				) );
				return;
			} else {
				$stylesheet = $contents;
			}
		} elseif ( is_wp_error( $css_file_path ) ) {
			$this->remove_invalid_child( $element, array(
				'code'    => $css_file_path->get_error_code(),
				'message' => $css_file_path->get_error_message(),
				'type'    => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
			) );
			return;
		} else {
			$stylesheet = file_get_contents( $css_file_path ); // phpcs:ignore -- It's a local filesystem path not a remote request.
		}

		if ( false === $stylesheet ) {
			$this->remove_invalid_child( $element, array(
				'code' => 'stylesheet_file_missing',
				'type' => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
			) );
			return;
		}

		// Honor the link's media attribute.
		$media = $element->getAttribute( 'media' );
		if ( $media && 'all' !== $media ) {
			$stylesheet = sprintf( '@media %s { %s }', $media, $stylesheet );
		}

		$this->set_current_node( $element ); // And sources when needing to be located.

		$processed = $this->process_stylesheet( $stylesheet, array(
			'allowed_at_rules'   => $this->style_custom_cdata_spec['css_spec']['allowed_at_rules'],
			'property_whitelist' => $this->style_custom_cdata_spec['css_spec']['declaration'],
			'stylesheet_url'     => $href,
			'stylesheet_path'    => $css_file_path,
		) );

		$this->pending_stylesheets[] = array_merge(
			array(
				'keyframes' => false,
				'node'      => $element,
				'sources'   => $this->current_sources, // Needed because node is removed below.
			),
			wp_array_slice_assoc( $processed, array( 'stylesheet', 'imported_font_urls' ) )
		);

		// Remove now that styles have been processed.
		$element->parentNode->removeChild( $element );

		$this->set_current_node( null );
	}

	/**
	 * Fetch external stylesheet.
	 *
	 * @param string $url External stylesheet URL.
	 * @return string|WP_Error Stylesheet contents or WP_Error.
	 */
	private function fetch_external_stylesheet( $url ) {
		$cache_key = md5( $url );
		$contents  = get_transient( $cache_key );
		if ( false === $contents ) {
			$r = wp_remote_get( $url );
			if ( 200 !== wp_remote_retrieve_response_code( $r ) ) {
				$contents = new WP_Error(
					wp_remote_retrieve_response_code( $r ),
					wp_remote_retrieve_response_message( $r )
				);
			} else {
				$contents = wp_remote_retrieve_body( $r );
			}
			set_transient( $cache_key, $contents, MONTH_IN_SECONDS );
		}
		return $contents;
	}

	/**
	 * Process stylesheet.
	 *
	 * Sanitized invalid CSS properties and rules, removes rules which do not
	 * apply to the current document, and compresses the CSS to remove whitespace and comments.
	 *
	 * @since 1.0
	 *
	 * @param string $stylesheet Stylesheet.
	 * @param array  $options {
	 *     Options.
	 *
	 *     @type string[] $property_whitelist          Exclusively-allowed properties.
	 *     @type string[] $property_blacklist          Disallowed properties.
	 *     @type string   $stylesheet_url              Original URL for stylesheet when originating via link or @import.
	 *     @type string   $stylesheet_path             Original filesystem path for stylesheet when originating via link or @import.
	 *     @type array    $allowed_at_rules            Allowed @-rules.
	 *     @type bool     $validate_keyframes          Whether keyframes should be validated.
	 * }
	 * @return array {
	 *    Processed stylesheet.
	 *
	 *    @type array $stylesheet         Stylesheet parts, where arrays are tuples for declaration blocks.
	 *    @type array $validation_results Validation results, array containing arrays with error and sanitized keys.
	 *    @type array $imported_font_urls Imported font stylesheet URLs.
	 * }
	 */
	private function process_stylesheet( $stylesheet, $options = array() ) {
		$parsed      = null;
		$cache_key   = null;
		$cache_group = 'amp-parsed-stylesheet-v13'; // This should be bumped whenever the PHP-CSS-Parser is updated.

		$cache_impacting_options = array_merge(
			wp_array_slice_assoc(
				$options,
				array( 'property_whitelist', 'property_blacklist', 'stylesheet_url', 'allowed_at_rules' )
			),
			wp_array_slice_assoc(
				$this->args,
				array( 'should_locate_sources', 'parsed_cache_variant' )
			),
			array(
				'language' => get_bloginfo( 'language' ), // Used to tree-shake html[lang] selectors.
			)
		);

		$cache_key = md5( $stylesheet . wp_json_encode( $cache_impacting_options ) );

		if ( wp_using_ext_object_cache() ) {
			$parsed = wp_cache_get( $cache_key, $cache_group );
		} else {
			$parsed = get_transient( $cache_key . $cache_group );
		}

		/*
		 * Make sure that the parsed stylesheet was cached with current sanitizations.
		 * The should_sanitize_validation_error method prevents duplicates from being reported.
		 */
		if ( ! empty( $parsed['validation_results'] ) ) {
			foreach ( $parsed['validation_results'] as $validation_result ) {
				$sanitized = $this->should_sanitize_validation_error( $validation_result['error'] );
				if ( $sanitized !== $validation_result['sanitized'] ) {
					$parsed = null; // Change to sanitization of validation error detected, so cache cannot be used.
					break;
				}
			}
		}

		if ( ! $parsed || ! isset( $parsed['stylesheet'] ) || ! is_array( $parsed['stylesheet'] ) ) {
			$parsed = $this->prepare_stylesheet( $stylesheet, $options );

			/*
			 * When an object cache is not available, we cache with an expiration to prevent the options table from
			 * getting filled infinitely. On the other hand, if an external object cache is available then we don't
			 * set an expiration because it should implement LRU cache expulsion policy.
			 */
			if ( wp_using_ext_object_cache() ) {
				wp_cache_set( $cache_key, $parsed, $cache_group );
			} else {
				// The expiration is to ensure transient doesn't stick around forever since no LRU flushing like with external object cache.
				set_transient( $cache_key . $cache_group, $parsed, MONTH_IN_SECONDS );
			}
		}

		return $parsed;
	}

	/**
	 * Parse imported stylesheet.
	 *
	 * @param Import  $item     Import object.
	 * @param CSSList $css_list CSS List.
	 * @param array   $options {
	 *     Options.
	 *
	 *     @type string $stylesheet_url Original URL for stylesheet when originating via link or @import.
	 * }
	 * @return array Validation results.
	 */
	private function parse_import_stylesheet( Import $item, CSSList $css_list, $options ) {
		$results      = array();
		$at_rule_args = $item->atRuleArgs();
		$location     = array_shift( $at_rule_args );
		$media_query  = array_shift( $at_rule_args );

		if ( isset( $options['stylesheet_url'] ) ) {
			$this->real_path_urls( array( $location ), $options['stylesheet_url'] );
		}

		$import_stylesheet_url = $location->getURL()->getString();

		// Prevent importing something that has already been imported, and avoid infinite recursion.
		if ( isset( $this->processed_imported_stylesheet_urls[ $import_stylesheet_url ] ) ) {
			$css_list->remove( $item );
			return array();
		}
		$this->processed_imported_stylesheet_urls[ $import_stylesheet_url ] = true;

		// Prevent importing font stylesheets from allowed font CDNs. These will get added to the document as links instead.
		$https_import_stylesheet_url = preg_replace( '#^(http:)?(?=//)#', 'https:', $import_stylesheet_url );
		if ( $this->allowed_font_src_regex && preg_match( $this->allowed_font_src_regex, $https_import_stylesheet_url ) ) {
			$this->imported_font_urls[] = $https_import_stylesheet_url;
			$css_list->remove( $item );
			_doing_it_wrong(
				'wp_enqueue_style',
				esc_html( sprintf(
					/* translators: %s is URL to font CDN */
					__( 'It is not a best practice to use @import to load font CDN stylesheets. Please use wp_enqueue_style() to enqueue %s as its own separate script.', 'amp' ),
					$import_stylesheet_url
				) ),
				'1.0'
			);
			return array();
		}

		$css_file_path = $this->get_validated_url_file_path( $import_stylesheet_url, array( 'css', 'less', 'scss', 'sass' ) );

		if ( is_wp_error( $css_file_path ) && ( 'disallowed_file_extension' === $css_file_path->get_error_code() || 'external_file_url' === $css_file_path->get_error_code() ) ) {
			$contents = $this->fetch_external_stylesheet( $import_stylesheet_url );
			if ( is_wp_error( $contents ) ) {
				$error     = array(
					'code'    => $contents->get_error_code(),
					'message' => $contents->get_error_message(),
					'type'    => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
				);
				$sanitized = $this->should_sanitize_validation_error( $error );
				if ( $sanitized ) {
					$css_list->remove( $item );
				}
				$results[] = compact( 'error', 'sanitized' );
				return $results;
			} else {
				$stylesheet = $contents;
			}
		} elseif ( is_wp_error( $css_file_path ) ) {
			$error     = array(
				'code'    => $css_file_path->get_error_code(),
				'message' => $css_file_path->get_error_message(),
				'type'    => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
			);
			$sanitized = $this->should_sanitize_validation_error( $error );
			if ( $sanitized ) {
				$css_list->remove( $item );
			}
			$results[] = compact( 'error', 'sanitized' );
			return $results;
		} else {
			$stylesheet = file_get_contents( $css_file_path ); // phpcs:ignore -- It's a local filesystem path not a remote request.
		}

		if ( $media_query ) {
			$stylesheet = sprintf( '@media %s { %s }', $media_query, $stylesheet );
		}

		$options['stylesheet_url'] = $import_stylesheet_url;

		$parsed_stylesheet = $this->parse_stylesheet( $stylesheet, $options );

		$results = array_merge(
			$results,
			$parsed_stylesheet['validation_results']
		);

		/**
		 * CSS Doc.
		 *
		 * @var Document $css_document
		 */
		$css_document = $parsed_stylesheet['css_document'];

		if ( ! empty( $parsed_stylesheet['css_document'] ) ) {
			$css_list->replace( $item, $css_document->getContents() );
		} else {
			$css_list->remove( $item );
		}

		return $results;
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
	 *    @type Document $css_document       CSS Document.
	 *    @type array    $validation_results Validation results, array containing arrays with error and sanitized keys.
	 * }
	 */
	private function parse_stylesheet( $stylesheet_string, $options ) {
		$validation_results = array();
		$css_document       = null;

		$this->imported_font_urls = array();
		try {
			// Remove spaces from data URLs, which cause errors and PHP-CSS-Parser can't handle them.
			$stylesheet_string = $this->remove_spaces_from_data_urls( $stylesheet_string );

			$parser_settings = Sabberworm\CSS\Settings::create();
			$css_parser      = new Sabberworm\CSS\Parser( $stylesheet_string, $parser_settings );
			$css_document    = $css_parser->parse(); // @todo If 'utf-8' is not $css_parser->getCharset() then issue warning?

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

			$validation_results = array_merge(
				$validation_results,
				$this->process_css_list( $css_document, $options )
			);
		} catch ( Exception $exception ) {
			$error = array(
				'code'    => 'css_parse_error',
				'message' => $exception->getMessage(),
				'type'    => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
			);

			/*
			 * This is not a recoverable error, so sanitized here is just used to give user control
			 * over whether to proceed with serving this exception-raising stylesheet in AMP.
			 */
			$sanitized = $this->should_sanitize_validation_error( $error );

			$validation_results[] = compact( 'error', 'sanitized' );
		}
		return array_merge(
			compact( 'validation_results', 'css_document' ),
			array(
				'imported_font_urls' => $this->imported_font_urls,
			)
		);
	}

	/**
	 * Prepare stylesheet.
	 *
	 * @since 1.0
	 *
	 * @param string $stylesheet_string Stylesheet.
	 * @param array  $options           Options. See definition in \AMP_Style_Sanitizer::process_stylesheet().
	 * @return array {
	 *    Prepared stylesheet.
	 *
	 *    @type array $stylesheet         Stylesheet parts, where arrays are tuples for declaration blocks.
	 *    @type array $validation_results Validation results, array containing arrays with error and sanitized keys.
	 *    @type array $imported_font_urls Imported font stylesheet URLs.
	 * }
	 */
	private function prepare_stylesheet( $stylesheet_string, $options = array() ) {
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

		// Strip the dreaded UTF-8 byte order mark (BOM, \uFEFF). This should ideally get handled by PHP-CSS-Parser <https://github.com/sabberworm/PHP-CSS-Parser/issues/150>.
		$stylesheet_string = preg_replace( '/^\xEF\xBB\xBF/', '', $stylesheet_string );

		$stylesheet         = array();
		$parsed_stylesheet  = $this->parse_stylesheet( $stylesheet_string, $options );
		$validation_results = $parsed_stylesheet['validation_results'];
		if ( ! empty( $parsed_stylesheet['css_document'] ) ) {
			$css_document = $parsed_stylesheet['css_document'];

			$output_format = Sabberworm\CSS\OutputFormat::createCompact();
			$output_format->setSemicolonAfterLastRule( false );

			$before_declaration_block          = '/*AMP_WP_BEFORE_DECLARATION_BLOCK*/';
			$between_selectors                 = '/*AMP_WP_BETWEEN_SELECTORS*/';
			$after_declaration_block_selectors = '/*AMP_WP_BEFORE_DECLARATION_SELECTORS*/';
			$after_declaration_block           = '/*AMP_WP_AFTER_DECLARATION*/';
			$before_at_rule                    = '/*AMP_WP_BEFORE_AT_RULE*/';
			$after_at_rule                     = '/*AMP_WP_AFTER_AT_RULE*/';

			$output_format->set( 'BeforeDeclarationBlock', $before_declaration_block );
			$output_format->set( 'SpaceBeforeSelectorSeparator', $between_selectors );
			$output_format->set( 'AfterDeclarationBlockSelectors', $after_declaration_block_selectors );
			$output_format->set( 'AfterDeclarationBlock', $after_declaration_block );
			$output_format->set( 'BeforeAtRuleBlock', $before_at_rule );
			$output_format->set( 'AfterAtRuleBlock', $after_at_rule );

			$stylesheet_string = $css_document->render( $output_format );

			$pattern  = '#';
			$pattern .= preg_quote( $before_at_rule, '#' );
			$pattern .= '|';
			$pattern .= preg_quote( $after_at_rule, '#' );
			$pattern .= '|';
			$pattern .= '(' . preg_quote( $before_declaration_block, '#' ) . ')';
			$pattern .= '(.+?)';
			$pattern .= preg_quote( $after_declaration_block_selectors, '#' );
			$pattern .= '(.+?)';
			$pattern .= preg_quote( $after_declaration_block, '#' );
			$pattern .= '#s';

			$dynamic_selector_pattern = null;
			if ( ! empty( $this->args['dynamic_element_selectors'] ) ) {
				$dynamic_selector_pattern = implode( '|', array_map(
					function( $selector ) {
						return preg_quote( $selector, '#' );
					},
					$this->args['dynamic_element_selectors']
				) );
			}

			$split_stylesheet = preg_split( $pattern, $stylesheet_string, -1, PREG_SPLIT_DELIM_CAPTURE );
			$length           = count( $split_stylesheet );
			for ( $i = 0; $i < $length; $i++ ) {
				if ( $before_declaration_block === $split_stylesheet[ $i ] ) {

					// Skip keyframe-selector, which is can be: from | to | <percentage>.
					if ( preg_match( '/^((from|to)\b|-?\d+(\.\d+)?%)/i', $split_stylesheet[ $i + 1 ] ) ) {
						$stylesheet[] = str_replace( $between_selectors, '', $split_stylesheet[ ++$i ] ) . $split_stylesheet[ ++$i ];
						continue;
					}

					$selectors   = explode( $between_selectors . ',', $split_stylesheet[ ++$i ] );
					$declaration = $split_stylesheet[ ++$i ];

					$selectors_parsed = array();
					foreach ( $selectors as $selector ) {
						$selectors_parsed[ $selector ] = array();

						// Remove :not() and pseudo selectors to eliminate false negatives, such as with `body:not(.title-tagline-hidden) .site-branding-text`.
						$reduced_selector = preg_replace( '/:[a-zA-Z0-9_-]+(\(.+?\))?/', '', $selector );

						// Remove attribute selectors to eliminate false negative, such as with `.social-navigation a[href*="example.com"]:before`.
						$reduced_selector = preg_replace( '/\[\w.*?\]/', '', $reduced_selector );

						// Ignore any selector terms that occur under a dynamic selector.
						if ( $dynamic_selector_pattern ) {
							$reduced_selector = preg_replace( '#((?:' . $dynamic_selector_pattern . ')(?:\.[a-z0-9_-]+)*)[^a-z0-9_-].*#si', '$1', $reduced_selector . ' ' );
						}

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

					$stylesheet[] = array(
						$selectors_parsed,
						$declaration,
					);
				} else {
					$stylesheet[] = $split_stylesheet[ $i ];
				}
			}
		}

		$this->parse_css_duration += ( microtime( true ) - $start_time );

		return array_merge(
			compact( 'stylesheet', 'validation_results' ),
			array(
				'imported_font_urls' => $parsed_stylesheet['imported_font_urls'],
			)
		);
	}

	/**
	 * Previous return values from calls to should_sanitize_validation_error().
	 *
	 * This is used to prevent duplicates from being reported when the sanitization status
	 * changes for a validation error in a previously-cached stylesheet.
	 *
	 * @see AMP_Style_Sanitizer::should_sanitize_validation_error()
	 * @var array
	 */
	protected $previous_should_sanitize_validation_error_results = array();

	/**
	 * Check whether or not sanitization should occur in response to validation error.
	 *
	 * Supply sources to the error and the current node to data.
	 *
	 * @since 1.0
	 *
	 * @param array $validation_error Validation error.
	 * @param array $data Data including the node.
	 * @return bool Whether to sanitize.
	 */
	public function should_sanitize_validation_error( $validation_error, $data = array() ) {
		if ( ! isset( $data['node'] ) ) {
			$data['node'] = $this->current_node;
		}
		if ( ! isset( $validation_error['sources'] ) ) {
			$validation_error['sources'] = $this->current_sources;
		}

		/*
		 * This is used to prevent duplicates from being reported when the sanitization status
		 * changes for a validation error in a previously-cached stylesheet.
		 */
		$args = compact( 'validation_error', 'data' );
		foreach ( $this->previous_should_sanitize_validation_error_results as $result ) {
			if ( $result['args'] === $args ) {
				return $result['sanitized'];
			}
		}

		$sanitized = parent::should_sanitize_validation_error( $validation_error, $data );

		$this->previous_should_sanitize_validation_error_results[] = compact( 'args', 'sanitized' );
		return $sanitized;
	}

	/**
	 * Remove spaces from data URLs which PHP-CSS-Parser doesn't handle.
	 *
	 * @since 1.0
	 *
	 * @param string $css CSS.
	 * @return string CSS with spaces removed from data URLs.
	 */
	private function remove_spaces_from_data_urls( $css ) {
		return preg_replace_callback(
			'/\burl\([^}]*?\)/',
			function( $matches ) {
				return preg_replace( '/\s+/', '', $matches[0] );
			},
			$css
		);
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
		$results = array();

		foreach ( $css_list->getContents() as $css_item ) {
			$sanitized = false;
			if ( $css_item instanceof DeclarationBlock && empty( $options['validate_keyframes'] ) ) {
				$results = array_merge(
					$results,
					$this->process_css_declaration_block( $css_item, $css_list, $options )
				);
			} elseif ( $css_item instanceof AtRuleBlockList ) {
				if ( ! in_array( $css_item->atRuleName(), $options['allowed_at_rules'], true ) ) {
					$error     = array(
						'code'    => self::ILLEGAL_AT_RULE_ERROR_CODE,
						'at_rule' => $css_item->atRuleName(),
						'type'    => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
					);
					$sanitized = $this->should_sanitize_validation_error( $error );
					$results[] = compact( 'error', 'sanitized' );
				}
				if ( ! $sanitized ) {
					$results = array_merge(
						$results,
						$this->process_css_list( $css_item, $options )
					);
				}
			} elseif ( $css_item instanceof Import ) {
				$results = array_merge(
					$results,
					$this->parse_import_stylesheet( $css_item, $css_list, $options )
				);
			} elseif ( $css_item instanceof AtRuleSet ) {
				if ( ! in_array( $css_item->atRuleName(), $options['allowed_at_rules'], true ) ) {
					$error     = array(
						'code'    => self::ILLEGAL_AT_RULE_ERROR_CODE,
						'at_rule' => $css_item->atRuleName(),
						'type'    => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
					);
					$sanitized = $this->should_sanitize_validation_error( $error );
					$results[] = compact( 'error', 'sanitized' );
				}

				if ( ! $sanitized ) {
					$results = array_merge(
						$results,
						$this->process_css_declaration_block( $css_item, $css_list, $options )
					);
				}
			} elseif ( $css_item instanceof KeyFrame ) {
				if ( ! in_array( 'keyframes', $options['allowed_at_rules'], true ) ) {
					$error     = array(
						'code'    => self::ILLEGAL_AT_RULE_ERROR_CODE,
						'at_rule' => $css_item->atRuleName(),
						'type'    => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
					);
					$sanitized = $this->should_sanitize_validation_error( $error );
					$results[] = compact( 'error', 'sanitized' );
				}

				if ( ! $sanitized ) {
					$results = array_merge(
						$results,
						$this->process_css_keyframes( $css_item, $options )
					);
				}
			} elseif ( $css_item instanceof AtRule ) {
				if ( 'charset' === $css_item->atRuleName() ) {
					/*
					 * The @charset at-rule is not allowed in style elements, so it is not allowed in AMP.
					 * If the @charset is defined, then it really should have already been acknowledged
					 * by PHP-CSS-Parser when the CSS was parsed in the first place, so at this point
					 * it is irrelevant and can be removed.
					 */
					$sanitized = true;
				} else {
					$error     = array(
						'code'    => self::ILLEGAL_AT_RULE_ERROR_CODE,
						'at_rule' => $css_item->atRuleName(),
						'type'    => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
					);
					$sanitized = $this->should_sanitize_validation_error( $error );
					$results[] = compact( 'error', 'sanitized' );
				}
			} else {
				$error     = array(
					'code' => 'unrecognized_css',
					'item' => get_class( $css_item ),
					'type' => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
				);
				$sanitized = $this->should_sanitize_validation_error( $error );
				$results[] = compact( 'error', 'sanitized' );
			}

			if ( $sanitized ) {
				$css_list->remove( $css_item );
			}
		}
		return $results;
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
			// URLs cannot have spaces in them, so strip them (especially when spaces get erroneously injected in data: URLs).
			$url_string = $url->getURL()->getString();

			// For data: URLs, all that is needed is to remove spaces so set and continue.
			if ( 'data:' === substr( $url_string, 0, 5 ) ) {
				continue;
			}

			// If the URL is already absolute, continue since there there is nothing left to do.
			$parsed_url = wp_parse_url( $url_string );
			if ( ! empty( $parsed_url['host'] ) || empty( $parsed_url['path'] ) || '/' === substr( $parsed_url['path'], 0, 1 ) ) {
				continue;
			}

			$relative_url = preg_replace( '#^\./#', '', $url->getURL()->getString() );

			// Resolve any relative parent directory paths.
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
	 * @return array Validation results.
	 */
	private function process_css_declaration_block( RuleSet $ruleset, CSSList $css_list, $options ) {
		$results = array();

		if ( $ruleset instanceof DeclarationBlock ) {
			$this->ampify_ruleset_selectors( $ruleset );
			if ( 0 === count( $ruleset->getSelectors() ) ) {
				$css_list->remove( $ruleset );
				return $results;
			}
		}

		// Remove disallowed properties.
		if ( ! empty( $options['property_whitelist'] ) ) {
			$properties = $ruleset->getRules();
			foreach ( $properties as $property ) {
				$vendorless_property_name = preg_replace( '/^-\w+-/', '', $property->getRule() );
				if ( ! in_array( $vendorless_property_name, $options['property_whitelist'], true ) ) {
					$error     = array(
						'code'           => 'illegal_css_property',
						'property_name'  => $property->getRule(),
						'property_value' => $property->getValue(),
						'type'           => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
					);
					$sanitized = $this->should_sanitize_validation_error( $error );
					if ( $sanitized ) {
						$ruleset->removeRule( $property->getRule() );
					}
					$results[] = compact( 'error', 'sanitized' );
				}
			}
		} else {
			foreach ( $options['property_blacklist'] as $illegal_property_name ) {
				$properties = $ruleset->getRules( $illegal_property_name );
				foreach ( $properties as $property ) {
					$error     = array(
						'code'           => 'illegal_css_property',
						'property_name'  => $property->getRule(),
						'property_value' => (string) $property->getValue(),
						'type'           => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
					);
					$sanitized = $this->should_sanitize_validation_error( $error );
					if ( $sanitized ) {
						$ruleset->removeRule( $property->getRule() );
					}
					$results[] = compact( 'error', 'sanitized' );
				}
			}
		}

		if ( $ruleset instanceof AtRuleSet && 'font-face' === $ruleset->atRuleName() ) {
			$this->process_font_face_at_rule( $ruleset );
		}

		$results = array_merge(
			$results,
			$this->transform_important_qualifiers( $ruleset, $css_list )
		);

		// Remove the ruleset if it is now empty.
		if ( 0 === count( $ruleset->getRules() ) ) {
			$css_list->remove( $ruleset );
		}
		// @todo Delete rules with selectors for -amphtml- class and i-amphtml- tags.
		return $results;
	}

	/**
	 * Process @font-face by making src URLs non-relative and converting data: URLs into (assumed) file URLs.
	 *
	 * @since 1.0
	 *
	 * @param AtRuleSet $ruleset Ruleset for @font-face.
	 */
	private function process_font_face_at_rule( AtRuleSet $ruleset ) {
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

				// Ensure font file exists.
				$path = $this->get_validated_url_file_path( $guessed_url, array( 'woff', 'woff2', 'ttf', 'otf', 'svg' ) );
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
	 * @return array Validation results.
	 */
	private function process_css_keyframes( KeyFrame $css_list, $options ) {
		$results = array();
		if ( ! empty( $options['property_whitelist'] ) ) {
			foreach ( $css_list->getContents() as $rules ) {
				if ( ! ( $rules instanceof DeclarationBlock ) ) {
					$error     = array(
						'code' => 'unrecognized_css',
						'item' => get_class( $rules ),
						'type' => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
					);
					$sanitized = $this->should_sanitize_validation_error( $error );
					if ( $sanitized ) {
						$css_list->remove( $rules );
					}
					$results[] = compact( 'error', 'sanitized' );
					continue;
				}

				$results = array_merge(
					$results,
					$this->transform_important_qualifiers( $rules, $css_list )
				);

				$properties = $rules->getRules();
				foreach ( $properties as $property ) {
					$vendorless_property_name = preg_replace( '/^-\w+-/', '', $property->getRule() );
					if ( ! in_array( $vendorless_property_name, $options['property_whitelist'], true ) ) {
						$error     = array(
							'code'           => 'illegal_css_property',
							'property_name'  => $property->getRule(),
							'property_value' => (string) $property->getValue(),
							'type'           => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
						);
						$sanitized = $this->should_sanitize_validation_error( $error );
						if ( $sanitized ) {
							$rules->removeRule( $property->getRule() );
						}
						$results[] = compact( 'error', 'sanitized' );
					}
				}
			}
		}
		return $results;
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
	 * @return array Validation results.
	 */
	private function transform_important_qualifiers( RuleSet $ruleset, CSSList $css_list ) {
		$results = array();

		// An !important only makes sense for rulesets that have selectors.
		$allow_transformation = (
			$ruleset instanceof DeclarationBlock
			&&
			! ( $css_list instanceof KeyFrame )
		);

		$properties = $ruleset->getRules();
		$importants = array();
		foreach ( $properties as $property ) {
			if ( $property->getIsImportant() ) {
				if ( $allow_transformation ) {
					$importants[] = $property;
					$property->setIsImportant( false );
					$ruleset->removeRule( $property->getRule() );
				} else {
					$error     = array(
						'code' => 'illegal_css_important',
						'type' => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
					);
					$sanitized = $this->should_sanitize_validation_error( $error );
					if ( $sanitized ) {
						$property->setIsImportant( false );
					}
					$results[] = compact( 'error', 'sanitized' );
				}
			}
		}
		if ( ! $allow_transformation || empty( $importants ) ) {
			return $results;
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
				// Calculate the specificity multiplier for the placeholder.
				$specificity_multiplier = AMP_Style_Sanitizer::INLINE_SPECIFICITY_MULTIPLIER + 1 + floor( $old_selector->getSpecificity() / 100 );
				if ( $old_selector->getSpecificity() % 100 > 0 ) {
					$specificity_multiplier++;
				}
				if ( $old_selector->getSpecificity() % 10 > 0 ) {
					$specificity_multiplier++;
				}
				$selector_mod = str_repeat( ':not(#_)', $specificity_multiplier ); // Here "_" is just a short single-char ID.

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

		$i = array_search( $ruleset, $css_list->getContents(), true );
		if ( false !== $i ) {
			$css_list->splice( $i + 1, 0, array( $important_ruleset ) );
		} else {
			$css_list->append( $important_ruleset );
		}

		return $results;
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
		$root  = ':root' . str_repeat( ':not(#_)', self::INLINE_SPECIFICITY_MULTIPLIER );
		$rule  = sprintf( '%s .%s { %s }', $root, $class, $style_attribute->nodeValue );

		$this->set_current_node( $element ); // And sources when needing to be located.

		$processed = $this->process_stylesheet( $rule, array(
			'allowed_at_rules'   => array(),
			'property_whitelist' => $this->style_custom_cdata_spec['css_spec']['declaration'],
		) );

		$element->removeAttribute( 'style' );

		if ( $processed['stylesheet'] ) {
			$this->pending_stylesheets[] = array(
				'stylesheet' => $processed['stylesheet'],
				'node'       => $element,
				'sources'    => $this->current_sources,
			);

			if ( $element->hasAttribute( 'class' ) ) {
				$element->setAttribute( 'class', $element->getAttribute( 'class' ) . ' ' . $class );
			} else {
				$element->setAttribute( 'class', $class );
			}
		}

		$this->set_current_node( null );
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
				'source_map_comment'  => "\n\n/*# sourceURL=amp-custom.css */",
				'total_size'          => 0,
				'cdata_spec'          => $this->style_custom_cdata_spec,
				'pending_stylesheets' => array(),
				'final_stylesheets'   => array(),
				'remove_unused_rules' => $this->args['remove_unused_rules'],
			),
			'keyframes' => array(
				'source_map_comment'  => "\n\n/*# sourceURL=amp-keyframes.css */",
				'total_size'          => 0,
				'cdata_spec'          => $this->style_keyframes_cdata_spec,
				'pending_stylesheets' => array(),
				'final_stylesheets'   => array(),
				'remove_unused_rules' => 'never', // Not relevant.
			),
		);

		$imported_font_urls = array();

		/*
		 * On Native AMP themes when there are new/rejected validation errors present, a parsed stylesheet may include
		 * @import rules. These must be moved to the beginning to be honored.
		 */
		$imports = array();

		// Divide pending stylesheet between custom and keyframes, and calculate size of each.
		while ( ! empty( $this->pending_stylesheets ) ) {
			$pending_stylesheet = array_shift( $this->pending_stylesheets );

			$set_name = ! empty( $pending_stylesheet['keyframes'] ) ? 'keyframes' : 'custom';
			$size     = 0;
			foreach ( $pending_stylesheet['stylesheet'] as $i => $part ) {
				if ( is_string( $part ) ) {
					$size += strlen( $part );
					if ( '@import' === substr( $part, 0, 7 ) ) {
						$imports[] = $part;
						unset( $pending_stylesheet['stylesheet'][ $i ] );
					}
				} elseif ( is_array( $part ) ) {
					$size += strlen( implode( ',', array_keys( $part[0] ) ) ); // Selectors.
					$size += strlen( $part[1] ); // Declaration block.
				}
			}
			$stylesheet_sets[ $set_name ]['total_size']           += $size;
			$stylesheet_sets[ $set_name ]['imports']               = $imports;
			$stylesheet_sets[ $set_name ]['pending_stylesheets'][] = $pending_stylesheet;

			if ( ! empty( $pending_stylesheet['imported_font_urls'] ) ) {
				$imported_font_urls = array_merge( $imported_font_urls, $pending_stylesheet['imported_font_urls'] );
			}
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
				$this->head->appendChild( $this->amp_custom_style_element );
			}

			$css  = implode( '', $stylesheet_sets['custom']['imports'] ); // For native dirty AMP.
			$css .= implode( '', $stylesheet_sets['custom']['final_stylesheets'] );
			$css .= $stylesheet_sets['custom']['source_map_comment'];

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

			$included_size          = 0;
			$included_original_size = 0;
			$excluded_size          = 0;
			$excluded_original_size = 0;
			$included_sources       = array();
			foreach ( $stylesheet_sets['custom']['pending_stylesheets'] as $i => $pending_stylesheet ) {
				if ( ! ( $pending_stylesheet['node'] instanceof DOMElement ) ) {
					continue;
				}
				$message = sprintf( '% 6d B', $pending_stylesheet['size'] );
				if ( $pending_stylesheet['size'] && $pending_stylesheet['size'] !== $pending_stylesheet['original_size'] ) {
					$message .= sprintf( ' (%d%%)', $pending_stylesheet['size'] / $pending_stylesheet['original_size'] * 100 );
				}
				$message .= ': ';
				$message .= $pending_stylesheet['node']->nodeName;
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

				if ( ! empty( $pending_stylesheet['included'] ) ) {
					$included_sources[]      = $message;
					$included_size          += $pending_stylesheet['size'];
					$included_original_size += $pending_stylesheet['original_size'];
				} else {
					$excluded_sources[]      = $message;
					$excluded_size          += $pending_stylesheet['size'];
					$excluded_original_size += $pending_stylesheet['original_size'];
				}
			}
			$comment = '';
			if ( ! empty( $included_sources ) && $included_original_size > 0 ) {
				$comment .= esc_html__( 'The style[amp-custom] element is populated with:', 'amp' ) . "\n" . implode( "\n", $included_sources ) . "\n";
				$comment .= sprintf(
					/* translators: %1$d is number of included bytes, %2$d is percentage of total CSS actually included after tree shaking, %3$d is total included size */
					esc_html__( 'Total included size: %1$s bytes (%2$d%% of %3$s total after tree shaking)', 'amp' ),
					number_format_i18n( $included_size ),
					$included_size / $included_original_size * 100,
					number_format_i18n( $included_original_size )
				) . "\n";
			}
			if ( ! empty( $excluded_sources ) && $excluded_original_size > 0 ) {
				if ( $comment ) {
					$comment .= "\n";
				}
				$comment .= esc_html__( 'The following stylesheets are too large to be included in style[amp-custom]:', 'amp' ) . "\n" . implode( "\n", $excluded_sources ) . "\n";

				$comment .= sprintf(
					/* translators: %1$d is number of excluded bytes, %2$d is percentage of total CSS actually excluded even after tree shaking, %3$d is total excluded size */
					esc_html__( 'Total excluded size: %1$s bytes (%2$d%% of %3$s total after tree shaking)', 'amp' ),
					number_format_i18n( $excluded_size ),
					$excluded_size / $excluded_original_size * 100,
					number_format_i18n( $excluded_original_size )
				) . "\n";

				$total_size          = $included_size + $excluded_size;
				$total_original_size = $included_original_size + $excluded_original_size;
				if ( $total_size !== $total_original_size ) {
					$comment .= "\n";
					$comment .= sprintf(
						/* translators: %1$d is total combined bytes, %2$d is percentage of CSS after tree shaking, %3$d is total before tree shaking */
						esc_html__( 'Total combined size: %1$s bytes (%2$d%% of %3$s total after tree shaking)', 'amp' ),
						number_format_i18n( $total_size ),
						( $total_size / $total_original_size ) * 100,
						number_format_i18n( $total_original_size )
					) . "\n";
				}
			}

			if ( $comment ) {
				$this->amp_custom_style_element->parentNode->insertBefore(
					$this->dom->createComment( "\n$comment" ),
					$this->amp_custom_style_element
				);
			}
		}

		/*
		 * Add font stylesheets from CDNs which were extracted from @import rules.
		 * We can't add crossorigin=anonymous to these since such a CORS request would not be made in the non-AMP version,
		 * and so if the service worker cached the opaque response on the non-AMP version then it wouldn't be usable in
		 * the AMP version if it was requested with CORS.
		 */
		foreach ( array_unique( $imported_font_urls ) as $imported_font_url ) {
			$link = $this->dom->createElement( 'link' );
			$link->setAttribute( 'rel', 'stylesheet' );
			$link->setAttribute( 'href', $imported_font_url );
			$this->head->appendChild( $link );
		}

		// Add style[amp-keyframes] to document.
		if ( ! empty( $stylesheet_sets['keyframes']['final_stylesheets'] ) ) {
			$body = $this->dom->getElementsByTagName( 'body' )->item( 0 );
			if ( ! $body ) {
				$this->should_sanitize_validation_error( array(
					'code' => 'missing_body_element',
					'type' => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
				) );
			} else {
				$css  = implode( '', $stylesheet_sets['keyframes']['final_stylesheets'] );
				$css .= $stylesheet_sets['keyframes']['source_map_comment'];

				$style_element = $this->dom->createElement( 'style' );
				$style_element->setAttribute( 'amp-keyframes', '' );
				$style_element->appendChild( $this->dom->createTextNode( $css ) );
				$body->appendChild( $style_element );
			}
		}
	}

	/**
	 * Convert CSS selectors and remove obsolete selector hacks for IE.
	 *
	 * @param DeclarationBlock $ruleset Ruleset.
	 */
	private function ampify_ruleset_selectors( $ruleset ) {
		$selectors = array();
		$changes   = 0;
		$language  = get_bloginfo( 'language' );
		foreach ( $ruleset->getSelectors() as $old_selector ) {
			$selector = $old_selector->getSelector();

			// Automatically tree-shake IE6/IE7 hacks for selectors with `* html` and `*+html`.
			if ( preg_match( '/^\*\s*\+?\s*html/', $selector ) ) {
				$changes++;
				continue;
			}

			// Automatically remove selectors that are for another language (and thus are irrelevant). This is safe because amp-bind'ed [lang] is not allowed.
			$is_other_language = (
				preg_match( '/^html\[lang(?P<starts_with>\^?)=([\'"]?)(?P<lang>.+?)\2\]/', $selector, $matches )
				&&
				(
					empty( $matches['starts_with'] )
					?
					$language !== $matches['lang']
					:
					substr( $language, 0, strlen( $matches['lang'] ) ) !== $matches['lang']
				)
			);
			if ( $is_other_language ) {
				$changes++;
				continue;
			}

			// An element (type) either starts a selector or is preceded by combinator, comma, opening paren, or closing brace.
			$before_type_selector_pattern = '(?<=^|\(|\s|>|\+|~|,|})';
			$after_type_selector_pattern  = '(?=$|[^a-zA-Z0-9_-])';

			$edited_selectors = array( $selector );
			foreach ( $this->selector_mappings as $html_selector => $amp_selectors ) { // Note: The $selector_mappings array contains ~6 items.
				$html_pattern = '/' . $before_type_selector_pattern . preg_quote( $html_selector, '/' ) . $after_type_selector_pattern . '/i';
				foreach ( $edited_selectors as &$edited_selector ) { // Note: The $edited_selectors array contains only item in the normal case.
					$original_selector = $edited_selector;
					$amp_selector      = array_shift( $amp_selectors );
					$amp_tag_pattern   = '/' . $before_type_selector_pattern . preg_quote( $amp_selector, '/' ) . $after_type_selector_pattern . '/i';
					preg_match( $amp_tag_pattern, $edited_selector, $matches );
					if ( ! empty( $matches ) && $amp_selector === $matches[0] ) {
						continue;
					}
					$edited_selector = preg_replace( $html_pattern, $amp_selector, $edited_selector, -1, $count );
					if ( ! $count ) {
						continue;
					}
					$changes += $count;
					while ( ! empty( $amp_selectors ) ) { // Note: This array contains only a couple items.
						$amp_selector       = array_shift( $amp_selectors );
						$edited_selectors[] = preg_replace( $html_pattern, $amp_selector, $original_selector, -1, $count );
					}
				}
			}
			$selectors = array_merge( $selectors, $edited_selectors );
		}

		if ( $changes > 0 ) {
			$ruleset->setSelectors( $selectors );
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
		$max_bytes         = $stylesheet_set['cdata_spec']['max_bytes'] - strlen( $stylesheet_set['source_map_comment'] );
		$is_too_much_css   = $stylesheet_set['total_size'] > $max_bytes;
		$should_tree_shake = (
			'always' === $stylesheet_set['remove_unused_rules'] || (
				$is_too_much_css
				&&
				'sometimes' === $stylesheet_set['remove_unused_rules']
			)
		);

		if ( $is_too_much_css && $should_tree_shake && empty( $this->args['accept_tree_shaking'] ) ) {
			$should_tree_shake = $this->should_sanitize_validation_error( array(
				'code' => self::TREE_SHAKING_ERROR_CODE,
				'type' => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
			) );
		}

		$stylesheet_set['processed_nodes'] = array();

		$final_size = 0;
		$dom        = $this->dom;
		foreach ( $stylesheet_set['pending_stylesheets'] as &$pending_stylesheet ) {
			$stylesheet_parts = array();
			$original_size    = 0;
			foreach ( $pending_stylesheet['stylesheet'] as $stylesheet_part ) {
				if ( is_string( $stylesheet_part ) ) {
					$stylesheet_parts[] = $stylesheet_part;
					$original_size     += strlen( $stylesheet_part );
					continue;
				}

				list( $selectors_parsed, $declaration_block ) = $stylesheet_part;
				if ( $should_tree_shake ) {
					$selectors = array();
					foreach ( $selectors_parsed as $selector => $parsed_selector ) {
						$should_include = (
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
				$stylesheet_part = implode( ',', $selectors ) . $declaration_block;
				$original_size  += strlen( $stylesheet_part );
				if ( ! empty( $selectors ) ) {
					$stylesheet_parts[] = $stylesheet_part;
				}
			}

			// Strip empty at-rules after tree shaking.
			$stylesheet_part_count = count( $stylesheet_parts );
			for ( $i = 0; $i < $stylesheet_part_count; $i++ ) {
				$stylesheet_part = $stylesheet_parts[ $i ];
				if ( '@' !== substr( $stylesheet_part, 0, 1 ) ) {
					continue;
				}

				// Delete empty at-rules.
				if ( '{}' === substr( $stylesheet_part, -2 ) ) {
					$stylesheet_part_count--;
					array_splice( $stylesheet_parts, $i, 1 );
					$i--;
					continue;
				}

				// Delete at-rules that were emptied due to tree-shaking.
				if ( '{' === substr( $stylesheet_part, -1 ) ) {
					$open_braces = 1;
					for ( $j = $i + 1; $j < $stylesheet_part_count; $j++ ) {
						$stylesheet_part = $stylesheet_parts[ $j ];
						$is_at_rule      = '@' === substr( $stylesheet_part, 0, 1 );
						if ( empty( $stylesheet_part ) ) {
							continue; // There was a shaken rule.
						} elseif ( $is_at_rule && '{}' === substr( $stylesheet_part, -2 ) ) {
							continue; // The rule opens is empty from the start.
						} elseif ( $is_at_rule && '{' === substr( $stylesheet_part, -1 ) ) {
							$open_braces++;
						} elseif ( '}' === $stylesheet_part ) {
							$open_braces--;
						} else {
							break;
						}

						// Splice out the parts that are empty.
						if ( 0 === $open_braces ) {
							array_splice( $stylesheet_parts, $i, $j - $i + 1 );
							$stylesheet_part_count = count( $stylesheet_parts );
							$i--;
							continue 2;
						}
					}
				}
			}
			$pending_stylesheet['original_size'] = $original_size;

			$stylesheet = implode( '', $stylesheet_parts );
			unset( $stylesheet_parts );
			$sheet_size                 = strlen( $stylesheet );
			$pending_stylesheet['size'] = $sheet_size;

			// Skip considering stylesheet if an identical one has already been processed.
			$hash = md5( $stylesheet );
			if ( isset( $stylesheet_set['final_stylesheets'][ $hash ] ) ) {
				$pending_stylesheet['included'] = true;
				continue;
			}

			// Report validation error if size is now too big.
			if ( $final_size + $sheet_size > $max_bytes ) {
				$validation_error = array(
					'code' => 'excessive_css',
					'type' => AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
				);
				if ( isset( $pending_stylesheet['sources'] ) ) {
					$validation_error['sources'] = $pending_stylesheet['sources'];
				}

				if ( $this->should_sanitize_validation_error( $validation_error, wp_array_slice_assoc( $pending_stylesheet, array( 'node' ) ) ) ) {
					$pending_stylesheet['included'] = false;
					continue; // Skip to the next stylesheet.
				}
			}

			$final_size += $sheet_size;

			$pending_stylesheet['included']               = true;
			$stylesheet_set['final_stylesheets'][ $hash ] = $stylesheet;
		}

		return $stylesheet_set;
	}
}
