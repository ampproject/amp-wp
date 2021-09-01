<?php
/**
 * Add new tab (AMP) in theme install screen in WordPress admin.
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Add new tab (AMP) in theme install screen in WordPress admin.
 *
 * @since 2.2
 * @internal
 */
class ThemeInstallTab implements Conditional, Service, Registerable {

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {

		return ( ! wp_doing_ajax() && is_admin() );
	}

	/**
	 * Adds hooks.
	 *
	 * @return void
	 */
	public function register() {

		add_filter( 'themes_api', [ $this, 'themes_api' ], 10, 3 );
	}

	/**
	 * Filter the response of API call to wordpress.org for theme data.
	 *
	 * @param bool|array $response List of AMP compatible theme.
	 * @param string     $action   API Action.
	 * @param array      $args     Args for plugin list.
	 *
	 * @return array List of AMP compatible plugins.
	 */
	public function themes_api( $response, $action, $args ) {

		return $response;
	}
}
