<?php
/**
 * Class AMP_Links_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\Dom\Document;
use AmpProject\Attribute;
use AmpProject\Tag;

/**
 * Class AMP_Link_Sanitizer.
 *
 * Adapts links for AMP-to-AMP navigation:
 *  - In paired AMP (Transitional and Reader modes), internal links get '?amp' added to them.
 *  - Internal links on AMP pages get rel=amphtml added to them.
 *  - Forms with internal actions get a hidden 'amp' input added to them.
 *  - AMP pages get meta[amp-to-amp-navigation] added to them.
 *  - Any elements in the admin bar are excluded.
 *
 * Adapted from https://gist.github.com/westonruter/f9ee9ea717d52471bae092879e3d52b0
 *
 * @link https://github.com/ampproject/amphtml/issues/12496
 * @since 1.4.0
 * @internal
 */
class AMP_Link_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Default meta tag content.
	 *
	 * @var string
	 */
	const DEFAULT_META_CONTENT = 'AMP-Redirect-To; AMP.navigateTo';

	/**
	 * Placeholder for default arguments, to be set in child classes.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [ // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
		'paired'        => false, // Only set to true when in a paired mode (will be false when amp_is_canonical()). Controls whether query var is added.
		'meta_content'  => self::DEFAULT_META_CONTENT,
		'excluded_urls' => [], // URLs in this won't have AMP-to-AMP links in a paired mode.
	];

	/**
	 * Home host.
	 *
	 * @var string
	 */
	protected $home_host;

	/**
	 * Content path.
	 *
	 * @var string
	 */
	protected $content_path;

	/**
	 * Admin path.
	 *
	 * @var string
	 */
	protected $admin_path;

	/**
	 * Sanitizer constructor.
	 *
	 * @param Document $dom  Document.
	 * @param array    $args Args.
	 */
	public function __construct( $dom, array $args = [] ) {
		if ( ! isset( $args['meta_content'] ) ) {
			$args['meta_content'] = self::DEFAULT_META_CONTENT;
		}

		parent::__construct( $dom, $args );

		$this->home_host    = wp_parse_url( home_url(), PHP_URL_HOST );
		$this->content_path = wp_parse_url( content_url( '/' ), PHP_URL_PATH );
		$this->admin_path   = wp_parse_url( admin_url(), PHP_URL_PATH );
	}

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		if ( ! empty( $this->args['meta_content'] ) ) {
			$this->add_meta_tag( $this->args['meta_content'] );
		}

		$this->process_links();
	}

	/**
	 * Add the amp-to-amp-navigation meta tag.
	 *
	 * @param string $content The content for the meta tag, for example 'AMP-Redirect-To; AMP.navigateTo'.
	 * @return DOMElement|null The added meta element if successful.
	 */
	public function add_meta_tag( $content = self::DEFAULT_META_CONTENT ) {
		if ( ! $content ) {
			return null;
		}
		$meta = $this->dom->createElement( 'meta' );
		$meta->setAttribute( 'name', 'amp-to-amp-navigation' );
		$meta->setAttribute( 'content', $content );
		$this->dom->head->appendChild( $meta );
		return $meta;
	}

	/**
	 * Process links by adding adding AMP query var to links in paired mode and adding rel=amphtml.
	 */
	public function process_links() {
		// Remove admin bar from DOM to prevent mutating it.
		$admin_bar_container   = $this->dom->getElementById( 'wpadminbar' );
		$admin_bar_placeholder = null;
		if ( $admin_bar_container ) {
			$admin_bar_placeholder = $this->dom->createComment( 'wpadminbar' );
			$admin_bar_container->parentNode->replaceChild( $admin_bar_placeholder, $admin_bar_container );
		}

		$link_query = $this->dom->xpath->query( '//*[ local-name() = "a" or local-name() = "area" ][ @href ]' );
		foreach ( $link_query as $link ) {
			$this->process_element( $link, Attribute::HREF );
		}

		$form_query = $this->dom->xpath->query(
			'
			//form[
				@action
				and
				(
					not( @method )
					or
					translate( @method, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "get"
				)
			]
			'
		);
		foreach ( $form_query as $form ) {
			$this->process_element( $form, Attribute::ACTION );
		}

		// Replace the admin bar after mutations are done.
		if ( $admin_bar_container && $admin_bar_placeholder ) {
			$admin_bar_placeholder->parentNode->replaceChild( $admin_bar_container, $admin_bar_placeholder );
		}
	}

	/**
	 * Check if element is descendant of a template element.
	 *
	 * @param DOMElement $node Node.
	 * @return bool Descendant of template.
	 */
	private function is_descendant_of_template_element( DOMElement $node ) {
		while ( $node instanceof DOMElement ) {
			$parent = $node->parentNode;
			if ( $parent instanceof DOMElement && Tag::TEMPLATE === $parent->tagName ) {
				return true;
			}
			$node = $parent;
		}
		return false;
	}

	/**
	 * Process element.
	 *
	 * @param DOMElement $element        Element to process.
	 * @param string     $attribute_name Attribute name that contains the URL.
	 */
	private function process_element( DOMElement $element, $attribute_name ) {
		$url = $element->getAttribute( $attribute_name );

		// Skip page anchor links or non-frontend links.
		if ( empty( $url ) || '#' === substr( $url, 0, 1 ) || ! $this->is_frontend_url( $url ) ) {
			return;
		}

		// Skip links with template variables.
		if ( preg_match( '/{{[^}]+?}}/', $url ) && $this->is_descendant_of_template_element( $element ) ) {
			return;
		}

		// Gather the rel values that were attributed to the element.
		// Note that links and forms may both have this attribute.
		// See <https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/rel>.
		if ( $element->hasAttribute( Attribute::REL ) ) {
			$rel = array_filter( preg_split( '/\s+/', trim( $element->getAttribute( Attribute::REL ) ) ) );
		} else {
			$rel = [];
		}

		$excluded = (
			in_array( Attribute::REL_NOAMPHTML, $rel, true )
			||
			in_array( strtok( $url, '#' ), $this->args['excluded_urls'], true )
		);

		/**
		 * Filters whether AMP-to-AMP is excluded for an element.
		 *
		 * The element may be either a link (`a` or `area`) or a `form`.
		 *
		 * @param bool       $excluded Excluded. Default value is whether element already has a `noamphtml` link relation or the URL is among `excluded_urls`.
		 * @param string     $url      URL considered for exclusion.
		 * @param string[]   $rel      Link relations.
		 * @param DOMElement $element  The element considered for excluding from AMP-to-AMP linking. May be instance of `a`, `area`, or `form`.
		 */
		$excluded = (bool) apply_filters( 'amp_to_amp_linking_element_excluded', $excluded, $url, $rel, $element );

		$query_vars = [];

		// Add rel=amphtml.
		if ( ! $excluded ) {
			$rel[] = Attribute::REL_AMPHTML;
			$rel   = array_diff(
				$rel,
				[ Attribute::REL_NOAMPHTML ]
			);
			$element->setAttribute( Attribute::REL, implode( ' ', $rel ) );
		}

		/**
		 * Filters the query vars that are added to the link/form which is considered for AMP-to-AMP linking.
		 *
		 * @internal
		 *
		 * @param string[]   $query_vars Query vars.
		 * @param bool       $excluded   Whether the element was excluded.
		 * @param string     $url        URL considered for exclusion.
		 * @param string[]   $rel        Link relations.
		 * @param DOMElement $element    Element.
		 */
		$query_vars = apply_filters( 'amp_to_amp_linking_element_query_vars', $query_vars, $excluded, $url, $element, $rel );

		if ( ! empty( $query_vars ) ) {
			$url = add_query_arg( $query_vars, $url );
		}

		// Only add the AMP query var when requested (in Transitional or Reader mode).
		if ( ! $excluded && ! empty( $this->args['paired'] ) ) {
			$url = amp_add_paired_endpoint( $url );
		}

		$element->setAttribute( $attribute_name, $url );

		// Given that form action query vars get overridden by the inputs, they need to be extracted and added as inputs.
		if ( Tag::FORM === $element->nodeName ) {
			$query = wp_parse_url( $url, PHP_URL_QUERY );
			if ( $query ) {
				$parsed_query_vars = [];
				wp_parse_str( $query, $parsed_query_vars );
				$query_vars = array_merge( $query_vars, $parsed_query_vars );
			}

			foreach ( $query_vars as $name => $value ) {
				$input = $this->dom->createElement( Tag::INPUT );
				$input->setAttribute( Attribute::NAME, $name );
				$input->setAttribute( Attribute::VALUE, $value );
				$input->setAttribute( Attribute::TYPE, 'hidden' );
				$element->appendChild( $input );
			}
		}
	}

	/**
	 * Determine whether a URL is for the frontend.
	 *
	 * @param string $url URL.
	 * @return bool Whether it is a frontend URL.
	 */
	public function is_frontend_url( $url ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! empty( $parsed_url['scheme'] ) && ! in_array( strtolower( $parsed_url['scheme'] ), [ 'http', 'https' ], true ) ) {
			return false;
		}

		// Skip adding query var to links on other URLs.
		if ( ! empty( $parsed_url['host'] ) && $this->home_host !== $parsed_url['host'] ) {
			return false;
		}

		// Skip adding query var to PHP files (e.g. wp-login.php).
		if ( ! empty( $parsed_url['path'] ) && preg_match( '/\.php$/', $parsed_url['path'] ) ) {
			return false;
		}

		// Skip adding query var to feed URLs.
		if ( ! empty( $parsed_url['path'] ) && preg_match( ':/feed/(\w+/)?$:', $parsed_url['path'] ) ) {
			return false;
		}

		// Skip adding query var to the admin.
		if ( ! empty( $parsed_url['path'] ) && false !== strpos( $parsed_url['path'], $this->admin_path ) ) {
			return false;
		}

		// Skip adding query var to content links (e.g. images).
		if ( ! empty( $parsed_url['path'] ) && false !== strpos( $parsed_url['path'], $this->content_path ) ) {
			return false;
		}

		return true;
	}
}
