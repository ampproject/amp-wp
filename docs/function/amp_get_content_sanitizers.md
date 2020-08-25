## Function `amp_get_content_sanitizers`

```php
function amp_get_content_sanitizers( $post = null );
```

Get content sanitizers.

### Arguments

* `\WP_Post $post` - Post that the content belongs to. Deprecated when theme supports AMP, as sanitizers apply                      to non-post data (e.g. Text widget).

### Return value

`array` - Embed handlers.

### Source

:link: [includes/amp-helper-functions.php:1429](../../includes/amp-helper-functions.php#L1429-L1605)

<details>
<summary>Show Code</summary>

```php
function amp_get_content_sanitizers( $post = null ) {
	$theme_support_args = AMP_Theme_Support::get_theme_support_args();

	if ( $post && ! amp_is_legacy() ) {
		_deprecated_argument(
			__FUNCTION__,
			'0.7',
			sprintf(
				/* translators: %s: $post */
				esc_html__( 'The %s argument is deprecated.', 'amp' ),
				'$post'
			)
		);
		$post = null;
	}

	$parsed_home_url = wp_parse_url( get_home_url() );
	$current_origin  = $parsed_home_url['scheme'] . '://' . $parsed_home_url['host'];
	if ( isset( $parsed_home_url['port'] ) ) {
		$current_origin .= ':' . $parsed_home_url['port'];
	}

	/**
	 * Filters whether AMP-to-AMP linking should be enabled.
	 *
	 * @since 1.4.0
	 * @param bool $amp_to_amp_linking_enabled Whether AMP-to-AMP linking should be enabled.
	 */
	$amp_to_amp_linking_enabled = (bool) apply_filters(
		'amp_to_amp_linking_enabled',
		AMP_Theme_Support::TRANSITIONAL_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
	);

	$sanitizers = [
		'AMP_Embed_Sanitizer'             => [
			'amp_to_amp_linking_enabled' => $amp_to_amp_linking_enabled,
		],
		'AMP_Core_Theme_Sanitizer'        => [
			'template'       => get_template(),
			'stylesheet'     => get_stylesheet(),
			'theme_features' => [
				'force_svg_support' => [], // Always replace 'no-svg' class with 'svg' if it exists.
			],
		],
		'AMP_Img_Sanitizer'               => [
			'align_wide_support' => current_theme_supports( 'align-wide' ),
		],
		'AMP_Form_Sanitizer'              => [],
		'AMP_Comments_Sanitizer'          => [
			'comments_live_list' => ! empty( $theme_support_args['comments_live_list'] ),
		],
		'AMP_Video_Sanitizer'             => [],
		'AMP_O2_Player_Sanitizer'         => [],
		'AMP_Audio_Sanitizer'             => [],
		'AMP_Playbuzz_Sanitizer'          => [],
		'AMP_Iframe_Sanitizer'            => [
			'add_placeholder'    => true,
			'current_origin'     => $current_origin,
			'align_wide_support' => current_theme_supports( 'align-wide' ),
		],
		'AMP_Gallery_Block_Sanitizer'     => [ // Note: Gallery block sanitizer must come after image sanitizers since itś logic is using the already sanitized images.
			'carousel_required' => ! is_array( $theme_support_args ), // For back-compat.
		],
		'AMP_Block_Sanitizer'             => [], // Note: Block sanitizer must come after embed / media sanitizers since its logic is using the already sanitized content.
		'AMP_Script_Sanitizer'            => [],
		'AMP_Style_Sanitizer'             => [],
		'AMP_Meta_Sanitizer'              => [],
		'AMP_Layout_Sanitizer'            => [],
		'AMP_Accessibility_Sanitizer'     => [],
		'AMP_Tag_And_Attribute_Sanitizer' => [], // Note: This validating sanitizer must come at the end to clean up any remaining issues the other sanitizers didn't catch.
	];

	if ( ! empty( $theme_support_args['nav_menu_toggle'] ) ) {
		$sanitizers['AMP_Nav_Menu_Toggle_Sanitizer'] = $theme_support_args['nav_menu_toggle'];
	}

	if ( ! empty( $theme_support_args['nav_menu_dropdown'] ) ) {
		$sanitizers['AMP_Nav_Menu_Dropdown_Sanitizer'] = $theme_support_args['nav_menu_dropdown'];
	}

	if ( $amp_to_amp_linking_enabled && AMP_Theme_Support::STANDARD_MODE_SLUG !== AMP_Options_Manager::get_option( Option::THEME_SUPPORT ) ) {

		/**
		 * Filters the list of URLs which are excluded from being included in AMP-to-AMP linking.
		 *
		 * This only applies when the amp_to_amp_linking_enabled filter returns true,
		 * which it does by default in Transitional mode. This filter can be used to opt-in
		 * when in Reader mode. This does not apply in Standard mode.
		 * Only frontend URLs on the frontend need be excluded, as all other URLs are never made into AMP links.
		 *
		 * @since 1.5.0
		 *
		 * @param string[] $excluded_urls The URLs to exclude from having AMP-to-AMP links.
		 */
		$excluded_urls = apply_filters( 'amp_to_amp_excluded_urls', [] );

		$sanitizers['AMP_Link_Sanitizer'] = array_merge(
			[ 'paired' => ! amp_is_canonical() ],
			compact( 'excluded_urls' )
		);
	}

	/**
	 * Filters the content sanitizers.
	 *
	 * @since 0.2
	 * @since 0.7 Deprecated $post parameter. It will be null when `amp_is_canonical()`.
	 *
	 * @param array   $handlers Handlers.
	 * @param WP_Post $post     Post. Deprecated.
	 */
	$sanitizers = apply_filters( 'amp_content_sanitizers', $sanitizers, $post );

	if ( amp_is_dev_mode() ) {
		/**
		 * Filters the XPath queries for elements that should be enabled for dev mode.
		 *
		 * By supplying XPath queries to this filter, the data-ampdevmode attribute will automatically be added to the
		 * root HTML element as well as to any elements that match the expressions. The attribute is added to the
		 * elements prior to running any of the sanitizers.
		 *
		 * @since 1.3
		 * @param string[] $element_xpaths XPath element queries. Context is the root element.
		 */
		$dev_mode_xpaths = (array) apply_filters( 'amp_dev_mode_element_xpaths', [] );

		if ( is_admin_bar_showing() ) {
			$dev_mode_xpaths[] = '//*[ @id = "wpadminbar" ]';
			$dev_mode_xpaths[] = '//*[ @id = "wpadminbar" ]//*';
			$dev_mode_xpaths[] = '//style[ @id = "admin-bar-inline-css" ]';
		}

		if ( is_customize_preview() ) {
			// Scripts are always needed to inject changeset UUID.
			$dev_mode_xpaths[] = '//script[ @src ]';
			$dev_mode_xpaths[] = '//script[ not( @type ) or @type = "text/javascript" ]';

			// Style needed for Additional CSS to work as intended.
			$dev_mode_xpaths[] = '//style[ @id = "wp-custom-css" ]';

			// Styles needed for Colors customization.
			$dev_mode_xpaths[] = '//style[ @id = "custom-background-css" ]';
			$dev_mode_xpaths[] = '//style[ @id = "custom-theme-colors" ]';
		}

		$sanitizers = array_merge(
			[
				'AMP_Dev_Mode_Sanitizer' => [
					'element_xpaths' => $dev_mode_xpaths,
				],
			],
			$sanitizers
		);
	}

	/**
	 * Filters whether parsed CSS is allowed to be cached in transients.
	 *
	 * When this is filtered to be false, parsed CSS will not be stored in transients. This is important when there is
	 * highly-variable CSS content in order to prevent filling up the wp_options table with an endless number of entries.
	 *
	 * @since 1.5.0
	 * @param bool $transient_caching_allowed Transient caching allowed.
	 */
	$sanitizers['AMP_Style_Sanitizer']['allow_transient_caching'] = apply_filters( 'amp_parsed_css_transient_caching_allowed', true );

	// Force style sanitizer, meta sanitizer, and validating sanitizer to be at end.
	foreach ( [ 'AMP_Style_Sanitizer', 'AMP_Meta_Sanitizer', 'AMP_Tag_And_Attribute_Sanitizer' ] as $class_name ) {
		if ( isset( $sanitizers[ $class_name ] ) ) {
			$sanitizer = $sanitizers[ $class_name ];
			unset( $sanitizers[ $class_name ] );
			$sanitizers[ $class_name ] = $sanitizer;
		}
	}

	return $sanitizers;
}
```

</details>
