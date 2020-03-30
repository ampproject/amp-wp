<?php
/**
 * Abstract class CronBasedBackgroundTask.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

use AmpProject\AmpWP\HasDeactivation;
use AmpProject\AmpWP\Service;
use AmpProject\Fonts;

/**
 * Abstract base class for using cron to execute a background task.
 *
 * @package AmpProject\AmpWP
 */
abstract class CronBasedBackgroundTask implements Service, HasDeactivation {

	const DEFAULT_INTERVAL_HOURLY      = 'hourly';
	const DEFAULT_INTERVAL_TWICE_DAILY = 'twicedaily';
	const DEFAULT_INTERVAL_DAILY       = 'daily';

	/**
	 * Name of the plugin as WordPress is expecting it.
	 *
	 * This should usually have the form "amp/amp.php".
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', [ $this, 'schedule_event' ] );
		add_action( $this->get_event_name(), [ $this, 'process' ] );

		$this->plugin_file = plugin_basename( dirname( dirname( __DIR__ ) ) . '/amp.php' );
		add_action( "network_admin_plugin_action_links_{$this->plugin_file}", [ $this, 'add_warning_sign_to_network_deactivate_action' ], 10, 1 );
		add_action( 'plugin_row_meta', [ $this, 'add_warning_to_plugin_meta' ], 10, 2 );
	}

	/**
	 * Get warning icon markup.
	 *
	 * @return string Warning icon markup.
	 */
	private function get_warning_icon() {
		static $icon = null;
		if ( null === $icon ) {
			$icon = sprintf(
				'<span style="font-family:%s">⚠️</span>',
				esc_attr( Fonts::getEmojiFontFamilyValue() )
			);
		}
		return $icon;
	}

	/**
	 * Schedule the event.
	 *
	 * This does nothing if the event is already scheduled.
	 *
	 * @return void
	 */
	public function schedule_event() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$event_name = $this->get_event_name();
		$timestamp  = wp_next_scheduled( $event_name );

		if ( $timestamp ) {
			return;
		}

		wp_schedule_event( time(), $this->get_interval(), $event_name );
	}

	/**
	 * Run deactivation logic.
	 *
	 * This should be hooked up to the WordPress deactivation hook.
	 *
	 * @todo Needs refactoring if used for more than one cron-based task, to avoid iterating over sites multiple times.
	 *
	 * @param bool $network_wide Whether the deactivation was done network-wide.
	 * @return void
	 */
	public function deactivate( $network_wide ) {
		if ( $network_wide && is_multisite() && ! wp_is_large_network( 'sites' ) ) {
			foreach ( get_sites(
				[
					'fields' => 'ids',
					'number' => 0,
				]
			) as $blog_id ) {
				switch_to_blog( $blog_id );
				wp_clear_scheduled_hook( $this->get_event_name() );
				restore_current_blog();
			}
		} else {
			wp_clear_scheduled_hook( $this->get_event_name() );
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

		$actions['deactivate'] = preg_replace( '#(?=</a>)#i', ' ' . $this->get_warning_icon(), $actions['deactivate'] );

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

		$plugin_meta[] = $this->get_warning_icon() . ' ' . esc_html__( 'Large site detected. Deactivation will leave orphaned scheduled events behind.', 'amp' ) . ' ' . $this->get_warning_icon();

		return $plugin_meta;
	}

	/**
	 * Get the interval to use for the event.
	 *
	 * @return string An existing interval name. Valid values are 'hourly', 'twicedaily' or 'daily'.
	 */
	abstract protected function get_interval();

	/**
	 * Get the event name.
	 *
	 * This is the "slug" of the event, not the display name.
	 *
	 * Note: the event name should be prefixed to prevent naming collisions.
	 *
	 * @return string Name of the event.
	 */
	abstract protected function get_event_name();

	/**
	 * Process a single cron tick.
	 *
	 * @return void
	 */
	abstract public function process();
}
