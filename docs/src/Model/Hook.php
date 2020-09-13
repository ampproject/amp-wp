<?php
/**
 * Class Hook.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing an action or filter.
 *
 * @property string     $name
 * @property int        $line
 * @property int        $end_line
 * @property string     $type
 * @property Argument[] $arguments
 * @property DocBlock   $doc
 */
final class Hook implements Leaf {

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
			'line'      => 0,
			'end_line'  => 0,
			'type'      => '',
			'doc'       => new DocBlock( [] ),
			'arguments' => [],
		];
	}

	/**
	 * Get the filename to use for the hook.
	 *
	 * @return string Filename to use.
	 */
	public function get_filename() {
		return $this->name;
	}

	/**
	 * Get the type of the hook with the first letter in uppercase.
	 *
	 * @return string Capitalized hook type.
	 */
	public function get_capitalized_type() {
		return ucfirst( $this->type );
	}

	/**
	 * Check whether the hook is an action.
	 *
	 * @return bool Whether the hook is an action.
	 */
	public function is_action() {
		return 'action' === $this->type;
	}

	/**
	 * Check whether the hook is a filter.
	 *
	 * @return bool Whether the hook is a filter.
	 */
	public function is_filter() {
		return 'filter' === $this->type;
	}

	/**
	 * Get the signature of the method.
	 *
	 * @return string Method signature.
	 */
	public function get_signature() {
		return sprintf(
			'%s( \'%s\'%s );',
			'action' === $this->type ? 'do_action' : 'apply_filters',
			$this->name,
			count( $this->arguments ) > 0
				? ', ' . implode( ', ', $this->get_argument_names() )
				: ''
		);
	}

	/**
	 * Process the type entry.
	 *
	 * @param string $value Value of the type entry.
	 */
	private function process_type( $value ) {
		switch ( $value ) {
			case 'filter_deprecated':
			case 'filter_reference':
				$this->type = 'filter';
				break;
			case 'action_deprecated':
			case 'action_reference':
				$this->type = 'action';
				break;
			default:
				$this->type = $value;
		}
	}

	/**
	 * Process the arguments entry.
	 */
	private function process_arguments() {
		if ( empty( $this->doc->tags ) ) {
			$this->arguments = [];
			return;
		}

		foreach ( $this->doc->tags as $tag ) {
			if ( 'param' !== $tag->name || ! isset( $tag->variable ) ) {
				continue;
			}

			$this->arguments[] = new Argument( [ 'name' => $tag->variable ], $this );
		}
	}
}
