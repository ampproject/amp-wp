<?php
/**
 * Class WP_Filesystem_Mock.
 *
 * @package Amp\AmpWP\Tests
 */

namespace Amp\AmpWP\Tests;

/**
 * Class WP_Filesystem_Mock
 *
 * @todo Inherit methods from `WP_FileSystem_Base` class. WordPress files are not autoloaded when this class is so an error occurs.
 *
 * @internal
 * @since 1.5.0
 */
class WP_Filesystem_Mock {

	/**
	 * Value to return for a function call.
	 *
	 * @var mixed
	 */
	private $returns;

	/**
	 * Value to be returned for called functions.
	 *
	 * @param mixed $value Value.
	 */
	public function set_returns( $value ) {
		$this->returns = $value;
	}

	/**
	 * Moves a file.
	 *
	 * @since 2.5.0
	 * @abstract
	 *
	 * @param string $source      Path to the source file.
	 * @param string $destination Path to the destination file.
	 * @param bool   $overwrite   Optional. Whether to overwrite the destination file if it exists.
	 *                            Default false.
	 * @return bool True on success, false on failure.
	 */
	public function move( $source, $destination, $overwrite = false ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return $this->returns;
	}
}
