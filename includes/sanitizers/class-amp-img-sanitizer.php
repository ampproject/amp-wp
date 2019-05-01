<?php
/**
 * Class AMP_Img_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Img_Sanitizer
 *
 * Converts <img> tags to <amp-img> or <amp-anim>
 */
class AMP_Img_Sanitizer extends AMP_Base_Sanitizer {
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
	 * @var string HTML <img> tag to identify and replace with AMP version.
	 *
	 * @since 0.2
	 */
	public static $tag = 'img';

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = array(
		'add_noscript_fallback' => true,
	);

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
		return array(
			'img' => array(
				'amp-img',
				'amp-anim',
			),
		);
	}

	/**
	 * Sanitize the <img> elements from the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.2
	 */
	public function sanitize() {

		/**
		 * Node list.
		 *
		 * @var DOMNodeList $node
		 */
		$nodes           = $this->dom->getElementsByTagName( self::$tag );
		$need_dimensions = array();

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

			if ( ! $node->hasAttribute( 'src' ) || '' === trim( $node->getAttribute( 'src' ) ) ) {
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

			$has_width  = is_numeric( $node->getAttribute( 'width' ) );
			$has_height = is_numeric( $node->getAttribute( 'height' ) );

			// Determine which images need their dimensions determined/extracted.
			$missing_dimensions = (
				( ! $has_height && 'fixed-height' === $layout )
				||
				(
					( ! $has_width || ! $has_height )
					&&
					in_array( $layout, array( 'fixed', 'responsive', 'intrinsic' ), true )
				)
			);
			if ( $missing_dimensions ) {
				$need_dimensions[ $node->getAttribute( 'src' ) ][] = $node;
			} else {
				$this->adjust_and_replace_node( $node );
			}
		}

		$this->determine_dimensions( $need_dimensions );
		$this->adjust_and_replace_nodes_in_array_map( $need_dimensions );

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
	 * "Filter" HTML attributes for <amp-anim> elements.
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
		$out = array();

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
	 * Determine width and height attribute values for images without them.
	 *
	 * Attempt to determine actual dimensions, otherwise set reasonable defaults.
	 *
	 * @param DOMElement[][] $need_dimensions Map <img> @src URLs to node for images with missing dimensions.
	 */
	private function determine_dimensions( $need_dimensions ) {

		$dimensions_by_url = AMP_Image_Dimension_Extractor::extract( array_keys( $need_dimensions ) );

		foreach ( $dimensions_by_url as $url => $dimensions ) {
			foreach ( $need_dimensions[ $url ] as $node ) {
				if ( ! $node instanceof DOMElement ) {
					continue;
				}
				$class = $node->getAttribute( 'class' );
				if ( ! $class ) {
					$class = '';
				}
				if ( ! $dimensions ) {
					$class .= ' amp-wp-unknown-size';
				}

				$width  = isset( $this->args['content_max_width'] ) ? $this->args['content_max_width'] : self::FALLBACK_WIDTH;
				$height = self::FALLBACK_HEIGHT;
				if ( isset( $dimensions['width'] ) ) {
					$width = $dimensions['width'];
				}
				if ( isset( $dimensions['height'] ) ) {
					$height = $dimensions['height'];
				}

				if ( ! is_numeric( $node->getAttribute( 'width' ) ) ) {

					// Let width have the right aspect ratio based on the height attribute.
					if ( is_numeric( $node->getAttribute( 'height' ) ) && isset( $dimensions['height'] ) && isset( $dimensions['width'] ) ) {
						$width = ( floatval( $node->getAttribute( 'height' ) ) * $dimensions['width'] ) / $dimensions['height'];
					}

					$node->setAttribute( 'width', $width );
					if ( ! isset( $dimensions['width'] ) ) {
						$class .= ' amp-wp-unknown-width';
					}
				}
				if ( ! is_numeric( $node->getAttribute( 'height' ) ) ) {

					// Let height have the right aspect ratio based on the width attribute.
					if ( is_numeric( $node->getAttribute( 'width' ) ) && isset( $dimensions['width'] ) && isset( $dimensions['height'] ) ) {
						$height = ( floatval( $node->getAttribute( 'width' ) ) * $dimensions['height'] ) / $dimensions['width'];
					}

					$node->setAttribute( 'height', $height );
					if ( ! isset( $dimensions['height'] ) ) {
						$class .= ' amp-wp-unknown-height';
					}
				}
				$node->setAttribute( 'class', trim( $class ) );
			}
		}
	}

	/**
	 * Now that all images have width and height attributes, make final tweaks and replace original image nodes
	 *
	 * @param DOMNodeList[] $node_lists Img DOM nodes (now with width and height attributes).
	 */
	private function adjust_and_replace_nodes_in_array_map( $node_lists ) {
		foreach ( $node_lists as $node_list ) {
			foreach ( $node_list as $node ) {
				$this->adjust_and_replace_node( $node );
			}
		}
	}

	/**
	 * Make final modifications to DOMNode
	 *
	 * @param DOMElement $node The img element to adjust and replace.
	 */
	private function adjust_and_replace_node( $node ) {

		$amp_data       = $this->get_data_amp_attributes( $node );
		$old_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );
		$old_attributes = $this->filter_data_amp_attributes( $old_attributes, $amp_data );
		$old_attributes = $this->maybe_add_lightbox_attributes( $old_attributes, $node );

		$new_attributes = $this->filter_attributes( $old_attributes );
		$layout         = isset( $amp_data['layout'] ) ? $amp_data['layout'] : false;
		$new_attributes = $this->filter_attachment_layout_attributes( $node, $new_attributes, $layout );

		$this->add_or_append_attribute( $new_attributes, 'class', 'amp-wp-enforced-sizes' );
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

		if ( $this->is_gif_url( $new_attributes['src'] ) ) {
			$this->did_convert_elements = true;

			$new_tag = 'amp-anim';
		} else {
			$new_tag = 'amp-img';
		}

		$img_node = AMP_DOM_Utils::create_node( $this->dom, $new_tag, $new_attributes );
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
			if ( in_array( $layout, array( 'fixed-height', 'responsive', 'fill', 'flex-item' ), true ) ) {
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
	 * Determines is a URL is considered a GIF URL
	 *
	 * @since 0.2
	 *
	 * @param string $url URL to inspect for GIF vs. JPEG or PNG.
	 *
	 * @return bool Returns true if $url ends in `.gif`
	 */
	private function is_gif_url( $url ) {
		$ext  = self::$anim_extension;
		$path = wp_parse_url( $url, PHP_URL_PATH );
		return substr( $path, -strlen( $ext ) ) === $ext;
	}
}
