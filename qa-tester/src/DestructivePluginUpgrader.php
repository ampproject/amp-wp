<?php
/**
 * Class DestructivePluginUpgrader.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester;

use Plugin_Upgrader;

/**
 * Extension of the `Plugin_Upgrader` class with modifications that allows the package being installed to overwrite
 * the currently installed version without any prompt or confirmation.
 *
 * @since 1.0.0
 */
class DestructivePluginUpgrader extends Plugin_Upgrader {

	/**
	 * Install a package, while ensuring that the destination is always overwritten.
	 *
	 * @param array|string $args {
	 *     Optional. Array or string of arguments for installing a package. Default empty array.
	 *
	 *     @type string $source                      Required path to the package source. Default empty.
	 *     @type string $destination                 Required path to a folder to install the package in.
	 *                                               Default empty.
	 *     @type bool   $clear_destination           Whether to delete any files already in the destination
	 *                                               folder. Default false.
	 *     @type bool   $clear_working               Whether to delete the files form the working directory
	 *                                               after copying to the destination. Default false.
	 *     @type bool   $abort_if_destination_exists Whether to abort the installation if
	 *                                               the destination folder already exists. Default true.
	 *     @type array  $hook_extra                  Extra arguments to pass to the filter hooks called by
	 *                                               WP_Upgrader::install_package(). Default empty array.
	 * }
	 *
	 * @return array|\WP_Error The result (also stored in `WP_Upgrader::$result`), or a WP_Error on failure.
	 */
	public function install_package( $args = [] ) {
		$args['clear_destination']           = true;
		$args['abort_if_destination_exists'] = false;
		return parent::install_package( $args );
	}
}
