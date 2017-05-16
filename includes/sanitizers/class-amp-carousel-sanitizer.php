<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/utils/class-amp-image-dimension-extractor.php' );

/**
 * Converts <img> tags to <amp-img> or <amp-anim>
 */
class AMP_Carousel_Sanitizer extends AMP_Base_Sanitizer {

	public static $tag = 'amp-carousel';

	private static $script_slug = 'amp-carousel';
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-carousel-0.1.js';

	public function sanitize() {

		$nodes = $this->dom->getElementsByTagName( self::$tag );

		$num_nodes = $nodes->length;
		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( ! $node->hasChildNodes() ) {
				$node->parentNode->removeChild( $node );
			} else {
				$this->did_convert_elements = true;
			}
		}
	}

	public function get_scripts() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}

		return array( self::$script_slug => self::$script_src );
	}
}
