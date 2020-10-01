<?php
/**
 * Class Root.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

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
	 * Collection of methods across all files.
	 *
	 * @var Method[]
	 */
	private $methods;

	/**
	 * Collection of hooks across all files.
	 *
	 * @var Hook[]
	 */
	private $hooks;

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
			$this->files[] = new File( $file_data, $this );
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
	public function get_classes() {
		if ( null === $this->classes ) {
			$this->classes = [];

			foreach ( $this->get_files() as $file ) {
				foreach ( $file->classes as $class ) {
					$this->classes[] = $class;
				}
			}

			usort(
				$this->classes,
				static function ( Class_ $a, Class_ $b ) {
					return strcmp(
						$a->get_relative_name(),
						$b->get_relative_name()
					);
				}
			);
		}

		return $this->classes;
	}

	/**
	 * Get the functions across all of the files.
	 *
	 * @return Function_[] Collection of function reference objects.
	 */
	public function get_functions() {
		if ( null === $this->functions ) {
			$this->functions = [];

			foreach ( $this->get_files() as $file ) {
				foreach ( $file->functions as $function ) {
					$this->functions[] = $function;
				}
			}

			usort(
				$this->functions,
				static function ( Function_ $a, Function_ $b ) {
					return strcmp(
						$a->get_relative_name(),
						$b->get_relative_name()
					);
				}
			);
		}

		return $this->functions;
	}

	/**
	 * Get the methods across all of the files.
	 *
	 * @return Method[] Collection of method reference objects.
	 */
	public function get_methods() {
		if ( null === $this->methods ) {
			$this->methods = [];

			foreach ( $this->get_classes() as $class ) {
				foreach ( $class->methods as $method ) {
					$this->methods[] = $method;
				}
			}

			usort(
				$this->methods,
				static function ( Method $a, Method $b ) {
					return strcmp(
						$a->get_display_name(),
						$b->get_display_name()
					);
				}
			);
		}

		return $this->methods;
	}

	/**
	 * Get the hooks across all of the files.
	 *
	 * @return Hook[] Collection of hook reference objects.
	 */
	public function get_hooks() {
		if ( null === $this->hooks ) {
			$this->hooks = [];

			foreach ( $this->get_files() as $file ) {
				foreach ( $file->hooks as $hook ) {
					$this->hooks[] = $hook;
				}
			}

			usort(
				$this->hooks,
				static function ( $a, $b ) {
					return strcmp( $a->name, $b->name );
				}
			);
		}

		return $this->hooks;
	}
}
