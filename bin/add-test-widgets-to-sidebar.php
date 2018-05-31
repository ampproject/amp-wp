<?php
/**
 * Adds an instance of every default WordPress widget to the first registered sidebar.
 *
 * @codeCoverageIgnore
 * @package AMP
 */

/**
 * Creates widgets, and adds them to a sidebar.
 *
 * @return string $sidebar The sidebar to which the widgets were added.
 */
function amp_populate_sidebar() {
	global $wp_registered_sidebars;
	$widgets         = amp_get_widgets();
	$sidebar         = amp_get_first_sidebar();
	$created_widgets = array();
	foreach ( $widgets as $widget ) {
		if ( ! amp_widget_already_in_sidebar( $widget, $sidebar ) ) {
			amp_create_widget( $widget );
			$created_widgets[] = amp_get_widget_id( $widget['widget'] );
		}
	}
	amp_add_widgets_to_sidebar( $created_widgets, $sidebar );
	return isset( $wp_registered_sidebars[ $sidebar ]['name'] ) ? $wp_registered_sidebars[ $sidebar ]['name'] : $sidebar;
}

/**
 * Gets the widget data, in order to create them.
 *
 * @return array $widgets Data for the widgets.
 */
function amp_get_widgets() {
	return array(
		array(
			'widget'   => 'media_audio',
			'settings' => array_merge(
				amp_media_widget( 'audio' ),
				array(
					'title' => 'Test Audio Widget: No Loop or Preload',
				)
			),
		),
		array(
			'widget'   => 'media_audio',
			'settings' => array_merge(
				amp_media_widget( 'audio' ),
				array(
					'title'   => 'Test Audio Widget: With Loop, Preload',
					'loop'    => true,
					'preload' => 'auto',
				)
			),
		),
		array(
			'widget'   => 'archives',
			'settings' => array(
				'title' => 'Test Archives Widget: No Dropdown or Count',
			),
		),
		array(
			'widget'   => 'archives',
			'settings' => array(
				'title'    => 'Test Archives Widget: With Dropdown, Count',
				'count'    => 1,
				'dropdown' => 1,
			),
		),
		array(
			'widget' => 'calendar',
		),
		array(
			'widget'   => 'categories',
			'settings' => array(
				'title' => 'Test Categories Widget: No Count, Dropdown, or Hierarchical',
			),
		),
		array(
			'widget'   => 'categories',
			'settings' => array(
				'title' => 'Test Categories Widget: With Count',
				'count' => 1,
			),
		),
		array(
			'widget'   => 'categories',
			'settings' => array(
				'title'        => 'Test Categories Widget: With Count, Dropdown, And Hierarchical',
				'count'        => 1,
				'dropdown'     => 1,
				'hierarchical' => 1,
			),
		),
		array(
			'widget'   => 'custom_html',
			'settings' => array(
				'title'   => 'Test Custom HTML Widget',
				'content' => '<h2>Example Custom HTML widget</h2><article>Here is some custom HTML with a <a href="http://example.com/m">link</a></article>',
			),
		),
		array(
			'widget'   => 'media_gallery',
			'settings' => array_merge(
				amp_gallery_widget(),
				array(
					'link_type'      => 'post',
					'orderby_random' => false,
					'size'           => 'thumbnail',
				)
			),
		),
		array(
			'widget'   => 'media_image',
			'settings' => array_merge(
				amp_image_widget(),
				array(
					'alt'               => 'Example alt value',
					'caption'           => 'Example caption',
					'image_classes'     => 'foo bar',
					'image_title'       => 'example image title',
					'link_classes'      => 'baz bar',
					'link_rel'          => 'nofollow',
					'link_target_blank' => false,
					'link_type'         => 'custom',
					'link_url'          => 'http://example.com/amp-test',
					'size'              => 'full',
				)
			),
		),
		array(
			'widget' => 'meta',
		),
		array(
			'widget'   => 'nav_menu',
			'settings' => array(
				'nav_menu' => amp_menu(),
			),
		),
		array(
			'widget' => 'pages',
		),
		array(
			'widget' => 'recent-comments',
		),
		array(
			'widget'   => 'recent-posts',
			'settings' => array(
				'show_date' => true,
			),
		),
		array(
			'widget'   => 'rss',
			'settings' => array(
				'title'        => 'Test RSS Widget: No Content, Author, or Date',
				'url'          => 'https://amphtml.wordpress.com/feed/',
				'show_author'  => 0,
				'show_date'    => 0,
				'show_summary' => 0,
			),
		),
		array(
			'widget'   => 'rss',
			'settings' => array(
				'title'        => 'Test RSS Widget: With Content, Author, Date',
				'url'          => 'https://amphtml.wordpress.com/feed/',
				'show_author'  => 1,
				'show_date'    => 1,
				'show_summary' => 1,
			),
		),
		array(
			'widget' => 'search',
		),
		array(
			'widget'   => 'tag_cloud',
			'settings' => array(
				'title' => 'Test Tag Widget, No Count',
			),
		),
		array(
			'widget'   => 'tag_cloud',
			'settings' => array(
				'title' => 'Test Tag Widget, With Count',
				'count' => 1,
			),
		),
		array(
			'widget'   => 'text',
			'settings' => array(
				'filter' => true,
				'text'   => '<strong>Example Headline</strong><ul><li>This is to test possible text</li><li>This should display as expected</li></ul>',
				'visual' => true,
			),
		),
		array(
			'widget'   => 'media_video',
			'settings' => array_merge(
				amp_media_widget( 'video' ),
				array(
					'title'   => 'Test Video Widget: No Loop or Preload',
					'loop'    => false,
					'preload' => 'none',
				)
			),
		),
		array(
			'widget'   => 'media_video',
			'settings' => array_merge(
				amp_media_widget( 'video' ),
				array(
					'title'   => 'Test Video Widget: With Loop, Preload',
					'loop'    => true,
					'preload' => 'metadata',
				)
			),
		),
	);
}

