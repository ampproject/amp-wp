<?php
/**
 * Trait Description
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
	protected $doc;

	/**
	 * Process a doc-block entry.
	 *
	 * @param array $value Associative array of the doc-block data.
	 */
	protected function process_doc( $value ) {
		$this->doc = new DocBlock( $value, $this );
	}

	/**
	 * Get the assembled description from the doc-block.
	 *
	 * @return string Assembled description.
	 */
	protected function get_description() {
		if ( empty( $this->doc->description ) ) {
			return '';
		}

		$description = $this->doc->description;

		if ( ! empty( $this->doc->long_description ) ) {
			$description .= "\n{$this->doc->long_description}";
		}

		return $description;
	}
}
