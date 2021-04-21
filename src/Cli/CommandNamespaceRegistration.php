<?php
/**
 * Class CommandNamespaceRegistration.
 *
 * Registers the AMP command namespace.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Cli;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_CLI;

/**
 * Interacts with the AMP plugin.
 *
 * @since 1.3.0
 * @since 2.1.0 Renamed and refactored into PSR-4 namespace.
 * @internal
 */
final class CommandNamespaceRegistration implements Service, Registerable, Conditional {

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI\Dispatcher\CommandNamespace' );
	}

	/**
	 * Register the service.
	 *
	 * @return void
	 */
	public function register() {
		WP_CLI::add_command( 'amp', AmpCommandNamespace::class );
	}
}
