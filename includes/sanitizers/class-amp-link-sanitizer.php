<?php
/**
 * Class AMP_Comments_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Link_Sanitizer
 *
 * Adapts links for AMP-to-AMP navigation in Transitional mode.
 *
 * Adapted from https://gist.github.com/westonruter/f9ee9ea717d52471bae092879e3d52b0
 */
class AMP_Link_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Default A2A meta tag content.
	 *
	 * @var string
	 */
	const DEFAULT_A2A_META_CONTENT = 'AMP-Redirect-To; AMP.navigateTo';

	/**
	 * Placeholder for default args, to be set in child classes.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [ // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
		'add_amphtml_rel'   => true,
		'add_query_vars'    => true, // Set to false when in native mode. Overridden in \AMP_To_AMP\filter_content_sanitizers().
		'has_theme_support' => false, // Set to true when theme has 'amp' support. Overridden in \AMP_To_AMP\filter_content_sanitizers().
		'add_a2a_meta'      => self::DEFAULT_A2A_META_CONTENT, // Only relevant when theme support is present.
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
	 * @param DOMDocument $dom  Document.
	 * @param array       $args Args.
	 */
	public function __construct( DOMDocument $dom, array $args = [] ) {
		parent::__construct( $dom, $args );

		$this->home_host    = wp_parse_url( home_url(), PHP_URL_HOST );
		$this->content_path = wp_parse_url( content_url( '/' ), PHP_URL_PATH );
		$this->admin_path   = wp_parse_url( admin_url(), PHP_URL_PATH );
	}

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		if ( $this->args['has_theme_support'] && $this->args['add_a2a_meta'] ) {
			$this->add_a2a_meta( $this->args['add_a2a_meta'] );
		}

		$this->process_links();
	}

	/**
	 * Add the amp-to-amp-navigation meta tag.
	 *
	 * @param string $content The content for the meta tag, for example 'AMP-Redirect-To; AMP.navigateTo'.
	 * @return DOMElement|null The added meta element if successful.
	 */
	public function add_a2a_meta( $content = self::DEFAULT_A2A_META_CONTENT ) {
		$head = $this->dom->documentElement->getElementsByTagName( 'head' )->item( 0 );
		if ( ! $head || ! $content ) {
			return null;
		}
		$meta = $this->dom->createElement( 'meta' );
		$meta->setAttribute( 'name', 'amp-to-amp-navigation' );
		$meta->setAttribute( 'content', $content );
		$head->appendChild( $meta );
		return $meta;
	}

	/**
	 * Process links by adding rel=amphtml and AMP query var.
	 */
	public function process_links() {
		/**
		 * Element.
		 *
		 * @var DOMElement $element
		 */
		$xpath = new DOMXPath( $this->dom );

		// Remove admin bar from DOM to prevent mutating it.
		$admin_bar_container   = $this->dom->getElementById( 'wpadminbar' );
		$admin_bar_placeholder = null;
		if ( $admin_bar_container ) {
			$admin_bar_placeholder = $this->dom->createComment( 'wpadminbar' );
			$admin_bar_container->parentNode->replaceChild( $admin_bar_placeholder, $admin_bar_container );
		}

		foreach ( $xpath->query( '//*[ local-name() = "a" or local-name() = "area" ]' ) as $element ) {
			if ( ! $element->hasAttribute( 'href' ) ) {
				continue;
			}

			$href = $element->getAttribute( 'href' );

			if ( $this->is_frontend_url( $href ) && '#' !== substr( $href, 0, 1 ) ) {
				if ( $this->args['add_amphtml_rel'] ) {
					$rel  = $element->hasAttribute( 'rel' ) ? $element->getAttribute( 'rel' ) . ' ' : '';
					$rel .= 'amphtml';
					$element->setAttribute( 'rel', $rel );
				}

				if ( $this->args['add_query_vars'] ) {
					$href = add_query_arg( amp_get_slug(), '', $href );
					$element->setAttribute( 'href', $href );
					$element->setAttribute( 'data-href', $href );
				}
			}
		}

		foreach ( $xpath->query( '//form[ @action and translate( @method, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "get" ]' ) as $element ) {
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
