<?php
/**
 * Class Argument.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing a function or method argument.
 *
 * @property string $name
 * @property mixed  $default
 * @property string $type
 */
final class Argument implements Leaf {

	use LeafConstruction;

	/**
	 * The docblock tag that is documenting this argument.
	 *
	 * @var Tag
	 */
	private $tag;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return array
	 */
	protected function get_known_keys() {
		return [
			'name'    => '',
			'default' => 0,
			'type'    => '',
		];
	}

	/**
	 * Get the docblock tag for the argument.
	 *
	 * @return Tag Docblock tag that represents the argument.
	 */
	private function get_docblock_tag() {
		if ( null === $this->tag ) {
			if ( ! isset( $this->parent->doc->tags ) ) {
				$this->tag = new Tag( [ 'name' => '<undocumented>' ], $this );
			}

			$tags = $this->parent->doc->tags;

			foreach ( $tags as $tag ) {
				if (
					'param' === $tag->name
					&& isset( $tag->variable )
					&& $tag->variable === $this->name
				) {
					$this->tag = $tag;
					return $this->tag;
				}
			}

			$this->tag = new Tag( [ 'name' => '<undocumented>' ], $this );
		}

		return $this->tag;
	}

	/**
	 * Guess the type of the argument.
	 *
	 * @return string
	 */
	public function guess_type() {
		if ( ! empty( $this->type ) ) {
			return $this->type;
		}

		$tag = $this->get_docblock_tag();

		if ( '<undocumented>' === $tag->name ) {
			return 'mixed';
		}

		return implode( '|', $tag->types );
	}

	/**
	 * Check whether the argument has a description.
	 *
	 * @return bool Whether the argument has a description.
	 */
	public function has_description() {
		$tag = $this->get_docblock_tag();

		return ! empty( trim( $tag->content ) );
	}

	/**
	 * Get the description of the argument.
	 *
	 * @return string Description of the argument.
	 */
	public function get_description() {
		$tag = $this->get_docblock_tag();

		return trim( $tag->content );
	}
}
