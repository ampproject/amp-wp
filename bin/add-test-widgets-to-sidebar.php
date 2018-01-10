<?php
/**
 * Add default widgets to sidebar.
 *
 * @codeCoverageIgnore
 * @package AMP
 */

/**
 * Get all registered widgets.
 *
 * @throws Exception When there are no registered widgets.
 * @return array|WP_CLI::error The registered widgets on success; error otherwise.
 */
function amp_get_wp_widgets() {
	$widgets_list = $GLOBALS['wp_widget_factory'];

	if ( ! isset( $widgets_list->widgets ) ) {
		throw new Exception( 'There were no registered widgets found.' );
	}

	return $widgets_list->widgets;
}

/**
 * Get first registered sidebar.
 *
 * @throws Exception When there is no registered sidebar.
 * @return array|WP_CLI::error The first registered sidebar on success; error otherwise.
 */
function amp_get_first_sidebar() {
	$sidebar = array_shift( $GLOBALS['wp_registered_sidebars'] );

	if ( ! isset( $sidebar ) ) {
		throw new Exception( 'Please make sure at least one sidebar is registered.' );
	}

	return $sidebar;
}

/**
 * Adds widget for use in a sidebar.
 *
 * @uses update_option()
 *
 * @param string $option_name The widget's option name.
 * @param array  $widget_options The options for the widget.
 *
 * @return void
 */
function amp_update_widget_option( $option_name, $widget_options ) {}

/**
 * Adds the new widget to the sidebar.
 *
 * @uses update_option()
 *
 * @param string $widget The widget's option name combined with the array index.
 *
 * @return void
 */
function amp_add_widget_to_sidebar( $widget ) {}

// Bootstrap.
if ( defined( 'WP_CLI' ) ) {
	try {
		WP_CLI::success( null );
	} catch ( Exception $e ) {
		WP_CLI::error( $e->getMessage() );
	}
} else {
	echo "Must be run in WP-CLI via: wp eval-file bin/add-test-widgets-to-sidebar.php\n";
	exit( 1 );
}
