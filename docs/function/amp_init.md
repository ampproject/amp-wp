## Function `amp_init`

```php
function amp_init();
```

Init AMP.

### Source

:link: [includes/amp-helper-functions.php:103](../../includes/amp-helper-functions.php#L103-L226)

<details>
<summary>Show Code</summary>

```php
function amp_init() {

	/**
	 * Triggers on init when AMP plugin is active.
	 *
	 * @since 0.3
	 */
	do_action( 'amp_init' );

	add_filter( 'allowed_redirect_hosts', [ 'AMP_HTTP', 'filter_allowed_redirect_hosts' ] );
	AMP_HTTP::purge_amp_query_vars();
	AMP_HTTP::send_cors_headers();
	AMP_HTTP::handle_xhr_request();
	AMP_Theme_Support::init();
	AMP_Validation_Manager::init();
	AMP_Service_Worker::init();
	add_action( 'admin_init', 'AMP_Options_Manager::init' );
	add_action( 'admin_init', 'AMP_Options_Manager::register_settings' );
	add_action( 'rest_api_init', 'AMP_Options_Manager::register_settings' );
	add_action( 'wp_loaded', 'amp_bootstrap_admin' );

	add_rewrite_endpoint( amp_get_slug(), EP_PERMALINK );
	add_action( 'parse_query', 'amp_correct_query_when_is_front_page' );
	add_action( 'admin_bar_menu', 'amp_add_admin_bar_view_link', 100 );

	add_action(
		'admin_bar_init',
		function () {
			$handle = 'amp-icons';
			if ( ! is_admin() && wp_style_is( $handle, 'registered' ) ) {
				wp_styles()->registered[ $handle ]->deps[] = 'admin-bar'; // Ensure included in dev mode.
				wp_enqueue_style( $handle );
			}
		}
	);

	add_action( 'wp_loaded', 'amp_editor_core_blocks' );
	add_filter( 'request', 'amp_force_query_var_value' );

	// Redirect the old url of amp page to the updated url.
	add_filter( 'old_slug_redirect_url', 'amp_redirect_old_slug_to_new_url' );

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		if ( class_exists( 'WP_CLI\Dispatcher\CommandNamespace' ) ) {
			WP_CLI::add_command( 'amp', 'AMP_CLI_Namespace' );
		}

		WP_CLI::add_command( 'amp validation', 'AMP_CLI_Validation_Command' );
	}

	/*
	 * Broadcast plugin updates.
	 * Note that AMP_Options_Manager::get_option( Option::VERSION, '0.0' ) cannot be used because
	 * version was new option added, and in that case default would never be used for a site
	 * upgrading from a version prior to 1.0. So this is why get_option() is currently used.
	 */
	$options     = get_option( AMP_Options_Manager::OPTION_NAME, [] );
	$old_version = isset( $options[ Option::VERSION ] ) ? $options[ Option::VERSION ] : '0.0';

	if ( AMP__VERSION !== $old_version && is_admin() && current_user_can( 'manage_options' ) ) {
		// This waits to happen until the very end of init to ensure that amp theme support and amp post type support have all been added.
		add_action(
			'init',
			static function () use ( $old_version ) {
				/**
				 * Triggers when after amp_init when the plugin version has updated.
				 *
				 * @param string $old_version Old version.
				 */
				do_action( 'amp_plugin_update', $old_version );
				AMP_Options_Manager::update_option( Option::VERSION, AMP__VERSION );
			},
			PHP_INT_MAX
		);
	}

	add_action(
		'rest_api_init',
		static function() {
			$reader_themes = new ReaderThemes();

			$reader_theme_controller = new AMP_Reader_Theme_REST_Controller( $reader_themes );
			$reader_theme_controller->register_routes();
		}
	);

	/*
	 * Hide admin bar if the window is inside the setup wizard iframe.
	 *
	 * Detects whether the current window is in an iframe with the specified `name` attribute. The iframe is created
	 * by Preview component located in <assets/src/setup/pages/save/index.js>.
	 */
	add_action(
		'wp_print_scripts',
		function() {
			if ( ! amp_is_dev_mode() || ! is_admin_bar_showing() ) {
				return;
			}
			?>
			<script data-ampdevmode>
				( () => {
					if ( 'amp-wizard-completion-preview' !== window.name ) {
						return;
					}

					/** @type {HTMLStyleElement} */
					const style = document.createElement( 'style' );
					style.setAttribute( 'type', 'text/css' );
					style.appendChild( document.createTextNode( 'html { margin-top: 0 !important; } #wpadminbar { display: none !important; }' ) );
					document.head.appendChild( style );

					document.addEventListener( 'DOMContentLoaded', function() {
						const adminBar = document.getElementById( 'wpadminbar' );
						if ( adminBar ) {
							document.body.classList.remove( 'admin-bar' );
							adminBar.remove();
						}
					});
				} )();
			</script>
			<?php
		}
	);
}
```

</details>
