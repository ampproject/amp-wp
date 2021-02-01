<?php
/**
 * Class ExtraThemeAndPluginHeaders.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Registers the 'AMP' extra header for themes and plugins.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
final class ExtraThemeAndPluginHeaders implements Service, Registerable {

	/**
	 * Header name.
	 *
	 * @var string
	 */
	const AMP_HEADER = 'AMP';

	/**
	 * AMP header value indicating legacy template support.
	 *
	 * @var string
	 */
	const AMP_HEADER_LEGACY = 'legacy';

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		// Filter must be added as soon as possible since once wp_get_themes() is called, the results are cached.
		add_filter( 'extra_theme_headers', [ $this, 'filter_extra_headers' ] );
	}

	/**
	 * Add 'AMP' to the list of headers parsed from a theme's style.css or plugin's bootstrap file.
	 *
	 * For prior precedent here, WooCommerce adds a 'Woo' header.
	 *
	 * @see wc_enable_wc_plugin_headers()
	 * @see \WC_Helper::get_local_woo_themes()
	 *
	 * @param string[] $headers Headers.
	 * @return string[] Amended headers.
	 */
	public function filter_extra_headers( $headers ) {
		$headers[] = self::AMP_HEADER;
		return $headers;
	}
}
