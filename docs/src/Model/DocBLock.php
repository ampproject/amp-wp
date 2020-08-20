<?php
/**
 * Class DocBlock.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

use RuntimeException;

/**
 * Documentation reference object representing a class.
 *
 * @property string $description
 * @property string $long_description
 * @property Tag[]  $tags
 */
final class DocBlock {

	use LeafConstruction;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return string[]
	 */
	protected function get_known_keys() {
		return [
			'description',
			'long_description',
			'tags',
		];
	}

	/**
	 * Process the tags entry.
	 *
	 * @param array $value Value of the tags entry.
	 */
	private function process_tags( $value ) {
		$this->tags = [];

		foreach ( $value as $tag ) {
			$this->tags[ $tag['name'] ] = new Tag( $tag, $this );
		}
	}

	/**
	 * Check whether the doc-block has a given tag.
	 *
	 * @param string $name Name of the tag to look for.
	 * @return bool Whether the doc-block has the requested tag.
	 */
	public function has_tag( $name ) {
		return array_key_exists( $name, $this->tags );
	}

	/**
	 * Get the content of a given tag.
	 * @param string $name Tag to get the content for.
	 * @return string Content of the requested tag.
	 */
	public function get_tag( $name ) {
		if ( ! array_key_exists( $name, $this->tags ) ) {
			throw new RuntimeException(
				"Trying to get the content of an unknown doc-block tag {$name}"
			);
		}

		return $this->tags[ $name ]->content;
	}
}
