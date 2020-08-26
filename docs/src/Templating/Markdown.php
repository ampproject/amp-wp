<?php
/**
 * Class Markdown.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Templating;

/**
 * A single markdown file and where it is supposed t be stored.
 */
final class Markdown {

	/**
	 * Filename to store the markdown under.
	 *
	 * This is relative to the documentation root folder.
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Contents of the markdown file.
	 *
	 * @var string
	 */
	private $contents;

	/**
	 * Markdown constructor.
	 *
	 * @param string $filename Filename to store the markdown under.
	 * @param string $contents Contents of the markdown file.
	 */
	public function __construct( $filename, $contents ) {
		$this->filename = $filename;
		$this->contents = $contents;
	}

	/**
	 * Get the filename to store the markdown under.
	 *
	 * @return string
	 */
	public function get_filename() {
		return $this->filename;
	}

	/**
	 * Get the contents of the markdown file.
	 *
	 * @return string
	 */
	public function get_contents() {
		return $this->contents;
	}
}
