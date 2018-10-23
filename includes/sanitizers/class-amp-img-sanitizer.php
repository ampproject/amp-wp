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

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			if ( ! $node->hasAttribute( 'src' ) || '' === trim( $node->getAttribute( 'src' ) ) ) {
				$this->remove_invalid_child( $node );
				continue;
			}

			// Determine which images need their dimensions determined/extracted.
			if ( ! is_numeric( $node->getAttribute( 'width' ) ) || ! is_numeric( $node->getAttribute( 'height' ) ) ) {
				$need_dimensions[ $node->getAttribute( 'src' ) ][] = $node;
			} else {
				$this->adjust_and_replace_node( $node );
			}
		}

		$this->determine_dimensions( $need_dimensions );
		$this->adjust_and_replace_nodes_in_array_map( $need_dimensions );
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
	 * @param DOMElement $node The DOMNode to adjust and replace.
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
			$new_attributes['layout'] = 'intrinsic';
		}

		if ( $this->is_gif_url( $new_attributes['src'] ) ) {
			$this->did_convert_elements = true;

			$new_tag = 'amp-anim';
		} else {
			$new_tag = 'amp-img';
		}
		$new_node = AMP_DOM_Utils::create_node( $this->dom, $new_tag, $new_attributes );
		$new_node = $this->handle_centering( $new_node );
		$node->parentNode->replaceChild( $new_node, $node );
		$this->add_auto_width_to_figure( $new_node );
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

	/**
	 * Handles an issue with the aligncenter class.
	 *
	 * If the <amp-img> has the class aligncenter, this strips the class and wraps it in a <figure> to center the image.
	 * There was an issue where the aligncenter class overrode the "display: inline-block" rule of AMP's layout="intrinsic" attribute.
	 * So this strips that class, and instead wraps the image in a <figure> to center it.
	 *
	 * @since 0.7
	 * @see https://github.com/Automattic/amp-wp/issues/1104
	 *
	 * @param DOMElement $node The <amp-img> node.
	 * @return DOMElement $node The <amp-img> node, possibly wrapped in a <figure>.
	 */
	public function handle_centering( $node ) {
		$align_class = 'aligncenter';
		$classes     = $node->getAttribute( 'class' );
		$width       = $node->getAttribute( 'width' );

		// If this doesn't have a width attribute, centering it in the <figure> wrapper won't work.
		if ( empty( $width ) || ! in_array( $align_class, preg_split( '/\s+/', trim( $classes ) ), true ) ) {
			return $node;
		}

		// Strip the class, and wrap the <amp-img> in a <figure>.
		$classes = trim( str_replace( $align_class, '', $classes ) );
		$node->setAttribute( 'class', $classes );
		$figure = AMP_DOM_Utils::create_node(
			$this->dom,
			'figure',
			array(
				'class' => $align_class,
				'style' => "max-width: {$width}px;",
			)
		);
		$figure->appendChild( $node );

		return $figure;
	}

	/**
	 * Add an inline style to set the `<figure>` element's width to `auto` instead of `fit-content`.
	 *
	 * @since 1.0
	 * @see https://github.com/Automattic/amp-wp/issues/1086
	 *
	 * @param DOMElement $node The DOMNode to adjust and replace.
	 */
	protected function add_auto_width_to_figure( $node ) {
		$figure = $node->parentNode;
		if ( ! ( $figure instanceof DOMElement ) || 'figure' !== $figure->tagName ) {
			return;
		}

		$class = $figure->getAttribute( 'class' );
		// Target only the <figure> with a 'wp-block-image' class attribute.
		if ( false === strpos( $class, 'wp-block-image' ) ) {
			return;
		}

		// Target only <figure> without a 'is-resized' class attribute.
		if ( false !== strpos( $class, 'is-resized' ) ) {
			return;
		}

		$new_style = 'width: auto;';
		if ( $figure->hasAttribute( 'style' ) ) {
			$figure->setAttribute( 'style', $new_style . $figure->getAttribute( 'style' ) );
		} else {
			$figure->setAttribute( 'style', $new_style );
		}
	}
}
