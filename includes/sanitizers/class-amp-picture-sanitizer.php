<?php
/**
 * Class AMP_Picture_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Picture_Sanitizer
 *
 * Converts <picture> tags to <amp-img>
 */
class AMP_Picture_Sanitizer extends AMP_Base_Sanitizer {
	use AMP_Noscript_Fallback;

	/**
	 * Value used for width attribute when $attributes['width'] is empty.
	 *
	 * @since 0.2
	 *
	 * @const int
	 */
	const FALLBACK_WIDTH = 600;

	/**
	 * Value used for height attribute when $attributes['height'] is empty.
	 *
	 * @since 0.2
	 *
	 * @const int
	 */
	const FALLBACK_HEIGHT = 400;

	/**
	 * Tag.
	 *
	 * @var string HTML <picture> tag to identify and replace with AMP version.
	 *
	 * @since 0.2
	 */
	public static $tag = 'picture';

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'add_noscript_fallback' => true,
	];

	/**
	 * Animation extension.
	 *
	 * @var string
	 */
	private static $anim_extension = '.gif';

	/**
	 * Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
	 *
	 * @return array Mapping.
	 */
	public function get_selector_conversion_mapping() {
		return [
			'picture' => [ 'amp-img' ],
		];
	}

	/**
	 * Sanitize the <picture> elements from the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.2
	 */
	public function sanitize() {

		/**
		 * Node list.
		 *
		 * @var DOMNodeList $node
		 */
		$nodes = $this->dom->getElementsByTagName( self::$tag );

		$num_nodes = $nodes->length;

		if ( 0 === $num_nodes ) {
			return;
		}

		if ( $this->args['add_noscript_fallback'] ) {
			$this->initialize_noscript_allowed_attributes( self::$tag );
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			// Skip element if already inside of an AMP element as a noscript fallback.
			if ( $this->is_inside_amp_noscript( $node ) ) {
				continue;
			}

			if ( ! $node->hasChildNodes() ) {
				$this->remove_invalid_child( $node );
				continue;
			}

			$fallback_img = $this->get_fallback_img( $node );

			// Invalid if we found 0 or more than 1 <img> child node.
			if ( ! $fallback_img ) {
				$this->remove_invalid_child( $node );
				continue;
			}

			// The fallback <img> needs to be a valid image tag.
			if ( ! $fallback_img->hasAttribute( 'src' ) || '' === trim( $fallback_img->getAttribute( 'src' ) ) ) {
				$this->remove_invalid_child( $node );
				continue;
			}

			if ( $node->hasAttribute( 'data-amp-layout' ) ) {
				$layout = $node->getAttribute( 'data-amp-layout' );
			} elseif ( $node->hasAttribute( 'layout' ) ) {
				$layout = $node->getAttribute( 'layout' );
			} else {
				$layout = 'intrinsic';
			}

			if ( $layout ) {
				$fallback_img->setAttribute( 'data-amp-layout', $layout );
			}

			$new_node = $this->adjust_and_replace_node( $node, $fallback_img );

			foreach ( $this->get_source_nodes( $node ) as $source_node ) {
				$this->assimilate_source_node( $new_node, $source_node );
			}
		}

		/*
		 * Opt-in to amp-img-auto-sizes experiment.
		 * This is needed because the sizes attribute is removed from all img elements converted to amp-img
		 * in order to prevent the undesirable setting of the width. This $meta tag can be removed once the
		 * experiment ends (and the feature has been fully launched).
		 * See <https://github.com/ampproject/amphtml/issues/21371> and <https://github.com/ampproject/amp-wp/pull/2036>.
		 */
		$head = $this->dom->getElementsByTagName( 'head' )->item( 0 );
		if ( $head ) {
			$meta = $this->dom->createElement( 'meta' );
			$meta->setAttribute( 'name', 'amp-experiments-opt-in' );
			$meta->setAttribute( 'content', 'amp-img-auto-sizes' );
			$head->insertBefore( $meta, $head->firstChild );
		}
	}

	/**
	 * "Filter" HTML attributes for <amp-img> elements.
	 *
	 * @since 0.2
	 *
	 * @param string[] $attributes {
	 *      Attributes.
	 *
	 *      @type string $src Image URL - Pass along if found
	 *      @type string $alt <img> `alt` attribute - Pass along if found
	 *      @type string $class <img> `class` attribute - Pass along if found
	 *      @type string $srcset <img> `srcset` attribute - Pass along if found
	 *      @type string $sizes <img> `sizes` attribute - Pass along if found
	 *      @type string $on <img> `on` attribute - Pass along if found
	 *      @type string $attribution <img> `attribution` attribute - Pass along if found
	 *      @type int $width <img> width attribute - Set to numeric value if px or %
	 *      @type int $height <img> width attribute - Set to numeric value if px or %
	 * }
	 * @return array Returns HTML attributes; removes any not specifically declared above from input.
	 */
	private function filter_attributes( $attributes ) {
		$out = [];

		foreach ( $attributes as $name => $value ) {
			switch ( $name ) {
				case 'width':
				case 'height':
					$out[ $name ] = $this->sanitize_dimension( $value, $name );
					break;

				case 'data-amp-layout':
					$out['layout'] = $value;
					break;

				case 'data-amp-noloading':
					$out['noloading'] = $value;
					break;

				default:
					$out[ $name ] = $value;
					break;
			}
		}

		return $out;
	}

	/**
	 * Make final modifications to DOMNode
	 *
	 * @param DOMElement $node         The picture element to adjust and replace.
	 * @param DOMElement $fallback_img Fallback img element provided in picture tag.
	 *
	 * @return DOMElement Replacement node.
	 */
	private function adjust_and_replace_node( $node, $fallback_img ) {

		$amp_data       = $this->get_data_amp_attributes( $fallback_img );
		$old_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $fallback_img );
		$old_attributes = $this->filter_data_amp_attributes( $old_attributes, $amp_data );
		$old_attributes = $this->maybe_add_lightbox_attributes( $old_attributes, $fallback_img );

		$new_attributes = $this->filter_attributes( $old_attributes );
		$layout         = isset( $amp_data['layout'] ) ? $amp_data['layout'] : false;
		$new_attributes = $this->filter_attachment_layout_attributes( $fallback_img, $new_attributes, $layout );

		if ( empty( $new_attributes['layout'] ) && ! empty( $new_attributes['height'] ) && ! empty( $new_attributes['width'] ) ) {
			// Use responsive images when a theme supports wide and full-bleed images.
			if ( ! empty( $this->args['align_wide_support'] ) && $node->parentNode && 'figure' === $node->parentNode->nodeName && preg_match( '/(^|\s)(alignwide|alignfull)(\s|$)/', $node->parentNode->getAttribute( 'class' ) ) ) {
				$new_attributes['layout'] = 'responsive';
			} else {
				$new_attributes['layout'] = 'intrinsic';
			}
		}

		// Remove sizes attribute since it causes headaches in AMP and because AMP will generate it for us. See <https://github.com/ampproject/amphtml/issues/21371>.
		unset( $new_attributes['sizes'] );

		$img_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-img', $new_attributes );
		$node->parentNode->replaceChild( $img_node, $node );

		/*
		 * Prevent inline style on an image from rendering the amp-img invisible or conflicting with the required display.
		 * This could eventually be expanded to fixup inline styles for elements other than images, but the reality
		 * is that this is not going to completely solve the problem for images as well, since it will not handle the
		 * case where an image gets a display:inline style via a style rule.
		 * See <https://github.com/ampproject/amp-wp/issues/1803>.
		 */
		if ( $img_node->hasAttribute( 'style' ) ) {
			$layout = $img_node->getAttribute( 'layout' );
			if ( in_array( $layout, [ 'fixed-height', 'responsive', 'fill', 'flex-item' ], true ) ) {
				$required_display = 'block';
			} elseif ( 'nodisplay' === $layout ) {
				$required_display = 'none';
			} else {
				// This is also the default for any AMP element (.i-amphtml-element).
				$required_display = 'inline-block';
			}
			$img_node->setAttribute(
				'style',
				preg_replace(
					'/\bdisplay\s*:\s*[a-z\-]+\b/',
					"display:$required_display",
					$img_node->getAttribute( 'style' )
				)
			);
		}

		$can_include_noscript = (
			$this->args['add_noscript_fallback']
			&&
			( $node->hasAttribute( 'src' ) && ! preg_match( '/^http:/', $node->getAttribute( 'src' ) ) )
			&&
			( ! $node->hasAttribute( 'srcset' ) || ! preg_match( '/http:/', $node->getAttribute( 'srcset' ) ) )
		);
		if ( $can_include_noscript ) {
			// Preserve original node in noscript for no-JS environments.
			$this->append_old_node_noscript( $img_node, $node, $this->dom );
		}

		return $img_node;
	}

	/**
	 * Set lightbox attributes.
	 *
	 * @param array   $attributes Array of attributes.
	 * @param DomNode $node Array of AMP attributes.
	 * @return array Updated attributes.
	 */
	private function maybe_add_lightbox_attributes( $attributes, $node ) {
		$parent_node = $node->parentNode;
		if ( ! ( $parent_node instanceof DOMElement ) || 'figure' !== $parent_node->tagName ) {
			return $attributes;
		}

		$parent_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $parent_node );

		if ( isset( $parent_attributes['data-amp-lightbox'] ) && true === filter_var( $parent_attributes['data-amp-lightbox'], FILTER_VALIDATE_BOOLEAN ) ) {
			$attributes['data-amp-lightbox'] = '';
			$attributes['on']                = 'tap:' . self::AMP_IMAGE_LIGHTBOX_ID;
			$attributes['role']              = 'button';
			$attributes['tabindex']          = 0;

			$this->maybe_add_amp_image_lightbox_node();
		}

		return $attributes;
	}

	/**
	 * Retrieve the fallback image node to use.
	 *
	 * @param DOMElement $node Node to extract the fallback image from.
	 * @return DOMElement|false Node of the fallback image, or false if none found.
	 */
	private function get_fallback_img( DOMElement $node ) {
		$fallback = false;

		foreach ( $node->childNodes as $childNode ) {
			if ( 'img' === $childNode->tagName ) {
				if ( false !== $fallback ) {
					// Invalid <picture> structure (contains multiple <img> tags), abort with error.
					return false;
				}

				$fallback = $childNode;
			}
		}

		return $fallback;
	}

	/**
	 * Retrieve the source nodes for a picture node.
	 *
	 * @param DOMElement $node Picture node to retrieve the source nodes from.
	 * @return array Array of source node elements.
	 */
	private function get_source_nodes( DOMElement $node ) {
		$source_nodes = [];

		foreach ( $node->childNodes as $childNode ) {
			if ( 'source' === $childNode->tagName ) {
				$source_nodes[] = $childNode;
			}
		}

		return $source_nodes;
	}

	private function assimilate_source_node( DOMElement $node, DOMElement $source ) {
		// TODO: parse <source> elements and turn them into srcset attributes or nested <amp-img> child nodes.
	}
}
