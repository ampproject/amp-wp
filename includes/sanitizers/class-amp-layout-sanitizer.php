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
		$nodes = $xpath->query( '//*[ @layout or @data-amp-layout ]' );

		foreach ( $nodes as $node ) {
			$width  = $node->getAttribute( 'width' );
			$height = $node->getAttribute( 'height' );

			// The `layout` attribute can also be defined through the `data-amp-layout` attribute.
			if ( $node->hasAttribute( 'data-amp-layout' ) ) {
				$layout = $node->getAttribute( 'data-amp-layout' );
				$node->setAttribute( 'layout', $layout );
				$node->removeAttribute( 'data-amp-layout' );
			}

			// If the width & height are `100%` the layout must be `fill`.
			if ( '100%' === $width && '100%' === $height ) {
				$node->removeAttribute( 'width' );
				$node->removeAttribute( 'height' );
				$node->setAttribute( 'layout', AMP_Rule_Spec::LAYOUT_FILL );
				return;
			}

			// If the width is `100%`, convert the layout to `fixed-height` and width to `auto`.
			if ( '100%' === $width ) {
				$node->setAttribute( 'width', 'auto' );
				$node->setAttribute( 'layout', AMP_Rule_Spec::LAYOUT_FIXED_HEIGHT );
			}
		}

		$this->did_convert_elements = true;
	}
}
