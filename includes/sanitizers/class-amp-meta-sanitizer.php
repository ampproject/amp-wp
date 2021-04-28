<?php
/**
 * Class AMP_Meta_Sanitizer.
 *
 * Ensure required markup is present for valid AMP pages.
 *
 * @todo Rename to something like AMP_Ensure_Required_Markup_Sanitizer.
 *
 * @link https://amp.dev/documentation/guides-and-tutorials/start/create/basic_markup/?format=websites#required-mark-up
 * @package AMP
 */

use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Tag;

/**
 * Class AMP_Meta_Sanitizer.
 *
 * Sanitizes meta tags found in the header.
 *
 * @since 1.5.0
 * @internal
 */
class AMP_Meta_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [ // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
		'remove_initial_scale_viewport_property' => true,
	];

	/**
	 * Tag.
	 *
	 * @var string HTML <meta> tag to identify and replace with AMP version.
	 */
	public static $tag = 'meta';

	/*
	 * Tags array keys.
	 */
	const TAG_CHARSET        = 'charset';
	const TAG_HTTP_EQUIV     = 'http-equiv';
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
	 *     @type DOMElement[] $charset                  Charset meta tag(s).
	 *     @type DOMElement[] $viewport                 Viewport meta tag(s).
	 *     @type DOMElement[] $amp_script_src           <amp-script> source meta tags.
	 *     @type DOMElement[] $other                    Remaining meta tags.
	 * }
	 */
	protected $meta_tags = [
		self::TAG_CHARSET        => [],
		self::TAG_HTTP_EQUIV     => [],
		self::TAG_VIEWPORT       => [],
		self::TAG_AMP_SCRIPT_SRC => [],
		self::TAG_OTHER          => [],
	];

	/**
	 * Viewport settings to use for AMP markup.
	 *
	 * @var string
	 */
	const AMP_VIEWPORT = 'width=device-width';

	/**
	 * Spec name for the tag spec for meta elements that are allowed in the body.
	 *
	 * @since 1.5.2
	 * @var string
	 */
	const BODY_ANCESTOR_META_TAG_SPEC_NAME = 'meta name= and content=';

	/**
	 * Get tag spec for meta tags which are allowed in the body.
	 *
	 * @since 1.5.2
	 * @return string Deny pattern.
	 */
	private function get_body_meta_tag_name_attribute_deny_pattern() {
		static $pattern = null;
		if ( null === $pattern ) {
			$tag_spec = current(
				array_filter(
					AMP_Allowed_Tags_Generated::get_allowed_tag( 'meta' ),
					static function ( $spec ) {
						return isset( $spec['tag_spec']['spec_name'] ) && self::BODY_ANCESTOR_META_TAG_SPEC_NAME === $spec['tag_spec']['spec_name'];
					}
				)
			);
			$pattern  = '/' . $tag_spec['attr_spec_list']['name']['disallowed_value_regex'] . '/';
		}
		return $pattern;
	}

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		$meta_elements = iterator_to_array( $this->dom->getElementsByTagName( static::$tag ), false );

		foreach ( $meta_elements as $meta_element ) {

			// Strip whitespace around equal signs. Won't be needed after <https://github.com/ampproject/amphtml/issues/26496> is resolved.
			if ( $meta_element->hasAttribute( Attribute::CONTENT ) ) {
				$meta_element->setAttribute(
					Attribute::CONTENT,
					preg_replace( '/\s*=\s*/', '=', $meta_element->getAttribute( Attribute::CONTENT ) )
				);
			}

			/**
			 * Meta tag to process.
			 *
			 * @var DOMElement $meta_element
			 */
			if ( $meta_element->hasAttribute( Attribute::CHARSET ) ) {
				$this->meta_tags[ self::TAG_CHARSET ][] = $meta_element->parentNode->removeChild( $meta_element );
			} elseif ( $meta_element->hasAttribute( Attribute::HTTP_EQUIV ) ) {
				$this->meta_tags[ self::TAG_HTTP_EQUIV ][] = $meta_element->parentNode->removeChild( $meta_element );
			} elseif ( Attribute::VIEWPORT === $meta_element->getAttribute( Attribute::NAME ) ) {
				$this->meta_tags[ self::TAG_VIEWPORT ][] = $meta_element->parentNode->removeChild( $meta_element );
			} elseif ( Attribute::AMP_SCRIPT_SRC === $meta_element->getAttribute( Attribute::NAME ) ) {
				$this->meta_tags[ self::TAG_AMP_SCRIPT_SRC ][] = $meta_element->parentNode->removeChild( $meta_element );
			} elseif (
				$meta_element->hasAttribute( 'name' )
				&&
				preg_match( $this->get_body_meta_tag_name_attribute_deny_pattern(), $meta_element->getAttribute( 'name' ) )
			) {
				$this->meta_tags[ self::TAG_OTHER ][] = $meta_element->parentNode->removeChild( $meta_element );
			}
		}

		$this->ensure_charset_is_present();
		$this->ensure_viewport_is_present();

		$this->process_amp_script_meta_tags();

		$this->re_add_meta_tags_in_optimized_order();

		$this->ensure_boilerplate_is_present();
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
	 * If there are `@viewport` style rules, these will have been moved into the content attribute of their own meta[name=viewport]
	 * tags by the style sanitizer. When there are multiple such meta tags, this method extracts the viewport properties of each
	 * and then merges them into a single meta[name=viewport] tag. Any invalid properties will get removed by the
	 * tag-and-attribute sanitizer.
	 */
	protected function ensure_viewport_is_present() {
		if ( empty( $this->meta_tags[ self::TAG_VIEWPORT ] ) ) {
			$this->meta_tags[ self::TAG_VIEWPORT ][] = $this->create_viewport_element( static::AMP_VIEWPORT );
		} else {
			// Merge one or more meta[name=viewport] tags into one.
			$parsed_rules = [];

			/**
			 * Meta viewport element.
			 *
			 * @var DOMElement $meta_viewport
			 */
			foreach ( $this->meta_tags[ self::TAG_VIEWPORT ] as $meta_viewport ) {
				$property_pairs = explode( ',', $meta_viewport->getAttribute( 'content' ) );
				foreach ( $property_pairs as $property_pair ) {
					$exploded_pair = explode( '=', $property_pair, 2 );
					if ( isset( $exploded_pair[1] ) ) {
						$parsed_rules[ trim( $exploded_pair[0] ) ] = trim( $exploded_pair[1] );
					}
				}
			}

			// Remove initial-scale=1 to leave just width=device-width in order to avoid a tap delay hurts FID.
			if (
				! empty( $this->args['remove_initial_scale_viewport_property'] )
				&&
				isset( $parsed_rules['initial-scale'] )
				&&
				abs( (float) $parsed_rules['initial-scale'] - 1.0 ) < 0.0001
			) {
				unset( $parsed_rules['initial-scale'] );
			}

			$viewport_value = implode(
				',',
				array_map(
					static function ( $rule_name ) use ( $parsed_rules ) {
						return $rule_name . '=' . $parsed_rules[ $rule_name ];
					},
					array_keys( $parsed_rules )
				)
			);

			$this->meta_tags[ self::TAG_VIEWPORT ] = [ $this->create_viewport_element( $viewport_value ) ];
		}
	}

	/**
	 * Always ensure we have a style[amp-boilerplate] and a noscript>style[amp-boilerplate].
	 *
	 * The AMP boilerplate styles should appear at the end of the head:
	 * "Finally, specify the AMP boilerplate code. By putting the boilerplate code last, it prevents custom styles from
	 * accidentally overriding the boilerplate css rules."
	 *
	 * @link https://amp.dev/documentation/guides-and-tutorials/learn/spec/amp-boilerplate/?format=websites
	 * @link https://amp.dev/documentation/guides-and-tutorials/optimize-and-measure/optimize_amp/#optimize-the-amp-runtime-loading
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
				Attribute::CHARSET => Document\Encoding::AMP,
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
