<?php
/**
 * Class Function_.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing a function.
 *
 * @property string     $name
 * @property string     $namespace
 * @property string[]   $aliases
 * @property int        $line
 * @property int        $end_line
 * @property Argument[] $arguments
 * @property DocBlock   $doc
 * @property Hook[]     $hooks
 * @property Usage[]    $uses
 *
 * phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 */
final class Function_ implements Leaf {

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
			'name'      => '',
			'namespace' => '',
			'aliases'   => [],
			'line'      => 0,
			'end_line'  => 0,
			'arguments' => [],
			'doc'       => new DocBlock( [] ),
			'hooks'     => [],
			'uses'      => [],
		];
	}

	/**
	 * Process a doc-block entry.
	 *
	 * @param array $value Associative array of the doc-block.
	 */
	private function process_doc( $value ) {
		$this->doc = new DocBlock( $value, $this );
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
	 * Get the fully qualified function name of the function.
	 *
	 * @return string Fully qualified function name.
	 */
	public function get_fully_qualified_name() {
		if ( empty( $this->namespace ) || 'global' === $this->namespace ) {
			return $this->name;
		}

		return "{$this->namespace}\\{$this->name}";
	}

	/**
	 * Get the name of the function relative to the root package namespace.
	 *
	 * @return string Relative name of the function.
	 */
	public function get_relative_name() {
		return str_replace(
			'AmpProject\\AmpWP\\',
			'',
			$this->get_fully_qualified_name()
		);
	}

	/**
	 * Get the filename to use for the function.
	 *
	 * @return string Filename to use.
	 */
	public function get_filename() {
		return str_replace( '\\', '/', $this->get_relative_name() );
	}

	/**
	 * Get the signature of the function.
	 *
	 * @return string Function signature.
	 */
	public function get_signature() {
		return sprintf(
			'function %s(%s);',
			$this->name,
			count( $this->arguments ) > 0
				? ' ' . implode( ', ', $this->get_argument_names() ) . ' '
				: ''
		);
	}
}
