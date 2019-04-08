<?php
/**
 * Class AMP_Story_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Story_Sanitizer
 *
 * Sanitizes pages within AMP Stories.
 */
class AMP_Story_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @var string Figure tag to identify wrapper around AMP elements.
	 * @since 1.0
	 */
	public static $tag = 'amp-story-page';

	/**
	 * Sanitize the AMP elements contained by <amp-story-page> element where necessary.
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

			if ( ! $node ) {
				continue;
			}

			$cta_layers     = $node->getElementsByTagName( 'amp-story-cta-layer' );
			$num_cta_layers = $cta_layers->length;

			/**
			 * Sanitizes usage of Call-to-Action layers.
			 *
			 * Does not use the remove_invalid_child() method
			 * since the withCallToActionValidation HOC in the editor
			 * already warns the user about improper usage.
			 */
			for ( $j = $num_cta_layers - 1; $j >= 0; $j-- ) {
				$cta_layer_node = $cta_layers->item( $j );

				// The first page in a story must not have a CTA layer.
				if ( 0 === $i ) {
					$cta_layer_node->parentNode->removeChild( $cta_layer_node );
					continue;
				}

				if ( $j > 0 ) {
					// There can only be one CTA layer.
					$cta_layer_node->parentNode->removeChild( $cta_layer_node );
				}
			}
		}
	}
}
