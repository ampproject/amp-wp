<?php
/**
 * Class AMP_Links_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Meta_Sanitizer.
 *
 * Sanitizes meta tags found in the header.
 *
 * @since 1.5.0
 */
class AMP_Meta_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @var string HTML <meta> tag to identify and replace with AMP version.
	 */
	public static $tag = 'meta';

	/**
	 * Placeholder for default arguments, to be set in child classes.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [ // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
		'use_document_element' => true, // We want to work on the header, so we need the entire document.
	];

	/**
	 * Reference to the shared XPath object to query the DOM.
	 *
	 * @var DOMXPath
	 */
	protected $xpath;

	/**
	 * The document's <head> element.
	 *
	 * @var DOMElement
	 */
	protected $head;

	/*
	 * Tags array keys.
	 */
	const TAG_CHARSET        = 'charset';
	const TAG_VIEWPORT       = 'viewport';
	const TAG_AMP_SCRIPT_SRC = 'amp_script_src';
	const TAG_OTHER          = 'other';

	/**
	 * Associative array of DOMElement arrays.
	 *
	 * Each key in the root level defines one group of meta tags to process.
	 *
	 * @var array $tags {
	 *     An array of meta tag groupings.
	 *
	 *     @type DOMElement[] $charset        Charset meta tag(s).
	 *     @type DOMElement[] $viewport       Viewport meta tag(s).
	 *     @type DOMElement[] $amp_script_src <amp-script> source meta tags.
	 *     @type DOMElement[] $other          Remaining meta tags.
	 * }
	 */
	protected $meta_tags = [
		self::TAG_CHARSET        => [],
		self::TAG_VIEWPORT       => [],
		self::TAG_AMP_SCRIPT_SRC => [],
		self::TAG_OTHER          => [],
	];

	/**
	 * Charset to use for AMP markup.
	 *
	 * @var string
	 */
	const AMP_CHARSET = 'utf-8';

	/**
	 * Viewport settings to use for AMP markup.
	 *
	 * @var string
	 */
	const AMP_VIEWPORT = 'width=device-width';

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		$this->xpath = new DOMXPath( $this->dom );
		$this->head  = $this->ensure_head_is_present();

		$charset = $this->detect_charset();

		foreach ( $this->dom->getElementsByTagName( static::$tag ) as $element ) {
			/**
			 * Meta tag to process.
			 *
			 * @var DOMElement $element
			 */
			$element = $element->parentNode->removeChild( $element );
			if ( $element->hasAttribute( 'charset' ) ) {
				$this->meta_tags[ self::TAG_CHARSET ][] = $element;
			} elseif ( 'viewport' === $element->getAttribute( 'name' ) ) {
				$this->meta_tags[ self::TAG_VIEWPORT ][] = $element;
			} elseif ( 'amp-script-src' === $element->getAttribute( 'name' ) ) {
				$this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ][] = $element;
			} else {
				$this->meta_tags[ self::TAG_OTHER ][] = $element;
			}
		}

		$this->ensure_charset_is_present( $charset );

		if ( ! $this->is_correct_charset() ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement
			// @TODO Re-encode the content into UTF-8.
			// ... sure?
		}

		$this->ensure_viewport_is_present();

		$this->process_amp_script_meta_tags();

		$this->re_add_meta_tags_in_optimized_order();
	}

	/**
	 * Ensure that the <head> element is present in the document.
	 *
	 * @return DOMElement The document's <head> element.
	 */
	protected function ensure_head_is_present() {
		$head = $this->dom->getElementsByTagName( 'head' )->item( 0 );

		if ( ! $head ) {
			$head = $this->dom->createElement( 'head' );
			$head = $this->dom->documentElement->insertBefore( $head, $this->dom->documentElement->firstChild );
		}

		return $head;
	}

	/**
	 * Detect the charset of the document.
	 *
	 * @return string|false Detected charset of the document, or false if none.
	 */
	protected function detect_charset() {
		$charset = false;

		// Check for HTML 4 http-equiv meta tags.
		$http_equiv_tag = $this->xpath->query( '//meta[ @http-equiv and @content ]' )->item( 0 );
		if ( $http_equiv_tag ) {
			$http_equiv_tag->parentNode->removeChild( $http_equiv_tag );

			// Check for the existence of a proper charset attribute first.
			$charset = $http_equiv_tag->getAttribute( 'charset' );
			if ( ! $charset ) {
				// If not, check whether the charset is included with the content type, and use that.
				$content = $http_equiv_tag->getAttribute( 'content' );

				$matches = [];
				if ( preg_match( '/;\s*charset=(?<charset>[^;]+)/', $content, $matches ) && ! empty( $matches['charset'] ) ) {
					$charset = $matches['charset'];
				}
			}
		}

		// Check for HTML 5 charset meta tag. This overrides the HTML 4 charset.
		$charset_tag = $this->xpath->query( '//meta[ @charset ]' )->item( 0 );
		if ( $charset_tag ) {
			$charset_tag = $charset_tag->parentNode->removeChild( $charset_tag );
			$charset     = $charset_tag->getAttribute( 'charset' );
		}

		return $charset;
	}

	/**
	 * Always ensure that we have an HTML 5 charset meta tag.
	 *
	 * The charset defaults to utf-8, which is also what AMP requires.
	 *
	 * @param string|false $charset Optional. Charset that was already detected. False if none. Defaults to false.
	 */
	protected function ensure_charset_is_present( $charset = false ) {
		if ( ! empty( $this->meta_tags[ self::TAG_CHARSET ] ) ) {
			return;
		}

		$this->meta_tags[ self::TAG_CHARSET ][] = $this->create_charset_element( $charset ?: static::AMP_CHARSET );
	}

	/**
	 * Always ensure we have a viewport tag.
	 *
	 * The viewport defaults to 'width=device-width', which is the bare minimum that AMP requires.
	 */
	protected function ensure_viewport_is_present() {
		if ( empty( $this->meta_tags[ self::TAG_VIEWPORT ] ) ) {
			$this->meta_tags[ self::TAG_VIEWPORT ][] = $this->create_viewport_element( static::AMP_VIEWPORT );
			return;
		}

		// Ensure we have the 'width=device-width' setting included.
		$viewport_tag      = $this->meta_tags[ self::TAG_VIEWPORT ][0];
		$viewport_content  = $viewport_tag->getAttribute( 'content' );
		$viewport_settings = array_map( 'trim', explode( ',', $viewport_content ) );
		$width_found       = false;

		foreach ( $viewport_settings as $index => $viewport_setting ) {
			list( $property, $value ) = array_map( 'trim', explode( '=', $viewport_setting ) );
			if ( 'width' === $property ) {
				if ( 'device-width' !== $value ) {
					$viewport_settings[ $index ] = 'width=device-width';
				}
				$width_found = true;
				break;
			}
		}

		if ( ! $width_found ) {
			array_unshift( $viewport_settings, 'width=device-width' );
		}

		$viewport_tag->setAttribute( 'content', implode( ',', $viewport_settings ) );
	}

	/**
	 * Parse and concatenate <amp-script> source meta tags.
	 */
	protected function process_amp_script_meta_tags() {
		if ( empty( $this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ] ) ) {
			return;
		}

		$first_meta_amp_script_src = array_shift( $this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ] );
		$content_values = [ $first_meta_amp_script_src->getAttribute( 'content' ) ];

		// Merge (and remove) any subsequent meta amp-script-src elements.
		while ( ! empty ( $this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ] ) ) {
			$meta_amp_script_src = array_shift( $this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ] );
			$content_values[]    = $meta_amp_script_src->getAttribute( 'content' );
		}

		$first_meta_amp_script_src->setAttribute( 'content', implode( ' ', $content_values ) );
	}

	/**
	 * Create a new meta tag for the charset value.
	 *
	 * @param string $charset Character set to use.
	 * @return DOMElement New meta tag with requested charset.
	 */
	protected function create_charset_element( $charset ) {
		return AMP_DOM_Utils::create_node(
			$this->dom,
			'meta',
			[
				'charset' => strtolower( $charset ),
			]
		);
	}

	/**
	 * Create a new meta tag for the viewport setting.
	 *
	 * @param string $viewport Viewport setting to use.
	 * @return DOMElement New meta tag with requested viewport setting.
	 */
	protected function create_viewport_element( $viewport ) {
		return AMP_DOM_Utils::create_node(
			$this->dom,
			'meta',
			[
				'name'    => 'viewport',
				'content' => $viewport,
			]
		);
	}

	/**
	 * Check whether the charset is the correct one according to AMP requirements.
	 *
	 * @return bool Whether the charset is the correct one.
	 */
	protected function is_correct_charset() {
		if ( empty( $this->meta_tags[ self::TAG_CHARSET ] ) ) {
			throw new LogicException( 'Failed to ensure a charset meta tag is present' );
		}

		$charset_element = $this->meta_tags[ self::TAG_CHARSET ][0];

		return static::AMP_CHARSET === strtolower( $charset_element->getAttribute( 'charset' ) );
	}

	/**
	 * Re-add the meta tags to the <head> node in the optimized order.
	 *
	 * The order is defined by the array entries in $this->meta_tags.
	 */
	protected function re_add_meta_tags_in_optimized_order() {
		/**
		 * Previous meta tag to append to.
		 *
		 * @var DOMElement $previous_meta_tag
		 */
		$previous_meta_tag = null;
		foreach ( $this->meta_tags as $meta_tag_group ) {
			foreach ( $meta_tag_group as $meta_tag ) {
				if ( $previous_meta_tag ) {
					$previous_meta_tag = $this->head->insertBefore( $meta_tag, $previous_meta_tag->nextSibling );
				} else {
					$previous_meta_tag = $this->head->insertBefore( $meta_tag, $this->head->firstChild );
				}
			}
		}
	}
}
