<?php
/**
 * Class AMP_Links_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\Dom\Document;

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
 */
class AMP_Link_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Default meta tag content.
	 *
	 * @var string
	 */
	const DEFAULT_META_CONTENT = 'AMP-Redirect-To; AMP.navigateTo';

	/**
	 * The rel attribute value for AMP links.
	 *
	 * @var string
	 */
	const REL_VALUE_AMP = 'amphtml';

	/**
	 * The rel attribute value that will force non-AMP links.
	 *
	 * Normally, in a paired mode, links to the same origin will be for AMP.
	 * But by adding this rel value, the link will be to non-AMP.
	 *
	 * @var string
	 */
	const REL_VALUE_NON_AMP_TO_AMP = 'noamphtml';

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
		/**
		 * Element.
		 *
		 * @var DOMElement $element
		 */

		// Remove admin bar from DOM to prevent mutating it.
		$admin_bar_container   = $this->dom->getElementById( 'wpadminbar' );
		$admin_bar_placeholder = null;
		if ( $admin_bar_container ) {
			$admin_bar_placeholder = $this->dom->createComment( 'wpadminbar' );
			$admin_bar_container->parentNode->replaceChild( $admin_bar_placeholder, $admin_bar_container );
		}

		foreach ( $this->dom->xpath->query( '//*[ local-name() = "a" or local-name() = "area" ]' ) as $element ) {
			if ( ! $element->hasAttribute( 'href' ) ) {
				continue;
			}

			$href = $element->getAttribute( 'href' );
			/**
			 * One or more rel values that were attributed to the href.
			 *
			 * @var string[] $rel
			 */
			$rel = $element->hasAttribute( 'rel' ) ? array_filter( preg_split( '/\s+/', $element->getAttribute( 'rel' ) ) ) : [];
			$pos = array_search( self::REL_VALUE_NON_AMP_TO_AMP, $rel, true );
			if ( false !== $pos ) {
				// The rel has a value to opt-out of AMP-to-AMP links, so strip it and ensure the link is to non-AMP.
				unset( $rel[ $pos ] );
				if ( empty( $rel ) ) {
					$element->removeAttribute( 'rel' );
				} else {
					$element->setAttribute( 'rel', implode( ' ', $rel ) );
				}
			} elseif (
				$this->is_frontend_url( $href )
				&&
				'#' !== substr( $href, 0, 1 )
				&&
				! in_array( strtok( $href, '#' ), $this->args['excluded_urls'], true )
			) {
				// Always add the amphtml link relation when linking enabled.
				array_push( $rel, self::REL_VALUE_AMP );
				$element->setAttribute( 'rel', implode( ' ', $rel ) );

				// Only add the AMP query var when requested (in Transitional or Reader mode).
				if ( ! empty( $this->args['paired'] ) ) {
					$href = add_query_arg( amp_get_slug(), '', $href );
					$element->setAttribute( 'href', $href );
				}
			}
		}

		foreach ( $this->dom->xpath->query( '//form[ @action and translate( @method, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "get" ]' ) as $element ) {
			if ( $this->is_frontend_url( $element->getAttribute( 'action' ) ) ) {
				$input = $this->dom->createElement( 'input' );
				$input->setAttribute( 'name', amp_get_slug() );
				$input->setAttribute( 'value', '' );
				$input->setAttribute( 'type', 'hidden' );
				$element->appendChild( $input );
			}
		}

		// Replace the admin bar after mutations are done.
		if ( $admin_bar_container && $admin_bar_placeholder ) {
			$admin_bar_placeholder->parentNode->replaceChild( $admin_bar_container, $admin_bar_placeholder );
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
