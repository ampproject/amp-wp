<?php
/**
 * Class AMP_Links_Sanitizer.
 *
 * @package AMP
 */

use Amp\Attribute;
use Amp\Dom\Document;
use Amp\Tag;

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

	/*
	 * Tags array keys.
	 */
	const TAG_CHARSET         = 'charset';
	const TAG_VIEWPORT        = 'viewport';
	const TAG_AMP_SCRIPT_SRC  = 'amp_script_src';
	const TAG_OTHER           = 'other';
	const TAG_AMP_BOILERPLATE = 'amp_boilerplate';

	/**
	 * Associative array of DOMElement arrays.
	 *
	 * Each key in the root level defines one group of meta tags to process.
	 *
	 * @var array $tags {
	 *     An array of meta tag groupings.
	 *
	 *     @type DOMElement[] $charset         Charset meta tag(s).
	 *     @type DOMElement[] $viewport        Viewport meta tag(s).
	 *     @type DOMElement[] $amp_script_src  <amp-script> source meta tags.
	 *     @type DOMElement[] $other           Remaining meta tags.
	 *     @type DOMElement[] $amp_boilerplate Amp boilerplate style tag.
	 * }
	 */
	protected $meta_tags = [
		self::TAG_CHARSET         => [],
		self::TAG_VIEWPORT        => [],
		self::TAG_AMP_SCRIPT_SRC  => [],
		self::TAG_OTHER           => [],
		self::TAG_AMP_BOILERPLATE => [],
	];

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
		$this->ensure_boilerplate_is_present();

		$elements = $this->dom->getElementsByTagName( static::$tag );

		// Remove all nodes for easy reordering later on.
		$elements = array_map(
			static function ( $element ) {
				return $element->parentNode->removeChild( $element );
			},
			iterator_to_array( $elements, false )
		);

		foreach ( $elements as $element ) {

			// Strip whitespace around equal signs. Won't be needed after <https://github.com/ampproject/amphtml/issues/26496> is resolved.
			if ( $element->hasAttribute( Attribute::CONTENT ) ) {
				$element->setAttribute(
					Attribute::CONTENT,
					preg_replace( '/\s*=\s*/', '=', $element->getAttribute( Attribute::CONTENT ) )
				);
			}

			/**
			 * Meta tag to process.
			 *
			 * @var DOMElement $element
			 */
			if ( $element->hasAttribute( Attribute::CHARSET ) ) {
				$this->meta_tags[ self::TAG_CHARSET ][] = $element;
			} elseif ( Attribute::VIEWPORT === $element->getAttribute( Attribute::NAME ) ) {
				$this->meta_tags[ self::TAG_VIEWPORT ][] = $element;
			} elseif ( Attribute::AMP_SCRIPT_SRC === $element->getAttribute( Attribute::NAME ) ) {
				$this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ][] = $element;
			} elseif (
				Tag::NOSCRIPT === $element->tagName
				&& $element->firstChild instanceof DOMElement
				&& Tag::STYLE === $element->firstChild->tagName
				&& $element->firstChild->hasAttribute( Attribute::AMP_BOILERPLATE )
			) {
				$this->meta_tags[ self::TAG_AMP_BOILERPLATE ][] = $element;
			} elseif (
				Tag::STYLE === $element->tagName
				&& $element->hasAttribute( Attribute::AMP_BOILERPLATE )
			) {
				$this->meta_tags[ self::TAG_AMP_BOILERPLATE ][] = $element;
			} else {
				$this->meta_tags[ self::TAG_OTHER ][] = $element;
			}
		}

		$this->ensure_charset_is_present();
		$this->ensure_viewport_is_present();

		$this->process_amp_script_meta_tags();

		$this->re_add_meta_tags_in_optimized_order();
	}

	/**
	 * Always ensure that we have an HTML 5 charset meta tag.
	 *
	 * The charset is set to utf-8, which is what AMP requires.
	 */
	protected function ensure_charset_is_present() {
		if ( ! empty( $this->meta_tags[ self::TAG_CHARSET ] ) ) {
			return;
		}

		$this->meta_tags[ self::TAG_CHARSET ][] = $this->create_charset_element();
	}

	/**
	 * Always ensure we have a viewport tag.
	 *
	 * The viewport defaults to 'width=device-width', which is the bare minimum that AMP requires.
	 */
	protected function ensure_viewport_is_present() {
		if ( empty( $this->meta_tags[ self::TAG_VIEWPORT ] ) ) {
			$this->meta_tags[ self::TAG_VIEWPORT ][] = $this->create_viewport_element( static::AMP_VIEWPORT );
		}
	}

	/**
	 * Always ensure we have a <style amp-boilerplate> tag.
	 */
	protected function ensure_boilerplate_is_present() {
		$style = $this->dom->xpath->query( './style[ @amp-boilerplate ]', $this->dom->head )->item( 0 );

		if ( ! $style ) {
			$style = $this->dom->createElement( Tag::STYLE );
			$style->setAttribute( Attribute::AMP_BOILERPLATE, '' );
			$style->appendChild( $this->dom->createTextNode( amp_get_boilerplate_stylesheets()[0] ) );
		} else {
			$style->parentNode->removeChild( $style ); // So we can move it.
		}

		$this->dom->head->appendChild( $style );

		$noscript = $this->dom->xpath->query( './noscript[ style[ @amp-boilerplate ] ]', $this->dom->head )->item( 0 );

		if ( ! $noscript ) {
			$noscript = $this->dom->createElement( Tag::NOSCRIPT );
			$style    = $this->dom->createElement( Tag::STYLE );
			$style->setAttribute( Attribute::AMP_BOILERPLATE, '' );
			$style->appendChild( $this->dom->createTextNode( amp_get_boilerplate_stylesheets()[1] ) );
			$noscript->appendChild( $style );
		} else {
			$noscript->parentNode->removeChild( $noscript ); // So we can move it.
		}

		$this->dom->head->appendChild( $noscript );
	}

	/**
	 * Parse and concatenate <amp-script> source meta tags.
	 */
	protected function process_amp_script_meta_tags() {
		if ( empty( $this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ] ) ) {
			return;
		}

		$first_meta_amp_script_src = array_shift( $this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ] );
		$content_values            = [ $first_meta_amp_script_src->getAttribute( Attribute::CONTENT ) ];

		// Merge (and remove) any subsequent meta amp-script-src elements.
		while ( ! empty( $this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ] ) ) {
			$meta_amp_script_src = array_shift( $this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ] );
			$content_values[]    = $meta_amp_script_src->getAttribute( Attribute::CONTENT );
		}

		$first_meta_amp_script_src->setAttribute( Attribute::CONTENT, implode( ' ', $content_values ) );

		$this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ][] = $first_meta_amp_script_src;
	}

	/**
	 * Create a new meta tag for the charset value.
	 *
	 * @return DOMElement New meta tag with requested charset.
	 */
	protected function create_charset_element() {
		return AMP_DOM_Utils::create_node(
			$this->dom,
			Tag::META,
			[
				Attribute::CHARSET => Document::AMP_ENCODING,
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
			Tag::META,
			[
				Attribute::NAME    => Attribute::VIEWPORT,
				Attribute::CONTENT => $viewport,
			]
		);
	}

	/**
	 * Re-add the meta tags to the <head> node in the optimized order.
	 *
	 * The order is defined by the array entries in $this->meta_tags.
	 *
	 * The optimal loading order for AMP pages is documented at:
	 * https://amp.dev/documentation/guides-and-tutorials/optimize-and-measure/optimize_amp/#optimize-the-amp-runtime-loading
	 *
	 * "1. The first tag should be the meta charset tag, followed by any remaining meta tags."
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
					$previous_meta_tag = $this->dom->head->insertBefore( $meta_tag, $previous_meta_tag->nextSibling );
				} else {
					$previous_meta_tag = $this->dom->head->insertBefore( $meta_tag, $this->dom->head->firstChild );
				}
			}
		}
	}
}
