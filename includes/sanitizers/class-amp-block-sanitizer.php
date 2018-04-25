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
	 * AMP elements.
	 *
	 * @todo Add all elements.
	 * @var array AMP elements to add layout to.
	 */
	public static $amp_embeds = array(
		'amp-youtube',
		'amp-facebook',
		'amp-twitter',
		'amp-vimeo',
		'amp-instagram',
		'amp-dailymotion',
		'amp-hulu',
		'amp-reddit',
		'amp-soundcloud',
	);

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

			// We're looking for <figure> elements that have one child node only.
			if ( 1 !== count( $node->childNodes ) ) {
				continue;
			}

			$attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );

			// We are looking for <figure> elements with layout attribute only.
			if ( ! isset( $attributes['data-amp-layout'] ) ) {
				continue;
			}

			$layout       = $attributes['data-amp-layout'];
			$amp_el_found = false;

			foreach ( $node->childNodes as $child_node ) {
				if ( ! in_array( $child_node->tagName, self::$amp_embeds, true ) ) {
					continue;
				}
				$amp_el_found = true;
				$child_node->setAttribute( 'layout', $layout );

				$this->set_attributes( $child_node, $node, $layout, $attributes );
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
	 * @param string  $layout Layout.
	 * @param array   $attributes Current attributes of the AMP element.
	 */
	protected function set_attributes( $node, $parent_node, $layout, $attributes ) {

		// The width has to be unset / auto in case of fixed-height.
		if ( 'fixed-height' === $layout ) {
			if ( ! isset( $attributes['height'] ) ) {
				$node->setAttribute( 'height', self::FALLBACK_HEIGHT );
			}
			$node->setAttribute( 'width', 'auto' );

			// @todo Perhaps the height is not in px, is it possible?
			$parent_node->setAttribute( 'style', 'height: ' . $node->getAttribute( 'height' ) . 'px; width: auto;' );

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
