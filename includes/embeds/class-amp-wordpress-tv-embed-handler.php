<?php
/**
 * Class AMP_WordPress_TV_Embed_Handler
 *
 * @package AMP
 * @since 1.4
 */

/**
 * Class AMP_WordPress_TV_Embed_Handler
 *
 * @since 1.4
 */
class AMP_WordPress_TV_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Base URL used for identifying embeds.
	 *
	 * @var string
	 */
	protected $base_embed_url = 'https://video.wordpress.com/embed/';

	/**
	 * Make embed AMP compatible.
	 *
	 * @param DOMElement $node DOM element.
	 */
	protected function sanitize_raw_embed( DOMElement $node ) {
		$node->setAttribute( 'layout', 'responsive' );

		$this->maybe_remove_script_sibling( $node, 'v0.wordpress.com/js/next/videopress-iframe.js' );
		$this->maybe_unwrap_p_element( $node );
	}
}
