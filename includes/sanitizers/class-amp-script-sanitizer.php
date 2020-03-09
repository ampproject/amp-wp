<?php
/**
 * Class AMP_Script_Sanitizer
 *
 * @since 1.0
 * @package AMP
 */

use AmpProject\DevMode;

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

			/*
			 * Skip noscript elements inside of amp-img or other AMP components for fallbacks.
			 * See \AMP_Img_Sanitizer::adjust_and_replace_node(). Also skip if the element has dev mode.
			 */
			if ( 'amp-' === substr( $noscript->parentNode->nodeName, 0, 4 ) || DevMode::hasExemptionForNode( $noscript ) ) {
				continue;
			}

			$is_inside_head_el = ( $noscript->parentNode && 'head' === $noscript->parentNode->nodeName );
			$must_move_to_body = false;

			$fragment = $this->dom->createDocumentFragment();
			$fragment->appendChild( $this->dom->createComment( 'noscript' ) );
			while ( $noscript->firstChild ) {
				if ( $is_inside_head_el && ! $must_move_to_body ) {
					$must_move_to_body = ! $this->dom->isValidHeadNode( $noscript->firstChild );
				}
				$fragment->appendChild( $noscript->firstChild );
			}
			$fragment->appendChild( $this->dom->createComment( '/noscript' ) );

			if ( $must_move_to_body ) {
				$this->dom->body->insertBefore( $fragment, $this->dom->body->firstChild );
				$noscript->parentNode->removeChild( $noscript );
			} else {
				$noscript->parentNode->replaceChild( $fragment, $noscript );
			}

			$this->did_convert_elements = true;
		}
	}
}
