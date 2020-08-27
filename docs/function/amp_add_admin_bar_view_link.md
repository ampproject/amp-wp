## Function `amp_add_admin_bar_view_link`

```php
function amp_add_admin_bar_view_link( $wp_admin_bar );
```

Add &quot;View AMP&quot; admin bar item for Transitional/Reader mode.

Note that when theme support is present (in Native/Transitional modes), the admin bar item will be further amended by the `AMP_Validation_Manager::add_admin_bar_menu_items()` method.

### Arguments

* `\WP_Admin_Bar $wp_admin_bar` - Admin bar.

### Source

:link: [includes/amp-helper-functions.php:1857](../../includes/amp-helper-functions.php#L1857-L1908)

<details>
<summary>Show Code</summary>

```php
function amp_add_admin_bar_view_link( $wp_admin_bar ) {
	if ( is_admin() || amp_is_canonical() || ! amp_is_available() ) {
		return;
	}

	$is_amp_request = amp_is_request();

	if ( $is_amp_request ) {
		$href = amp_remove_endpoint( amp_get_current_url() );
	} elseif ( is_singular() ) {
		$href = amp_get_permalink( get_queried_object_id() ); // For sake of Reader mode.
	} else {
		$href = add_query_arg( amp_get_slug(), '', amp_get_current_url() );
	}

	$href = remove_query_arg( QueryVar::NOAMP, $href );

	$icon = $is_amp_request ? Icon::logo() : Icon::link();
	$attr = [
		'id'    => 'amp-admin-bar-item-status-icon',
		'class' => 'ab-icon',
	];

	$wp_admin_bar->add_node(
		[
			'id'    => 'amp',
			'title' => $icon->to_html( $attr ) . ' ' . esc_html__( 'AMP', 'amp' ),
			'href'  => esc_url( $href ),
		]
	);

	$wp_admin_bar->add_node(
		[
			'parent' => 'amp',
			'id'     => 'amp-view',
			'title'  => esc_html( $is_amp_request ? __( 'View non-AMP version', 'amp' ) : __( 'View AMP version', 'amp' ) ),
			'href'   => esc_url( $href ),
		]
	);

	// Make sure the Customizer opens with AMP enabled.
	$customize_node = $wp_admin_bar->get_node( 'customize' );
	if ( $customize_node && $is_amp_request && AMP_Theme_Support::READER_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT ) ) {
		$args = get_object_vars( $customize_node );
		if ( amp_is_legacy() ) {
			$args['href'] = add_query_arg( 'autofocus[panel]', AMP_Template_Customizer::PANEL_ID, $args['href'] );
		} else {
			$args['href'] = add_query_arg( amp_get_slug(), '1', $args['href'] );
		}
		$wp_admin_bar->add_node( $args );
	}
}
```

</details>
