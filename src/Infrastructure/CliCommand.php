<?php
/**
 * Interface CliCommand.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure;

/**
 * A CLI command to be registered with WP-CLI.
 *
 * A class marked as being a CLI command will automatically be registered
 * as a command with WP-CLI.
 *
 * @since 2.1.0
 * @internal
 */
interface CliCommand {

	/**
	 * Get the name under which to register the CLI command.
	 *
	 * @return string The name under which to register the CLI command.
	 */
	public static function get_command_name();
}
