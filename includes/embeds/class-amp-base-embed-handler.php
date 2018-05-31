<?php
/**
 * Class AMP_Base_Embed_Handler
 *
 * Used by some children.
 *
 * @package  AMP
 */

/**
 * Class AMP_Base_Embed_Handler
 */
abstract class AMP_Base_Embed_Handler {
	protected $DEFAULT_WIDTH = 600;
	protected $DEFAULT_HEIGHT = 480;

	protected $args = array();
	protected $did_convert_elements = false;

	abstract function register_embed();
	abstract function unregister_embed();

	function __construct( $args = array() ) {
		$this->args = wp_parse_args( $args, array(
			'width' => $this->DEFAULT_WIDTH,
			'height' => $this->DEFAULT_HEIGHT,
		) );
	}

	/**
	 * Get mapping of AMP component names to AMP script URLs.
	 *
	 * This is normally no longer needed because the whitelist
	 * sanitizer will automatically detect the need for them via
	 * the spec.
	 *
	 * @see AMP_Tag_And_Attribute_Sanitizer::get_scripts()
	 * @return array Scripts.
	 */
	public function get_scripts() {
		return array();
	}
}
