<?php
/**
 * Class AMP_Layout_Sanitizer
 *
 * @since 1.5.0
 * @package AMP
 */

/**
 * Class AMP_Layout_Sanitizer
 *
 * @since 1.5.0
 */
class AMP_Layout_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Sanitize any element that has the `layout` or `data-amp-layout` attribute.
	 */
	public function sanitize() {
		$xpath = new DOMXPath( $this->dom );
		// Elements with the `layout` attribute will be validated by `AMP_Tag_And_Attribute_Sanitizer`.
		$nodes = $xpath->query( '//*[ not( @layout ) and ( @data-amp-layout or @width or @height or @style ) ]' );

		foreach ( $nodes as $node ) {
			$width  = $node->getAttribute( 'width' );
			$height = $node->getAttribute( 'height' );
			$style  = $node->getAttribute( 'style' );

			// The `layout` attribute can also be defined through the `data-amp-layout` attribute.
			if ( $node->hasAttribute( 'data-amp-layout' ) ) {
				$layout = $node->getAttribute( 'data-amp-layout' );
				$node->setAttribute( 'layout', $layout );
				$node->removeAttribute( 'data-amp-layout' );
			}

			if ( ! $this->attr_empty( $style ) ) {
				$styles = $this->parse_style_string( $style );

				// If both height & width descriptors are 100%, apply fill layout.
				if (
					isset( $styles['width'], $styles['height'] ) &&
					( '100%' === $styles['width'] && '100%' === $styles['height'] )
				) {
					unset( $styles['width'], $styles['height'] );

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
				$node->setAttribute( 'layout', AMP_Rule_Spec::LAYOUT_FIXED_HEIGHT );
			}
		}

		$this->did_convert_elements = true;
	}

	/**
	 * Apply the `fill` layout.
	 *
	 * @param DOMElement $node Node.
	 */
	private function set_layout_fill( $node ) {
		if ( $node->hasAttribute( 'width' ) && $node->hasAttribute( 'height' ) ) {
			$node->removeAttribute( 'width' );
			$node->removeAttribute( 'height' );
		}

		if ( AMP_Rule_Spec::LAYOUT_FILL !== $node->getAttribute( 'layout' ) ) {
			$node->setAttribute( 'layout', AMP_Rule_Spec::LAYOUT_FILL );
		}
	}
}
