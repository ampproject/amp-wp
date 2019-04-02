<?php
/**
 * Class AMP_Script_Sanitizer
 *
 * @since 1.0
 * @package AMP
 */

/**
 * Class AMP_Script_Sanitizer
 *
 * @since 1.0
 */
class AMP_Script_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Sanitize noscript elements.
	 *
	 * Eventually this should also handle script elements, if there is a known AMP equivalent.
	 * If nothing is done with script elements, the whitelist sanitizer will deal with them ultimately.
	 *
	 * @todo Eventually this try to automatically convert script tags to AMP when they are recognized. See <https://github.com/ampproject/amp-wp/issues/1032>.
	 * @todo When a script has an adjacent noscript, consider removing the script here to prevent validation error later. See <https://github.com/ampproject/amp-wp/issues/1213>.
	 *
	 * @since 1.0
	 */
	public function sanitize() {
		$noscripts = $this->dom->getElementsByTagName( 'noscript' );

		for ( $i = $noscripts->length - 1; $i >= 0; $i-- ) {
			$noscript = $noscripts->item( $i );

			// Skip AMP boilerplate.
			if ( $noscript->firstChild instanceof DOMElement && $noscript->firstChild->hasAttribute( 'amp-boilerplate' ) ) {
				continue;
			}

			// Skip noscript elements inside of amp-img or other AMP components for fallbacks. See \AMP_Img_Sanitizer::adjust_and_replace_node().
			if ( 'amp-' === substr( $noscript->parentNode->nodeName, 0, 4 ) ) {
				continue;
			}

			$fragment = $this->dom->createDocumentFragment();
			$fragment->appendChild( $this->dom->createComment( 'noscript' ) );
			while ( $noscript->firstChild ) {
				$fragment->appendChild( $noscript->firstChild );
			}
			$fragment->appendChild( $this->dom->createComment( '/noscript' ) );
			$noscript->parentNode->replaceChild( $fragment, $noscript );

			$this->did_convert_elements = true;
		}
	}
}
