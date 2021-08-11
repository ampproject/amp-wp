<?php
/**
 * Class AMP_Script_Sanitizer
 *
 * @since 1.0
 * @package AMP
 */

use AmpProject\Attribute;
use AmpProject\DevMode;
use AmpProject\Tag;
use AmpProject\Dom\Element;

/**
 * Class AMP_Script_Sanitizer
 *
 * @since 1.0
 * @internal
 */
class AMP_Script_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Error code for custom inline JS script tag.
	 *
	 * @var string
	 */
	const CUSTOM_INLINE_SCRIPT = 'CUSTOM_INLINE_SCRIPT';

	/**
	 * Error code for custom external JS script tag.
	 *
	 * @var string
	 */
	const CUSTOM_EXTERNAL_SCRIPT = 'CUSTOM_EXTERNAL_SCRIPT';

	/**
	 * Error code for JS event handler attribute.
	 *
	 * @var string
	 */
	const CUSTOM_EVENT_HANDLER_ATTR = 'CUSTOM_EVENT_HANDLER_ATTR';

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'sanitize_js_scripts' => false,
	];

	/**
	 * Array of flags used to control sanitization.
	 *
	 * @var array {
	 *      @type bool $sanitize_js_scripts Whether to sanitize JS scripts (and not defer for final sanitizer).
	 * }
	 */
	protected $args;

	/**
	 * Sanitize script and noscript elements.
	 *
	 * Eventually this should also handle script elements, if there is a known AMP equivalent.
	 * If nothing is done with script elements, the validating sanitizer will deal with them ultimately.
	 *
	 * @todo Eventually this try to automatically convert script tags to AMP when they are recognized. See <https://github.com/ampproject/amp-wp/issues/1032>.
	 * @todo When a script has an adjacent noscript, consider removing the script here to prevent validation error later. See <https://github.com/ampproject/amp-wp/issues/1213>.
	 *
	 * @since 1.0
	 */
	public function sanitize() {
		$this->unwrap_noscript_elements();

		if ( ! empty( $this->args['sanitize_js_scripts'] ) ) {
			$this->sanitize_js_script_elements();
		}
	}

	/**
	 * Unwrap noscript elements so their contents become the AMP version by default.
	 */
	protected function unwrap_noscript_elements() {
		$noscripts = $this->dom->getElementsByTagName( Tag::NOSCRIPT );

		for ( $i = $noscripts->length - 1; $i >= 0; $i-- ) {
			$noscript = $noscripts->item( $i );

			// Skip AMP boilerplate.
			if ( $noscript->firstChild instanceof Element && $noscript->firstChild->hasAttribute( 'amp-boilerplate' ) ) {
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

	/**
	 * Sanitize JavaScript script elements.
	 *
	 * This runs explicitly in the script sanitizer before the final validating sanitizer (tag-and-attribute) so that
	 * the style sanitizer will be able to know whether there are custom scripts in the page, and thus whether tree
	 * shaking can be performed.
	 */
	protected function sanitize_js_script_elements() {
		$scripts = $this->dom->xpath->query( '//script[ not( @type ) or not( contains( @type, "json" ) ) ]' );

		/** @var Element $script */
		foreach ( $scripts as $script ) {
			if ( DevMode::hasExemptionForNode( $script ) ) {
				continue;
			}

			if ( $script->hasAttribute( Attribute::SRC ) ) {
				// Skip external AMP CDN scripts.
				if ( 0 === strpos( $script->getAttribute( Attribute::SRC ), 'https://cdn.ampproject.org/' ) ) {
					continue;
				}

				$removed = $this->remove_invalid_child(
					$script,
					[ 'code' => self::CUSTOM_EXTERNAL_SCRIPT ]
				);
				if ( ! $removed ) {
					$script->setAttribute( DevMode::DEV_MODE_ATTRIBUTE, '' );
					$this->dom->documentElement->setAttribute( DevMode::DEV_MODE_ATTRIBUTE, '' );
				}
			} else {
				// Skip inline scripts used by AMP.
				if ( $script->hasAttribute( Attribute::AMP_ONERROR ) ) {
					continue;
				}

				$removed = $this->remove_invalid_child(
					$script,
					[ 'code' => self::CUSTOM_INLINE_SCRIPT ]
				);
				if ( ! $removed ) {
					$script->setAttribute( DevMode::DEV_MODE_ATTRIBUTE, '' );
					$this->dom->documentElement->setAttribute( DevMode::DEV_MODE_ATTRIBUTE, '' );
				}
			}
		}

		// @todo Also need to check for inline script attributes.
	}
}
