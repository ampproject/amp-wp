<?php
/**
 * Class for handling `amp-iframe` embeds.
 *
 * @package AMP
 * @since 2.4.1
 */

use AmpProject\Html\Tag;
use AmpProject\Extension;
use AmpProject\Dom\Document;

/**
 * Class AMP_Iframe_Embed_Handler
 *
 * @since 2.4.1
 */
class AMP_Iframe_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Register embed.
	 */
	public function register_embed() {
		// Not implemented.
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		// Not implemented.
	}

	/**
	 * Sanitize `amp-iframe` raw embeds.
	 *
	 * @param Document $dom Document.
	 *
	 * @return void
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		// If there were any previous embeds in the DOM that were wrapped by `wpautop()`, unwrap them.
		foreach ( [ Extension::IFRAME, Tag::IFRAME ] as $tag_name ) {
			$this->unwrap_p_element_by_child_tag_name( $dom, $tag_name );
		}
	}
}