/**
 * Get the settings for a media widget.
 *
 * Can apply to 'audio' or 'video' widgets.
 * Queries for a single post of the media type.
 * If none exists, amp_media() will throw an error.
 *
 * @param  string $type The type of media. like 'audio' or 'video'.
 * @return array $audio_widget The settings for the media widget.
 */
function amp_media_widget( $type ) {
	$all_media = amp_media( $type, 1 );
	$media     = reset( $all_media );
	return array(
		'attachment_id' => $media->ID,
		'url'           => $media->guid,
	);
}

/**
 * Get the settings for the gallery widget.
 *
 * @return array $audio_widget The settings for the gallery widget.
 */
function amp_gallery_widget() {
	$images = amp_media( 'image', 3 );
	return array(
		'ids' => wp_list_pluck( $images, 'ID' ),
	);
}

/**
 * Gets the settings for the image widget.
 *
 * @return array $image_widget The settings for the image widget.
 */
function amp_image_widget() {
	$all_images        = amp_media( 'image', 1 );
	$image             = reset( $all_images );
	$metadata          = wp_get_attachment_metadata( $image->ID );
	$default_dimension = 100;
	return array(
		'attachment_id' => $image->ID,
		'height'        => isset( $metadata['height'] ) ? $metadata['height'] : $default_dimension,
		'width'         => isset( $metadata['width'] ) ? $metadata['width'] : $default_dimension,
		'url'           => wp_get_attachment_url( $image->ID ),
	);
}

/**
 * Get media items of a certain type.
 *
 * @throws Exception If not enough posts exist.
 * @param  integer $type  The post_mime_type of the media item.
 * @param  integer $count The number of images for which to query.
 * @return array|WP_CLI::error The media IDs, or an error on failure.
 */
function amp_media( $type, $count = 3 ) {
	$query = new \WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_mime_type' => $type,
			'post_status'    => 'inherit',
			'posts_per_page' => $count,
		)
	);
	if ( $query->post_count < $count ) {
		throw new Exception(
			sprintf(
				'Please ensure at least %1$s "%2$s" attachments are accessible and run this again. There are currently only %3$s.',
				$count,
				$type,
				$query->found_posts
			)
		);
	}
	return $query->get_posts();
}

/**
 * Gets a menu ID, if it has more than 4 items.
 *
 * Iterates through all of the menus.
 * And returns the first that has 4 items.
 *
 * @throws Exception When there is no menu with 4 items.
 * @return integer|WP_CLI::error $menu_id The menu ID, or an error if no menu has at least 4 items.
 */
function amp_menu() {
	$menus         = wp_get_nav_menus();
	$minimum_count = 4;
	foreach ( $menus as $menu ) {
		if ( $menu->count >= $minimum_count ) {
			return $menu->term_id;
		}
	}
	throw new Exception( 'Please add at least 4 items to a menu.' );
}

/**
 * Whether a widget with the same values exists in the sidebar.
 *
 * Iterate through all of the widgets in the sidebar.
 * Find all of the widgets with the same ID base, like 'text.'
 * Then, find if one of those has the same settings as the new widget settings.
 *
 * @param  array  $widget  The settings of the widget.
 * @param  string $sidebar The slug of the sidebar.
 * @return boolean $in_sidebar Whether the widget is in the sidebar.
 */
