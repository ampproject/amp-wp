<?php
/**
 * Class AMP_Video_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\DevMode;

/**
 * Class AMP_Video_Sanitizer
 *
 * @since 0.2
 *
 * Converts <video> tags to <amp-video>
 */
class AMP_Video_Sanitizer extends AMP_Base_Sanitizer {
	use AMP_Noscript_Fallback;

	/**
	 * Tag.
	 *
	 * @var string HTML <video> tag to identify and replace with AMP version.
	 *
	 * @since 0.2
	 */
	public static $tag = 'video';

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'add_noscript_fallback' => true,
	];

	/**
	 * Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
	 *
	 * @return array Mapping.
	 */
	public function get_selector_conversion_mapping() {
		return [
			'video' => [ 'amp-video', 'amp-youtube' ],
		];
	}

	/**
	 * Sanitize the <video> elements from the HTML contained in this instance's Dom\Document.
	 *
	 * @since 0.2
	 * @since 1.0 Set the filtered child node's src attribute.
	 */
	public function sanitize() {
		$nodes     = $this->dom->getElementsByTagName( self::$tag );
		$num_nodes = $nodes->length;
		if ( 0 === $num_nodes ) {
			return;
		}

		if ( $this->args['add_noscript_fallback'] ) {
			$this->initialize_noscript_allowed_attributes( self::$tag );
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			/**
			 * Node.
			 *
			 * @var DOMElement $node
			 */
			$node = $nodes->item( $i );

			// Skip element if already inside of an AMP element as a noscript fallback, or if the element is in dev mode.
			if ( $this->is_inside_amp_noscript( $node ) || DevMode::hasExemptionForNode( $node ) ) {
				continue;
			}

			$amp_data       = $this->get_data_amp_attributes( $node );
			$old_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );
			$old_attributes = $this->filter_data_amp_attributes( $old_attributes, $amp_data );

			$sources        = [];
			$new_attributes = $this->filter_attributes( $old_attributes );
			$layout         = isset( $amp_data['layout'] ) ? $amp_data['layout'] : false;
			if ( isset( $new_attributes['src'] ) ) {
				$new_attributes = $this->filter_video_dimensions( $new_attributes, $new_attributes['src'] );
				if ( $new_attributes['src'] ) {
					$sources[] = $new_attributes['src'];
				}
			}

			/**
			 * Original node.
			 *
			 * @var DOMElement $old_node
			 */
			$old_node = $node->cloneNode( false );

			// Gather all child nodes and supply empty video dimensions from sources.
			$fallback    = null;
			$child_nodes = [];
			while ( $node->firstChild ) {
				$child_node = $node->removeChild( $node->firstChild );
				if ( $child_node instanceof DOMElement && 'source' === $child_node->nodeName && $child_node->hasAttribute( 'src' ) ) {
					$src = $this->maybe_enforce_https_src( $child_node->getAttribute( 'src' ), true );
					if ( ! $src ) {
						// @todo $this->remove_invalid_child( $child_node ), but this will require refactoring the while loop since it uses firstChild.
						continue; // Skip adding source.
					}
					$sources[] = $src;
					$child_node->setAttribute( 'src', $src );
					$new_attributes = $this->filter_video_dimensions( $new_attributes, $src );
				}

				if ( ! $fallback && $child_node instanceof DOMElement && ! ( 'source' === $child_node->nodeName || 'track' === $child_node->nodeName ) ) {
					$fallback = $child_node;
					$fallback->setAttribute( 'fallback', '' );
				}

				$child_nodes[] = $child_node;
			}

			/*
			 * Add fallback for audio shortcode which is not present by default since wp_mediaelement_fallback()
			 * is not called when wp_audio_shortcode_library is filtered from mediaelement to amp.
			 */
			if ( ! $fallback && ! empty( $sources ) ) {
				$fallback = $this->dom->createElement( 'a' );
				$fallback->setAttribute( 'href', $sources[0] );
				$fallback->setAttribute( 'fallback', '' );
				$fallback->appendChild( $this->dom->createTextNode( $sources[0] ) );
				$child_nodes[] = $fallback;
			}

			$new_attributes = $this->filter_attachment_layout_attributes( $node, $new_attributes, $layout );
			if ( empty( $new_attributes['layout'] ) && ! empty( $new_attributes['width'] ) && ! empty( $new_attributes['height'] ) ) {
				$new_attributes['layout'] = 'responsive';
			}
			$new_attributes = $this->set_layout( $new_attributes );

			// @todo Make sure poster and artwork attributes are HTTPS.
			$new_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-video', $new_attributes );
			foreach ( $child_nodes as $child_node ) {
				$new_node->appendChild( $child_node );
				if ( ! ( $child_node instanceof DOMElement ) || ! $child_node->hasAttribute( 'fallback' ) ) {
					$old_node->appendChild( $child_node->cloneNode( true ) );
				}
			}

			// Make sure the updated src and poster are applied to the original.
			foreach ( [ 'src', 'poster', 'artwork' ] as $attr_name ) {
				if ( $new_node->hasAttribute( $attr_name ) ) {
					$old_node->setAttribute( $attr_name, $new_node->getAttribute( $attr_name ) );
				}
			}

			/*
			 * If the node has at least one valid source, replace the old node with it.
			 * Otherwise, just remove the node.
			 *
			 * @todo Add a fallback handler.
			 * See: https://github.com/ampproject/amphtml/issues/2261
			 */
			if ( empty( $sources ) ) {
				$this->remove_invalid_child(
					$node,
					[
						'code'       => AMP_Tag_And_Attribute_Sanitizer::ATTR_REQUIRED_BUT_MISSING,
						'attributes' => [ 'src' ],
						'spec_name'  => 'amp-video',
					]
				);
			} else {
				$node->parentNode->replaceChild( $new_node, $node );

				if ( $this->args['add_noscript_fallback'] ) {
					// Preserve original node in noscript for no-JS environments.
					$this->append_old_node_noscript( $new_node, $old_node, $this->dom );
				}
			}

			$this->did_convert_elements = true;

		}
	}

	/**
	 * Filter video dimensions, try to get width and height from original file if missing.
	 *
	 * The video block will automatically have the width/height supplied for attachments.
	 *
	 * @see \AMP_Core_Block_Handler::ampify_video_block()
	 *
	 * @param array  $new_attributes Attributes.
	 * @param string $src            Video URL.
	 * @return array Modified attributes.
	 */
	protected function filter_video_dimensions( $new_attributes, $src ) {

		// Short-circuit if width and height are already defined.
		if ( ! empty( $new_attributes['width'] ) && ! empty( $new_attributes['height'] ) ) {
			return $new_attributes;
		}

		// Short-circuit if no width and height are required based on the layout.
		$layout = isset( $new_attributes['layout'] ) ? $new_attributes['layout'] : null;
		if ( in_array( $layout, [ 'fill', 'nodisplay', 'flex-item' ], true ) ) {
			return $new_attributes;
		}

		// Get the width and height from the file.
		$path = wp_parse_url( $src, PHP_URL_PATH );
		$ext  = pathinfo( $path, PATHINFO_EXTENSION );
		$name = sanitize_title( wp_basename( $path, ".$ext" ) ); // Extension removed by media_handle_upload().
		$args = [
			'name'        => $name,
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'numberposts' => 1,
		];

		$attachments = get_posts( $args );
		if ( ! empty( $attachments ) ) {
			$attachment = array_shift( $attachments );
			$meta_data  = wp_get_attachment_metadata( $attachment->ID );
			if ( empty( $new_attributes['width'] ) && ! empty( $meta_data['width'] ) && 'fixed-height' !== $layout ) {
				$new_attributes['width'] = $meta_data['width'];
			}
			if ( empty( $new_attributes['height'] ) && ! empty( $meta_data['height'] ) ) {
				$new_attributes['height'] = $meta_data['height'];
			}
		}

		return $new_attributes;
	}

	/**
	 * "Filter" HTML attributes for <amp-audio> elements.
	 *
	 * @since 0.2
	 * @since 1.0 Force HTTPS for the src attribute.
	 *
	 * @param string[] $attributes {
	 *      Attributes.
	 *
	 *      @type string    $src        Video URL - Empty if HTTPS required per $this->args['require_https_src']
	 *      @type int       $width      <video> attribute - Set to numeric value if px or %
	 *      @type int       $height     <video> attribute - Set to numeric value if px or %
	 *      @type string    $poster     <video> attribute - Pass along if found
	 *      @type string    $class      <video> attribute - Pass along if found
	 *      @type bool      $controls   <video> attribute - Convert 'false' to empty string ''
	 *      @type bool      $loop       <video> attribute - Convert 'false' to empty string ''
	 *      @type bool      $muted      <video> attribute - Convert 'false' to empty string ''
	 *      @type bool      $autoplay   <video> attribute - Convert 'false' to empty string ''
	 * }
	 * @return array Returns HTML attributes; removes any not specifically declared above from input.
	 */
	private function filter_attributes( $attributes ) {
		$out = [];

		foreach ( $attributes as $name => $value ) {
			switch ( $name ) {
				case 'src':
					$out[ $name ] = $this->maybe_enforce_https_src( $value, true );
					break;

				case 'width':
				case 'height':
					$out[ $name ] = $this->sanitize_dimension( $value, $name );
					break;

				// @todo Convert to HTTPS when is_ssl().
				case 'poster':
				case 'artwork':
					$out[ $name ] = $value;
					break;

				case 'controls':
				case 'loop':
				case 'muted':
				case 'autoplay':
					if ( 'false' !== $value ) {
						$out[ $name ] = '';
					}
					break;

				case 'data-amp-layout':
					$out['layout'] = $value;
					break;

				case 'data-amp-noloading':
					$out['noloading'] = $value;
					break;

				default:
					$out[ $name ] = $value;
			}
		}

		return $out;
	}
}
