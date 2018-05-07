<?php
/**
 * Class AMP_Script_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Script_Sanitizer
 *
 * Collects inline styles and outputs them in the amp-custom stylesheet.
 */
class AMP_Script_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Known scripts.
	 *
	 * @since 1.0
	 * @var string[]
	 */
	private $known_scripts;

	/**
	 * Sanitize scripts that are commonly used and are available in AMP.
	 *
	 * @since 1.0
	 */
	public function sanitize() {

		$this->known_scripts = $this->get_known_scripts_from_page();
	}

	/**
	 * Check if script is a known, AMP valid script.
	 */
	private function is_known_valid_script( $script ) {
		return true;
	}

	/**
	 * Gather known scripts in page
	 */
	private function get_known_scripts_from_page() {

		$scripts = array();

		foreach ( $this->dom->getElementsByTagName( 'script' ) as $script ) {
			// If known, track it
			if ( $this->is_known_valid_script( $script ) ) {
				array_push( $scripts, $script );
			}
		}

		return $scripts;
	}
}

