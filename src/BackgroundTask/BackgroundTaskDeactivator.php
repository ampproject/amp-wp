<?php
/**
 * BackgroundTaskDeactivator service for clearing background tasks on plugin deactivation.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

use AmpProject\AmpWP\Icon;
use AmpProject\AmpWP\Infrastructure\Deactivateable;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Clears background tasks when the plugin is deactivated.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 */
final class BackgroundTaskDeactivator implements Service, Registerable, Deactivateable {

	/**
	 * List of event names to deactivate.
	 *
	 * @var string[]
	 */
	private $events_to_deactivate = [];

	/**
	 * Name of the plugin as WordPress is expecting it.
	 *
	 * This should usually have the form "amp/amp.php".
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->plugin_file = plugin_basename( AMP__FILE__ );
	}

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		add_action( "network_admin_plugin_action_links_{$this->plugin_file}", [ $this, 'add_warning_sign_to_network_deactivate_action' ], 10, 1 );
		add_action( 'plugin_row_meta', [ $this, 'add_warning_to_plugin_meta' ], 10, 2 );
	}

	/**
	 * Get warning icon markup.
	 *
	 * @return string Warning icon markup.
	 */
	private function get_warning_icon() {
		return sprintf( '<span style="vertical-align: middle">%s</span>', Icon::warning()->to_html() );
	}

	/**
	 * Adds an event to the deactivate queue.
	 *
	 * @param string $event_name The event name.
	 */
	public function add_event( $event_name ) {
		if ( ! in_array( $event_name, $this->events_to_deactivate, true ) ) {
			$this->events_to_deactivate[] = $event_name;
		}
	}

	/**
	 * Run deactivation logic.
	 *
	 * This should be hooked up to the WordPress deactivation hook.
	 *
	 * @param bool $network_wide Whether the deactivation was done network-wide.
	 * @return void
	 */
	public function deactivate( $network_wide ) {
		if ( $network_wide && is_multisite() && ! wp_is_large_network( 'sites' ) ) {
			foreach ( get_sites(
				[
					'fields' => 'ids',
					'number' => 0, // Disables pagination to retrieve all sites.
				]
			) as $blog_id ) {
				switch_to_blog( $blog_id );

				foreach ( $this->events_to_deactivate as $event_name ) {
					wp_unschedule_hook( $event_name );
				}

				restore_current_blog();
			}
		} else {
			foreach ( $this->events_to_deactivate as $event_name ) {
				wp_unschedule_hook( $event_name );
			}
		}
	}

	/**
	 * Add a warning sign to the network deactivate action on the network plugins screen.
	 *
	 * @param string[] $actions     An array of plugin action links. By default this can include 'activate',
	 *                              'deactivate', and 'delete'.
	 * @return string[]
	 */
	public function add_warning_sign_to_network_deactivate_action( $actions ) {
		if ( ! wp_is_large_network() ) {
			return $actions;
		}

		if ( ! array_key_exists( 'deactivate', $actions ) ) {
			return $actions;
		}

		wp_enqueue_style( 'amp-icons' );

		$warning_icon = $this->get_warning_icon();
		if ( false === strpos( $actions['deactivate'], $warning_icon ) ) {
			$actions['deactivate'] = preg_replace( '#(?=</a>)#i', ' ' . $warning_icon, $actions['deactivate'] );
		}

		return $actions;
	}

	/**
	 * Add a warning to the plugin meta row on the network plugins screen.
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata, including the version, author, author URI, and
	 *                              plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return string[]
	 */
	public function add_warning_to_plugin_meta( $plugin_meta, $plugin_file ) {
		if ( ! is_multisite() || ! wp_is_large_network() ) {
			return $plugin_meta;
		}

		if ( $plugin_file !== $this->plugin_file ) {
			return $plugin_meta;
		}

		wp_enqueue_style( 'amp-icons' );

		$warning = $this->get_warning_icon() . ' ' . esc_html__( 'Large site detected. Deactivation will leave orphaned scheduled events behind.', 'amp' ) . ' ' . $this->get_warning_icon();

		if ( ! in_array( $warning, $plugin_meta, true ) ) {
			$plugin_meta[] = $warning;
		}

		return $plugin_meta;
	}
}
