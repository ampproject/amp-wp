<?php
/**
 * Class AMP_Auto_Lightbox_Disable_Sanitizer
 *
 * @package AmpProject\AmpWP
 */

/**
 * Disable auto lightbox for images.
 *
 * @since 2.2.2
 * @internal
 */
class AMP_Auto_Lightbox_Disable_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Add "data-amp-auto-lightbox-disable" attribute to body tag.
	 *
	 * @return void
	 */
	public function sanitize() {
		$this->dom->html->setAttributeNode( $this->dom->createAttribute( 'data-amp-auto-lightbox-disable' ) );
	}
}
