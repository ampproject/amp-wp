<?php
/**
 * Class Method.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing a method.
 *
 * @property string     $name
 * @property string     $namespace
 * @property string[]   $aliases
 * @property int        $line
 * @property int        $end_line
 * @property bool       $final
 * @property bool       $abstract
 * @property bool       $static
 * @property string     $visibility
 * @property Argument[] $arguments
 * @property DocBlock   $doc
 * @property Usage[]    $uses
 * @property Hook[]     $hooks
 */
final class Method implements Leaf {

	use LeafConstruction;
	use HasDocBlock;
	use HasArguments;
	use HasCodeLinks;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return array
	 */
	protected function get_known_keys() {
		return [
			'name'       => '',
			'namespace'  => '',
			'aliases'    => [],
			'line'       => 0,
			'end_line'   => 0,
			'final'      => false,
			'abstract'   => false,
			'static'     => false,
			'visibility' => '',
			'arguments'  => [],
			'doc'        => new DocBlock( [] ),
			'uses'       => [],
			'hooks'      => [],
		];
	}

	/**
	 * Process the uses entry.
	 *
	 * @param array $value Array of usage entries.
	 */
	private function process_uses( $value ) {
		$this->uses = [];

		foreach ( $value as $use ) {
			$this->uses[] = new Usage( $use, $this );
		}
	}

	/**
	 * Process the hooks entry.
	 *
	 * @param array $value Array of hook entries.
	 */
	private function process_hooks( $value ) {
		$this->hooks = [];

		foreach ( $value as $hook ) {
			$this->hooks[] = new Hook( $hook, $this );
		}
	}

	/**
	 * Get the signature of the method.
	 *
	 * @return string Method signature.
	 */
	public function get_signature() {
		return sprintf(
			'%s%s%s%s function %s(%s);',
			$this->final ? 'final ' : '',
			$this->abstract ? 'abstract ' : '',
			$this->static ? 'static ' : '',
			$this->visibility ?: 'public',
			$this->name,
			count( $this->arguments ) > 0
				? ' ' . implode( ', ', $this->get_argument_names() ) . ' '
				: ''
		);
	}

	/**
	 * Get the display name of the method.
	 *
	 * @return string Display name of the method.
	 */
	public function get_display_name() {
		return "{$this->parent->name}::{$this->name}()";
	}

	/**
	 * Get the filename to use for the method.
	 *
	 * @return string Filename to use.
	 */
	public function get_filename() {
		$relative_class_name = str_replace(
			'AmpProject\\AmpWP\\',
			'',
			$this->parent->get_fully_qualified_name()
		);

		$class_path = str_replace( '\\', '/', $relative_class_name );

		return "{$class_path}/{$this->name}";
	}
}
