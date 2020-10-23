<?php
/**
 * Class AMP_Layout_Sanitizer
 *
 * @since 1.5.0
 * @package AMP
 */

use AmpProject\Layout;

/**
 * Class AMP_Layout_Sanitizer
 *
 * @since 1.5.0
 * @internal
 */
class AMP_Layout_Sanitizer extends AMP_Base_Sanitizer {
	use AMP_Noscript_Fallback;

	/**
	 * Sanitize any element that has the `layout` or `data-amp-layout` attribute.
	 */
	public function sanitize() {
		$xpath = new DOMXPath( $this->dom );

		/**
		 * Sanitize AMP nodes to be AMP compatible. Elements with the `layout` attribute will be validated by
		 * `AMP_Tag_And_Attribute_Sanitizer`.
		 */
		$nodes = $xpath->query( '//*[ starts-with( name(), "amp-" ) and not( @layout ) and ( @data-amp-layout or @width or @height or @style ) ]' );

		foreach ( $nodes as $node ) {
			/**
			 * Element.
			 *
			 * @var DOMElement $node
			 */

			// Layout does not apply inside of noscript.
			if ( $this->is_inside_amp_noscript( $node ) ) {
				continue;
			}

			$width  = $node->getAttribute( 'width' );
			$height = $node->getAttribute( 'height' );
			$style  = $node->getAttribute( 'style' );

			// The `layout` attribute can also be defined through the `data-amp-layout` attribute.
			if ( $node->hasAttribute( 'data-amp-layout' ) ) {
				$layout = $node->getAttribute( 'data-amp-layout' );
				$node->setAttribute( 'layout', $layout );
				$node->removeAttribute( 'data-amp-layout' );
			}

			if ( ! $this->is_empty_attribute_value( $style ) ) {
				$styles = $this->parse_style_string( $style );

				/*
				 * If both height & width descriptors are 100%, or
				 *    width attribute is 100% and height style descriptor is 100%, or
				 *    height attribute is 100% and width style descriptor is 100%
				 * then apply fill layout.
				 */
				if (
					(
						isset( $styles['width'], $styles['height'] ) &&
						( '100%' === $styles['width'] && '100%' === $styles['height'] )
					) ||
					(
						( ! $this->is_empty_attribute_value( $width ) && '100%' === $width ) &&
						( isset( $styles['height'] ) && '100%' === $styles['height'] )
					) ||
					(
						( ! $this->is_empty_attribute_value( $height ) && '100%' === $height ) &&
						( isset( $styles['width'] ) && '100%' === $styles['width'] )
					)
				) {
					unset( $styles['width'], $styles['height'] );
					$node->removeAttribute( 'width' );
					$node->removeAttribute( 'height' );

					if ( empty( $styles ) ) {
						$node->removeAttribute( 'style' );
					} else {
						$node->setAttribute( 'style', $this->reassemble_style_string( $styles ) );
					}

					$this->set_layout_fill( $node );
					continue;
				}
			}

			// If the width & height are `100%` then apply fill layout.
			if ( '100%' === $width && '100%' === $height ) {
				$this->set_layout_fill( $node );
				continue;
			}

			// If the width is `100%`, convert the layout to `fixed-height` and width to `auto`.
			if ( '100%' === $width ) {
				$node->setAttribute( 'width', 'auto' );
				$node->setAttribute( 'layout', Layout::FIXED_HEIGHT );
			}
		}

		$this->did_convert_elements = true;
	}

	/**
	 * Apply the `fill` layout.
	 *
	 * @param DOMElement $node Node.
	 */
	private function set_layout_fill( DOMElement $node ) {
		if ( $node->hasAttribute( 'width' ) && $node->hasAttribute( 'height' ) ) {
			$node->removeAttribute( 'width' );
			$node->removeAttribute( 'height' );
		}

		if ( Layout::FILL !== $node->getAttribute( 'layout' ) ) {
			$node->setAttribute( 'layout', Layout::FILL );
		}
	}
}
