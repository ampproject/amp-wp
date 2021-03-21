## Function `amp_is_available`

```php
function amp_is_available();
```

Determine whether AMP is available for the current URL.

### Return value

`bool` - Whether there is an AMP version for the provided URL.

### Source

:link: [includes/amp-helper-functions.php:332](/includes/amp-helper-functions.php#L332-L525)

<details>
<summary>Show Code</summary>

```php
function amp_is_available() {
	global $pagenow, $wp_query;

	// Short-circuit for cron, CLI, admin requests or requests to non-frontend pages.
	if ( wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) || is_admin() || in_array( $pagenow, [ 'wp-login.php', 'wp-signup.php', 'wp-activate.php', 'repair.php' ], true ) ) {
		return false;
	}

	$warn = static function () {
		static $already_warned_sources = [];

		try {
			$likely_culprit_detector = Services::get( 'dev_tools.likely_culprit_detector' );
			$closest_source          = $likely_culprit_detector->analyze_backtrace();
		} catch ( InvalidService $e ) {
			$closest_source = [
				'type' => 'exception',
				'name' => 'invalid_service',
			];
		}

		$closest_source_identifier = $closest_source['type'] . ':' . $closest_source['name'];
		if ( in_array( $closest_source_identifier, $already_warned_sources, true ) ) {
			return;
		}

		$message = sprintf(
			/* translators: 1: amp_is_available() function, 2: amp_is_request() function, 3: is_amp_endpoint() function */
			__( '%1$s (or %2$s, formerly %3$s) was called too early and so it will not work properly.', 'amp' ),
			'`amp_is_available()`',
			'`amp_is_request()`',
			'`is_amp_endpoint()`'
		);

		$current_hook = current_action();
		if ( $current_hook ) {
			/* translators: placeholder is the current hook */
			$message .= ' ' . sprintf(
				'WordPress is currently doing the %s hook.',
				'`' . $current_hook . '`'
			);
		} else {
			$message .= ' ' . __( 'WordPress is not currently doing any hook.', 'amp' );
		}

		$message .= ' ' . sprintf(
			/* translators: 1: the wp action, 2: the WP_Query class, 3: the amp_skip_post() function */
			__( 'Calling this function before the %1$s action means it will not have access to %2$s and the queried object to determine if it is an AMP response, thus neither the %3$s filter nor the AMP enabled toggle will be considered.', 'amp' ),
			'`wp`',
			'`WP_Query`',
			'`amp_skip_post()`'
		);

		if ( ! empty( $closest_source['type'] ) && ! empty( $closest_source['name'] ) ) {
			$translated_string = false;

			switch ( $closest_source['type'] ) {
				case 'plugin':
					/* translators: placeholder is the slug of the plugin */
					$translated_string = __( 'It appears the plugin with slug %s is responsible; please contact the author.', 'amp' );
					break;
				case 'mu-plugin':
					/* translators: placeholder is the slug of the must-use plugin */
					$translated_string = __( 'It appears the must-use plugin with slug %s is responsible; please contact the author.', 'amp' );
					break;
				case 'theme':
					/* translators: placeholder is the slug of the theme */
					$translated_string = __( 'It appears the theme with slug %s is responsible; please contact the author.', 'amp' );
					break;
				case 'exception':
					$translated_string = __( 'The function was called too early (before the plugins_loaded action) to determine the plugin source.', 'amp' );
					break;
			}

			if ( $translated_string ) {
				$message .= ' ' . sprintf( $translated_string, '`' . $closest_source['name'] . '`' );
			}
		}

		_doing_it_wrong( 'amp_is_available', esc_html( $message ), '2.0.0' );
		$already_warned_sources[] = $closest_source_identifier;
	};

	// Make sure the parse_request action has triggered before trying to read from the REST_REQUEST constant, which is set during rest_api_loaded().
	if ( ! did_action( 'parse_request' ) ) {
		$warn();
	} elseif ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}

	// Make sure that the parse_query action has triggered, as this is required to initially populate the global WP_Query.
	if ( ! ( $wp_query instanceof WP_Query || did_action( 'parse_query' ) ) ) {
		$warn();
	}

	// Always return false when requesting the service worker.
	// Note this is no longer strictly required because AMP_Theme_Support::prepare_response() will abort for non-HTML responses.
	// But it is still good to do so because it avoids needlessly output-buffering the response.
	if ( class_exists( 'WP_Service_Workers' ) && $wp_query instanceof WP_Query && defined( 'WP_Service_Workers::QUERY_VAR' ) && $wp_query->get( WP_Service_Workers::QUERY_VAR ) ) {
		return false;
	}

	// Short-circuit queries that can never have AMP responses (e.g. post embeds and feeds).
	// Note that these conditionals only require the parse_query action to have been run. They don't depend on the wp action having been fired.
	if (
		$wp_query instanceof WP_Query
		&&
		(
			$wp_query->is_embed()
			||
			$wp_query->is_feed()
			||
			$wp_query->is_comment_feed()
			||
			$wp_query->is_trackback()
			||
			$wp_query->is_robots()
			||
			( method_exists( $wp_query, 'is_favicon' ) && $wp_query->is_favicon() )
		)
	) {
		return false;
	}

	// Ensure that all templates can be accessed in AMP when a Reader theme is selected.
	$has_reader_theme = (
		AMP_Theme_Support::READER_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
		&&
		ReaderThemes::DEFAULT_READER_THEME !== AMP_Options_Manager::get_option( Option::READER_THEME )
	);
	if ( $has_reader_theme && is_customize_preview() ) {
		return true;
	}

	$is_legacy = amp_is_legacy();

	// If the query has not been initialized, we can only assume AMP is available if theme support is present and all templates are supported.
	if ( ! $wp_query instanceof WP_Query || ! did_action( 'wp' ) ) {
		$warn();
		return ! $is_legacy && AMP_Options_Manager::get_option( Option::ALL_TEMPLATES_SUPPORTED );
	}

	// If redirected to this page because AMP is not available due to validation errors, prevent AMP from being available (if not AMP-first).
	if (
		( ! amp_is_canonical() || AMP_Validation_Manager::has_cap() )
		&&
		( isset( $_GET[ QueryVar::NOAMP ] ) && QueryVar::NOAMP_AVAILABLE === $_GET[ QueryVar::NOAMP ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	) {
		return false;
	}

	/*
	 * If this is a URL for validation, and validation is forced for all URLs, return true.
	 * Normally, this would be false if the user has deselected a template,
	 * like by unchecking 'Categories' in 'AMP Settings' > 'Supported Templates'.
	 * But there's a flag for the WP-CLI command that sets this query var to validate all URLs.
	 */
	if ( AMP_Validation_Manager::is_theme_support_forced() ) {
		return true;
	}

	$queried_object = get_queried_object();
	if ( ! $is_legacy ) {
		// Abort if in Transitional mode and AMP is not available for the URL.
		$availability = AMP_Theme_Support::get_template_availability( $wp_query );

		if ( ! $availability['supported'] ) {
			return false;
		}

		// If not in an AMP-first mode, check if there are any validation errors with kept invalid markup for this URL.
		// And if so, and if the user cannot do validation (since they can always get fresh validation results), then
		// AMP is not available.
		if ( ! amp_is_canonical() && ! AMP_Validation_Manager::has_cap() ) {
			$validation_errors = AMP_Validated_URL_Post_Type::get_invalid_url_validation_errors(
				amp_get_current_url(),
				[ 'ignore_accepted' => true ]
			);
			if ( count( $validation_errors ) > 0 ) {
				return false;
			}
		}
	} elseif ( ! (
		$queried_object instanceof WP_Post &&
		$wp_query instanceof WP_Query &&
		( $wp_query->is_singular() || $wp_query->is_posts_page ) &&
		amp_is_post_supported( $queried_object ) )
	) {
		// Abort if in legacy Reader mode and the post doesn't support AMP.
		return false;
	}

	return true;
}
```

</details>
