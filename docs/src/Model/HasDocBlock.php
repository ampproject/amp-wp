<?php
/**
 * Trait HasDocBlock.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

trait HasDocBlock {

	/**
	 * Doc-block for the element.
	 *
	 * @var DocBlock
	 */
	public $doc;

	/**
	 * Process a doc-block entry.
	 *
	 * @param array $value Associative array of the doc-block data.
	 */
	protected function process_doc( $value ) {
		$this->doc = new DocBlock( $value, $this );
	}

	/**
	 * Check if a description is available.
	 *
	 * @return bool Whether a description is available.
	 */
	public function has_description() {
		if ( empty( trim( $this->doc->description ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the assembled description from the doc-block.
	 *
	 * @return string Assembled description.
	 */
	public function get_description() {
		$description = trim( $this->doc->description );

		if ( ! empty( $this->doc->long_description ) ) {
			$description .= "\n\n";
			$description .= trim( $this->doc->long_description );
		}

		return trim( $description );
	}

	/**
	 * Get the assembled description from the doc-block.
	 *
	 * @return string Assembled description.
	 */
	public function get_short_description() {
		return trim( $this->doc->description );
	}
}
