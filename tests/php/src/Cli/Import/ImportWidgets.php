<?php
/**
 * Reference site import widgets step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Import;

use AmpProject\AmpWP\Tests\Cli\ImportStep;
use WP_CLI;

final class ImportWidgets implements ImportStep {

	/**
	 * Associative array of widgets settings to import.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * ImportWidgets constructor.
	 *
	 * @param array $settings Associative array of widgets settings to import.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Process the step.
	 *
	 * @return int Number of items that were successfully processed.
	 *             Returns -1 for failure.
	 */
	public function process() {
		global $wp_registered_sidebars;

		// Get all available widgets site supports.
		$available_widgets = $this->get_available_widgets();

		// Get all existing widget instances.
		$widget_instances = [];
		foreach ( $available_widgets as $widget_data ) {
			$widget_instances[ $widget_data['id_base'] ] = get_option( 'widget_' . $widget_data['id_base'] );
		}

		$count = 0;

		foreach ( $this->settings as $sidebar_id => $widgets ) {

			// Skip inactive widgets.
			if ( 'wp_inactive_widgets' === $sidebar_id ) {
				continue;
			}

			// Check if sidebar is available on this site.
			// Otherwise add widgets to inactive, and say so.
			if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
				$use_sidebar_id = $sidebar_id;
			} else {
				$use_sidebar_id = 'wp_inactive_widgets';
				WP_CLI::warning(
					WP_CLI::colorize(
						"Widget area %G'{$sidebar_id}'%n does not exist in theme, using %G'wp_inactive_widgets'%n instead."
					)
				);
			}

			foreach ( $widgets as $widget_instance_id => $widget ) {
				$fail = false;

				// Get id_base (remove -# from end) and instance ID number.
				$id_base = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );

				// Does site support this widget?
				if ( ! $fail && ! isset( $available_widgets[ $id_base ] ) ) {
					$fail = true;
					WP_CLI::warning(
						WP_CLI::colorize(
							"Site does not support widget %G'{$id_base}'%n, skipping."
						)
					);
				}

				// Convert multidimensional objects to multidimensional arrays.
				// Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays.
				// Without this, they are imported as objects and cause fatal error on Widgets page.
				$widget = json_decode( wp_json_encode( $widget ), true );

				// Does widget with identical settings already exist in same sidebar?
				if ( ! $fail && isset( $widget_instances[ $id_base ] ) ) {

					// Get existing widgets in this sidebar.
					$sidebars_widgets = get_option( 'sidebars_widgets' );
					$sidebar_widgets  = isset( $sidebars_widgets[ $use_sidebar_id ] )
						? $sidebars_widgets[ $use_sidebar_id ]
						: [];

					// Loop widgets with ID base.
					$single_widget_instances = ! empty( $widget_instances[ $id_base ] )
						? $widget_instances[ $id_base ]
						: [];

					foreach ( $single_widget_instances as $check_id => $check_widget ) {
						if (
							in_array( "$id_base-$check_id", $sidebar_widgets, true )
							&& (array) $widget === $check_widget
						) {
							$fail = true;
							WP_CLI::warning(
								WP_CLI::colorize(
									"Widget %G'{$id_base}-{$check_id}'%n already exists, skipping."
								)
							);
							break;
						}
					}
				}

				if ( ! $fail ) {
					$single_widget_instances   = get_option( 'widget_' . $id_base, [] );
					$single_widget_instances   = ! empty( $single_widget_instances )
						? (array) $single_widget_instances
						: [ '_multiwidget' => 1 ];
					$single_widget_instances[] = $widget;

					end( $single_widget_instances );
					$new_instance_id_number = key( $single_widget_instances );

					// If key is 0, make it 1.
					// When 0, an issue can occur where adding a widget causes data from other widget to load, and the widget doesn't stick (reload wipes it).
					if ( '0' === strval( $new_instance_id_number ) ) {
						$new_instance_id_number                             = 1;
						$single_widget_instances[ $new_instance_id_number ] = $single_widget_instances[0];
						unset( $single_widget_instances[0] );
					}

					// Move _multiwidget to end of array for uniformity.
					if ( isset( $single_widget_instances['_multiwidget'] ) ) {
						$multiwidget = $single_widget_instances['_multiwidget'];
						unset( $single_widget_instances['_multiwidget'] );
						$single_widget_instances['_multiwidget'] = $multiwidget;
					}

					// Update option with new widget.
					update_option( 'widget_' . $id_base, $single_widget_instances );

					// Assign widget instance to sidebar.
					$sidebars_widgets = get_option( 'sidebars_widgets' );
					if ( ! $sidebars_widgets ) {
						$sidebars_widgets = [];
					}

					$new_instance_id                       = $id_base . '-' . $new_instance_id_number;
					$sidebars_widgets[ $use_sidebar_id ][] = $new_instance_id;
					update_option( 'sidebars_widgets', $sidebars_widgets );

					WP_CLI::log(
						WP_CLI::colorize(
							"Widget %G'{$id_base}'%n imported into sidebar '%G{$use_sidebar_id}%n'."
						)
					);
					$count++;
				}
			}
		}

		WP_CLI::success( 'Widgets imported successfully.' );

		return $count;
	}

	/**
	 * Get available widgets.
	 *
	 * @return array Associative array with widget information.
	 */
	public function get_available_widgets() {
		global $wp_registered_widget_controls;

		$widget_controls   = $wp_registered_widget_controls;
		$available_widgets = [];

		foreach ( $widget_controls as $widget ) {
			if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) {
				$available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
				$available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
			}
		}

		return $available_widgets;
	}
}