function amp_widget_already_in_sidebar( $widget, $sidebar ) {
	$sidebars = wp_get_sidebars_widgets();
	if ( empty( $sidebars[ $sidebar ] ) ) {
		return false;
	}

	$id_base         = $widget['widget'];
	$all_widget_data = get_option( 'widget_' . $id_base, array() );

	foreach ( $sidebars[ $sidebar ] as $possible_widget ) {
		if ( false !== strpos( $possible_widget, $id_base ) ) {
			/*
			* If there aren't any settings for the widget, any instance of it is enough.
			* For example, a 'Pages' widget.
			*/
			if ( ! isset( $widget['settings'] ) ) {
				return true;
			}

			preg_match( '/\d+/', $possible_widget, $matches );
			$id = $matches[0];
			if ( isset( $all_widget_data[ $id ] ) ) {
				$widget_data = $all_widget_data[ $id ];
			} else {
				continue;
			}

			// Find if all of the settings in $widget['settings'] are present in the widget that's already in the sidebar.
			if ( array() === array_diff_assoc( array_map( 'serialize', $widget['settings'] ), array_map( 'serialize', $widget_data ) ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Creates a widget, based on the passed settings.
 *
 * @param  array $widget The data for the widget.
 * @return void.
 */
function amp_create_widget( $widget ) {
	if ( ! isset( $widget['widget'] ) ) {
		return;
	}

	$id_base    = $widget['widget'];
	$option_key = 'widget_' . $id_base;
	$widgets    = get_option( $option_key, array() );
	$settings   = isset( $widget['settings'] ) ? $widget['settings'] : array();

	if ( ! isset( $settings['title'] ) ) {
		$title             = str_replace( '_', ' ', $id_base );
		$title             = str_replace( '-', ' ', $title );
		$settings['title'] = sprintf( 'Test %s Widget', ucwords( $title ) );
	}

	$number = 1;
	unset( $widgets['_multiwidget'] );
	if ( ! empty( $widgets ) ) {
		$number = max( array_keys( $widgets ) );
		$number = max( 1, $number );
	}
	$number++;
	$widgets[ $number ] = $settings;
	update_option( $option_key, $widgets );
}

/**
 * Gets the first registered sidebar.
 *
 * @throws Exception When there is no registered sidebar.
 * @return string|WP_CLI::error The first registered sidebar on success; error otherwise.
 */
function amp_get_first_sidebar() {
	$sidebar = reset( $GLOBALS['wp_registered_sidebars'] );
	if ( ! isset( $sidebar['id'] ) ) {
		throw new Exception( 'Please make sure at least one sidebar is registered.' );
	}
	return $sidebar['id'];
}

/**
 * Gets a widget ID, based on its name.
 *
 * Mainly copied from import_theme_starter_content().
 * Increments the ID, based on the number of existing widgets.
 *
 * @see    WP_Customize_Manager::import_theme_starter_content().
 * @param  string $id_base The ID base of the widget, like 'text'.
 * @return string $widget_id The ID of the widget, like 'text-2'.
 */
function amp_get_widget_id( $id_base ) {
	$settings = get_option( "widget_{$id_base}", array() );
	if ( $settings instanceof ArrayObject || $settings instanceof ArrayIterator ) {
		$settings = $settings->getArrayCopy();
	}

	// Get the widget's maximum widget number.
	$widget_numbers = array_keys( $settings );
	if ( count( $widget_numbers ) > 0 ) {
		$widget_numbers[] = 1;
		$widget_number    = call_user_func_array( 'max', $widget_numbers );
	} else {
		$widget_number = 1;
	}
	return sprintf( '%s-%d', $id_base, $widget_number );
}

/**
 * Adds the newly-created widgets to the sidebar.
 *
 * @param  array  $widgets The widget IDs to add to the sidebar.
 * @param  string $sidebar The sidebar to which to add the widget, like sidebar-1.
 * @return void
 */
function amp_add_widgets_to_sidebar( $widgets, $sidebar ) {
	$sidebars = wp_get_sidebars_widgets();
	foreach ( $widgets as $widget ) {
		$sidebars[ $sidebar ][] = $widget;
	}
	update_option( 'sidebars_widgets', $sidebars );
}

// Bootstrap the file.
if ( defined( 'WP_CLI' ) ) {
	try {
		$sidebar = amp_populate_sidebar();
		WP_CLI::success( sprintf( 'Please visit a page with the sidebar: %s.', esc_html( $sidebar ) ) );
	} catch ( Exception $e ) {
		WP_CLI::error( $e->getMessage() );
	}
} else {
	echo 'Must be run in WP-CLI via: wp eval-file bin/add-test-widgets-to-sidebar.php.';
	exit( 1 );
}
