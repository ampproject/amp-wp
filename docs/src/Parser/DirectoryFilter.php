<?php
/**
 * Class DirectoryFilter.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Parser;

use RecursiveFilterIterator;
use RecursiveIterator;

/**
 * Filter class to filter a recursive directory iterator.
 */
final class DirectoryFilter extends RecursiveFilterIterator {

	/**
	 * @var string[]
	 */
	private $excluded_dirs;

	/**
	 * Create a RecursiveFilterIterator from a RecursiveIterator.
	 *
	 * @link https://php.net/manual/en/recursivefilteriterator.construct.php
	 * @param RecursiveIterator $iterator      Iterator to filter.
	 * @param string[]          $excluded_dirs Directories to exclude.
	 */
	public function __construct( RecursiveIterator $iterator, $excluded_dirs ) {
		parent::__construct( $iterator );
		$this->excluded_dirs = $excluded_dirs;
	}

	/**
	 * Return the inner iterator's children contained in a
	 * RecursiveFilterIterator.
	 *
	 * @link https://php.net/manual/en/recursivefilteriterator.getchildren.php
	 * @return RecursiveFilterIterator containing the inner iterator's
	 *                                 children.
	 */
	public function getChildren() {
		return new self(
			$this->getInnerIterator()->getChildren(),
			$this->excluded_dirs
		);
	}

	/**
	 * Check whether the current element of the iterator is acceptable.
	 *
	 * @link https://php.net/manual/en/filteriterator.accept.php
	 * @return bool true if the current element is acceptable, otherwise false.
	 */
	public function accept() {
		$directory = $this->getInnerIterator()->current();

		if ( ! $directory->isDir() ) {
			return true;
		}

		foreach ( $this->excluded_dirs as $excluded_dir ) {
			if ( preg_match( $excluded_dir, $directory->getPathname() ) ) {
				return false;
			}
		}

		return true;
	}
}
