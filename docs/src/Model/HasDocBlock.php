<?php
/**
 * Trait HasDocBlock.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * A trait for an object that has a doc-block and needs to interact with it.
 */
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

	/**
	 * Check whether the element is marked as being deprecated.
	 *
	 * @return bool Whether the element is marked as deprecated.
	 */
	public function is_deprecated() {
		return $this->doc->has_tag( 'deprecated' );
	}

	/**
	 * Get the reason for why the element is deprecated.
	 *
	 * @return string Deprecation reason.
	 */
	public function get_deprecation_reason() {
		return $this->doc->get_tag( 'deprecated' )->content;
	}

	/**
	 * Check if the element has a return value.
	 *
	 * @return bool Whether the element has a return value.
	 */
	public function has_return_value() {
		return $this->doc->has_tag( 'return' );
	}

	/**
	 * Get the description for the return value.
	 *
	 * @return string Return value description.
	 */
	public function has_return_value_description() {
		return ! empty( trim( $this->get_return_value_description() ) );
	}

	/**
	 * Get the description for the return value.
	 *
	 * @return string Return value description.
	 */
	public function get_return_value_description() {
		return $this->doc->get_tag( 'return' )->content;
	}

	/**
	 * Get the return value type(s).
	 *
	 * @return string Return value type(s).
	 */
	public function get_return_value_types() {
		return implode( '|', $this->doc->get_tag( 'return' )->types );
	}
}
