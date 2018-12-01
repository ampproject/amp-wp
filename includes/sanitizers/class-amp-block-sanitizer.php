<?php
/**
 * Class AMP_Block_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Block_Sanitizer
 *
 * Modifies elements created as blocks to match the blocks' AMP-specific configuration.
 */
class AMP_Block_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @var string Figure tag to identify wrapper around AMP elements.
	 * @since 1.0
	 */
	public static $tag = 'figure';

	/**
	 * Sanitize the AMP elements contained by <figure> element where necessary.
	 *
	 * @since 0.2
	 */
	public function sanitize() {
		$nodes     = $this->dom->getElementsByTagName( self::$tag );
		$num_nodes = $nodes->length;
		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );

			// We are only looking for <figure> elements which have wp-block-embed as class.
			$class = (string) $node->getAttribute( 'class' );
			if ( false === strpos( $class, 'wp-block-embed' ) ) {
				continue;
			}

			// Remove classes like wp-embed-aspect-16-9 since responsive layout is handled by AMP's layout system.
			$node->setAttribute( 'class', preg_replace( '/(?<=^|\s)wp-embed-aspect-\d+-\d+(?=\s|$)/', '', $class ) );

			// We're looking for <figure> elements that have one child node only.
			if ( 1 !== count( $node->childNodes ) ) {
				continue;
			}

			$attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );

			// We are looking for <figure> elements with layout attribute only.
			if (
				! isset( $attributes['data-amp-layout'] ) &&
				! isset( $attributes['data-amp-noloading'] ) &&
				! isset( $attributes['data-amp-lightbox'] )
			) {
				continue;
			}

			$amp_el_found = false;

			foreach ( $node->childNodes as $child_node ) {

				// We are looking for child elements which start with 'amp-'.
				if ( 0 !== strpos( $child_node->tagName, 'amp-' ) ) {
					continue;
				}
				$amp_el_found = true;

				$this->set_attributes( $child_node, $node, $attributes );
			}

			if ( false === $amp_el_found ) {
				continue;
			}
			$this->did_convert_elements = true;
		}
	}

	/**
	 * Sets necessary attributes to both parent and AMP element node.
	 *
	 * @param DOMNode $node AMP element node.
	 * @param DOMNode $parent_node <figure> node.
	 * @param array   $attributes Current attributes of the AMP element.
	 */
	protected function set_attributes( $node, $parent_node, $attributes ) {

		if ( isset( $attributes['data-amp-layout'] ) ) {
			$node->setAttribute( 'layout', $attributes['data-amp-layout'] );
		}
		if ( isset( $attributes['data-amp-noloading'] ) && true === filter_var( $attributes['data-amp-noloading'], FILTER_VALIDATE_BOOLEAN ) ) {
			$node->setAttribute( 'noloading', '' );
		}

		$layout = $node->getAttribute( 'layout' );

		// The width has to be unset / auto in case of fixed-height.
		if ( 'fixed-height' === $layout ) {
			if ( ! isset( $attributes['height'] ) ) {
				$node->setAttribute( 'height', self::FALLBACK_HEIGHT );
			}
			$node->setAttribute( 'width', 'auto' );

			$height = $node->getAttribute( 'height' );
			if ( is_numeric( $height ) ) {
				$height .= 'px';
			}
			$parent_node->setAttribute( 'style', "height: $height; width: auto;" );

			// The parent element should have width/height set and position set in case of 'fill'.
		} elseif ( 'fill' === $layout ) {
			if ( ! isset( $attributes['height'] ) ) {
				$attributes['height'] = self::FALLBACK_HEIGHT;
			}
			$parent_node->setAttribute( 'style', 'position:relative; width: 100%; height: ' . $attributes['height'] . 'px;' );
			$node->removeAttribute( 'width' );
			$node->removeAttribute( 'height' );
		} elseif ( 'responsive' === $layout ) {
			$parent_node->setAttribute( 'style', 'position:relative; width: 100%; height: auto' );
		} elseif ( 'fixed' === $layout ) {
			if ( ! isset( $attributes['height'] ) ) {
				$node->setAttribute( 'height', self::FALLBACK_HEIGHT );
			}
		}

		// Set the fallback layout in case needed.
		$attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );
		$attributes = $this->set_layout( $attributes );
		if ( $layout !== $attributes['layout'] ) {
			$node->setAttribute( 'layout', $attributes['layout'] );
		}
	}
}
