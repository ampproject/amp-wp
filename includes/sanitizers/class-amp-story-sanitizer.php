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

			$cta_layers     = $node->getElementsByTagName( 'amp-story-cta-layer' );
			$num_cta_layers = $cta_layers->length;

			for ( $j = $num_cta_layers - 1; $j >= 0; $j-- ) {
				$cta_layer_node = $cta_layers->item( $j );

				// The first page in a story must not have a CTA layer.
				if ( 0 === $i ) {
					$this->remove_invalid_child( $cta_layer_node );
					continue;
				}

				if ( 0 === $j ) {
					$node->appendChild( $cta_layer_node );
					continue;
				}

				// There can only be one CTA layer.
				$this->remove_invalid_child( $cta_layer_node );
			}
		}
	}
}
