<?php
/**
 * Class Root.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

use Generator;

/**
 * Documentation reference object representing the root of the parsed object
 * tree.
 */
final class Root {

	/**
	 * Collection of classes across all files.
	 *
	 * @var Class_[]
	 */
	private $classes;

	/**
	 * Collection of functions across all files.
	 *
	 * @var Function_[]
	 */
	private $functions;

	/**
	 * Collection of files that represent the source code to be documented.
	 *
	 * @var File[]
	 */
	private $files = [];

	/**
	 * Root constructor.
	 *
	 * @param array $data Associative array of data that comes from the parser.
	 */
	public function __construct( $data ) {
		foreach ( $data as $file_data ) {
			$this->files[ $file_data['path'] ] = new File( $file_data, $this );
		}
	}

	/**
	 * Get the collection of files that were parsed.
	 *
	 * @return File[] Collection of files that were parsed.
	 */
	public function get_files() {
		return $this->files;
	}

	/**
	 * Get the classes across all of the files.
	 *
	 * @return Class_[] Collection of class reference objects.
	 */
	public function get_classes( ) {
		if ( null === $this->classes ) {
			$this->classes = [];

			foreach ( $this->get_files() as $file ) {
				foreach ( $file->classes as $class ) {
					$this->classes[] = $class;
				}
			}
		}

		return $this->classes;
	}

	/**
	 * Get the functions across all of the files.
	 *
	 * @return Function_[] Collection of function reference objects.
	 */
	public function get_functions( ) {
		if ( null === $this->functions ) {
			$this->functions = [];

			foreach ( $this->get_files() as $file ) {
				foreach ( $file->functions as $function ) {
					$this->functions[] = $function;
				}
			}
		}

		return $this->functions;
	}
}
