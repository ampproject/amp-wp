<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );

/**
 * Collects amp-custom styles and outputs directly to the output.
 */
class AMP_Custom_Style_Sanitizer extends AMP_Base_Sanitizer {
	private $styles = array();

	public function get_styles() {
		return $this->styles;
	}

	public function sanitize() {
		$this->collect_amp_custom_styles();
	}

	private function collect_amp_custom_styles() {

		$custom_styles = $this->dom->getElementsByTagName('style');
		$num_styles = $custom_styles->length;
		if ( 0 === $num_styles ) {
			return;
		}
		$index = 0;
		error_log("Recording styles...");
		foreach ($custom_styles as $style) {
			if ( $style->hasAttribute('amp-custom') ) {
				$this->styles[$index] = $style->textContent;
				$index += 1;
			}
		}
	}

}
