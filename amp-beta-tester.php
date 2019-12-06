<?php
/**
 * Plugin Name:       AMP Beta Tester
 * Description:       Opt-in to receive non-stable release builds for the AMP plugin.
 * Plugin URI:        https://amp-wp.org
 * Author:            AMP Project Contributors
 * Author URI:        https://github.com/ampproject/amp-wp/graphs/contributors
 * Version:           1.5.0-alpha
 * Text Domain:       amp
 * Domain Path:       /languages/
 * License:           GPLv2 or later
 * Requires at least: 4.9
 * Requires PHP:      5.4
 *
 * @package AMP Beta Tester
 */

namespace AMP_Beta_Tester;

define( 'AMP_BETA_TESTER_DIR', __DIR__ );
define( 'AMP_BETA_TESTER_RELEASES_TRANSIENT', 'amp_releases' );
define( 'AMP_PLUGIN_BASENAME', 'amp/amp.php' );
define( 'AMP_BETA_OPTION_NAME', 'amp-beta-options' );

// DEV_CODE. This block of code is removed during the build process.
if ( file_exists( AMP_BETA_TESTER_DIR . '/amp.php' ) ) {
	add_filter(
		'site_transient_update_plugins',
		static function ( $updates ) {
			if ( isset( $updates->response ) && is_array( $updates->response ) ) {
				if ( array_key_exists( 'amp/amp-beta-tester.php', $updates->response ) ) {
					unset( $updates->response['amp/amp-beta-tester.php'] );
				}

				if ( array_key_exists( 'amp/amp.php', $updates->response ) ) {
					unset( $updates->response['amp/amp.php'] );
				}
			}

			return $updates;
		}
	);
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\force_plugin_update_check' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\remove_plugin_data' );
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Force a plugin update check. This will allows us to modify the plugin update cache so we
 * can set a custom update.
 */
function force_plugin_update_check() {
	if ( wp_doing_cron() ) {
		return;
	}
	delete_site_transient( 'update_plugins' );
}

/**
 * Remove any plugin data.
 */
function remove_plugin_data() {
	delete_site_transient( AMP_BETA_TESTER_RELEASES_TRANSIENT );

	/*
	 * Delete the `update_plugins` transient to force a plugin update check, and to get rid of any
	 * custom update manifest for the plugin.
	 */
	delete_site_transient( 'update_plugins' );
}

/**
 * Hook into WP.
 *
 * @return void
 */
function init() {
	if ( defined( 'AMP__VERSION' ) ) {
		add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );
		add_action( 'admin_menu', __NAMESPACE__ . '\add_to_setting_pages' );
	}

	add_filter( 'plugins_api_result', __NAMESPACE__ . '\update_amp_plugin_details', 10, 3 );
	add_filter( 'pre_set_site_transient_update_plugins', __NAMESPACE__ . '\update_amp_manifest' );
	add_filter( 'upgrader_post_install', __NAMESPACE__ . '\move_plugin_to_correct_folder', 10, 3 );
	add_filter( 'auto_update_plugin', 'auto_update_amp_plugin', 10, 2 );
}

/**
 * Register plugin settings.
 */
function register_settings() {
	\register_setting(
		\AMP_Options_Manager::OPTION_NAME,
		AMP_BETA_OPTION_NAME,
		[
			'type'              => 'array',
			'default'           => [],
			'sanitize_callback' => __NAMESPACE__ . '\validate_settings',
		]
	);
}

/**
 * Validate new settings before being saved.
 *
 * @param array|null $options New settings.
 * @return mixed
 */
function validate_settings( $options ) {
	if ( empty( $options ) ) {
		return $options;
	}

	if ( isset( $options['should_auto_update'] ) ) {
		$options['should_auto_update'] = ! empty( $options['should_auto_update'] );
	}

	return $options;
}

/**
 * Add plugin settings to relevant pages.
 */
function add_to_setting_pages() {
	add_settings_section(
		'beta-tester',
		false,
		'__return_false',
		\AMP_Options_Manager::OPTION_NAME
	);

	add_settings_field(
		'auto_updates',
		__( 'Automatic Updates', 'amp' ),
		__NAMESPACE__ . '\render_update_settings',
		\AMP_Options_Manager::OPTION_NAME,
		'beta-tester'
	);
}

/**
 * Display auto update setting field.
 */
function render_update_settings() {
	$should_auto_update = get_option( 'should_auto_update' );
	?>
	<p>
		<label for="should_auto_update">
			<input id="should_auto_update" type="checkbox" name="<?php echo esc_attr( AMP_BETA_OPTION_NAME . '[should_auto_update]' ); ?>" <?php checked( $should_auto_update ); ?>>
			<?php esc_html_e( 'Allow the AMP plugin to be automatically updated.', 'amp' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'This will include pre-release updates.', 'amp' ); ?>
	</p>
	<?php
}

/**
 * Whether or not to auto update the AMP plugin.
 *
 * @param bool   $should_update     Whether to update or not.
 * @param object $plugin_manifest  Plugin update manifest.
 * @return bool True if it should auto update, false if not.
 */
function auto_update_amp_plugin( $should_update, $plugin_manifest ) {
	$should_auto_update = get_option( 'should_auto_update' );

	if ( true === $should_auto_update && AMP_PLUGIN_BASENAME === $plugin_manifest->plugin ) {
		return true;
	}

	return $should_update;
}

/**
 * Modifies the AMP plugin manifest to point to the latest non-stable update, if it exists.
 *
 * @param \stdClass $updates Object containing information on plugin updates.
 * @return \stdClass
 */
function update_amp_manifest( $updates ) {
	// Nothing to do if there is no `no_update` property.
	if ( ! isset( $updates->no_update ) ) {
		return $updates;
	}

	// Nothing to do if the AMP plugin update manifest cannot be retrieved.
	if ( ! get_amp_update_manifest() ) {
		return $updates;
	}

	if ( ! on_latest_amp_release() ) {
		// Get the latest AMP release from GitHub.
		$latest_release_manifest = get_github_amp_update_manifest();

		unset( $updates->no_update[ AMP_PLUGIN_BASENAME ] );
		// Mark AMP plugin as having an update available.
		$updates->response[ AMP_PLUGIN_BASENAME ] = $latest_release_manifest;
	}

	return $updates;
}

/**
 * Update the AMP plugin details to reflect that of the GitHub release.
 *
 * @param false|object|array $value  The result object or array. Default false.
 * @param string             $action The type of information being requested from the Plugin Installation API.
 * @param object             $args   Plugin API arguments.
 * @return false|object|array Updated $value, or passed-through $value on failure.
 */
function update_amp_plugin_details( $value, $action, $args ) {
	if (
		'plugin_information' !== $action
		||
		( is_object( $args ) && isset( $args->slug ) && 'amp' !== $args->slug )
		||
		! is_object( $value )
	) {
		return $value;
	}

	$amp_version             = get_amp_version();
	$latest_release_manifest = get_github_amp_update_manifest();

	$value->version = $amp_version;

	/*
	 * Note: When the 'Install Update' button is shown, it will install the latest stable version of
	 * the AMP plugin from WordPress if the latest release manifest from GitHub cannot be obtained.
	 */
	if ( $latest_release_manifest ) {
		$value->download_link = $latest_release_manifest->package;
	}

	return $value;
}

/**
 * Renames the folder created by WordPress to 'amp'. This is important as WordPress uses it as an
 * identifier for future updates.
 *
 * @param bool  $response   Installation response.
 * @param array $hook_extra Extra arguments passed to hooked filters.
 * @param array $result     Installation result data.
 *
 * @return WP_Error|bool
 */
function move_plugin_to_correct_folder( $response, $hook_extra, $result ) {
	global $wp_filesystem;

	if ( ! isset( $hook_extra['plugin'] ) || AMP_PLUGIN_BASENAME !== $hook_extra['plugin'] ) {
		return $response;
	}

	if ( $wp_filesystem->move( $result['destination'], WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'amp', true ) ) {
		return $response;
	} else {
		return new WP_Error();
	}
}

/**
 * Fetch AMP releases from GitHub.
 *
 * @return array|false Array of releases in descending order, or false if an error occurred parsing response.
 */
function get_amp_github_releases() {
	$releases = get_site_transient( AMP_BETA_TESTER_RELEASES_TRANSIENT );

	if ( ! empty( $releases ) ) {
		return $releases;
	}

	$raw_response = wp_remote_get( 'https://api.github.com/repos/ampproject/amp-wp/releases' );

	if ( is_wp_error( $raw_response ) ) {
		return false;
	}

	$releases = json_decode( $raw_response['body'] );

	if ( ! is_array( $releases ) ) {
		false;
	}

	$releases_by_name = [];

	foreach ( $releases as $release ) {
		$zip_url = get_download_url_from_amp_release( $release );

		// If there is not an 'amp.zip' asset for the release, ignore it.
		if ( ! $zip_url ) {
			continue;
		}

		$release->zip_url                   = $zip_url;
		$releases_by_name[ $release->name ] = $release;
	}

	// Sort releases in descending order by version.
	uksort(
		$releases_by_name,
		static function ( $a, $b ) {
			if ( version_compare( $a, $b, '=' ) ) {
				return 0;
			}

			return version_compare( $a, $b, '<' ) ? 1 : -1;
		}
	);

	set_site_transient(
		AMP_BETA_TESTER_RELEASES_TRANSIENT,
		$releases_by_name,
		DAY_IN_SECONDS
	);

	return $releases_by_name;
}

/**
 * Retrieves the download url for amp.zip, if it exists.
 *
 * @param object $release GitHub release JSON object.
 * @return string|false Download URL if it exists, false if not.
 */
function get_download_url_from_amp_release( $release ) {
	foreach ( $release->assets as $asset ) {
		if ( 'amp.zip' === $asset->name ) {
			return $asset->browser_download_url;
		}
	}

	return false;
}

/**
 * Retrieves the current AMP update manifest, and updates it to include the analogous information
 * from its GitHub release.
 *
 * @param object $release GitHub release JSON object.
 * @return array|false Updated manifest, or false if it fails to retrieve the current update manifest.
 */
function generate_amp_update_manifest( $release ) {
	$current_manifest = get_amp_update_manifest();

	if ( ! $current_manifest ) {
		return false;
	}

	$manifest = [
		'package'     => $release->zip_url,
		'new_version' => $release->name,
		'url'         => $release->html_url,
	];

	return array_merge( (array) $current_manifest, $manifest );
}

/**
 * Determine whether or not the latest AMP plugin is in use.
 *
 * @return bool True if on latest release, false if not.
 */
function on_latest_amp_release() {
	$releases = get_amp_github_releases();

	// The first release is always the latest.
	$release_ver = key( $releases );

	return version_compare( get_amp_version(), $release_ver, '>=' );
}

/**
 * Fetch the AMP plugin update manifest for the specified version from GitHub.
 *
 * @param string $version Version to get manifest for. Defaults to getting the latest release.
 * @return object|false Latest release, or false on failure.
 */
function get_github_amp_update_manifest( $version = 'latest' ) {
	$github_release = null;
	$releases       = get_amp_github_releases();

	if ( is_array( $releases ) && 0 !== count( $releases ) ) {
		if ( 'latest' === $version ) {
			$github_release = $releases[ key( $releases ) ];
		} elseif ( array_key_exists( $version, $releases ) ) {
			$github_release = $releases[ $version ];
		}
	} else {
		// Something went wrong fetching the releases.
		return false;
	}

	if ( null === $github_release ) {
		return false;
	}

	$amp_manifest = generate_amp_update_manifest( $github_release );

	return $amp_manifest ? (object) $amp_manifest : false;
}

/**
 * Get the current AMP plugin update manifest.
 *
 * @return array|false Update manifest for current AMP plugin. False if it can't be retrieved.
 */
function get_amp_update_manifest() {
	$updates = get_site_transient( 'update_plugins' );

	if ( ! isset( $updates->response, $updates->no_update ) ) {
		return false;
	}

	if ( isset( $updates->response[ AMP_PLUGIN_BASENAME ] ) ) {
		$manifest = $updates->response[ AMP_PLUGIN_BASENAME ];
	} else {
		$manifest = $updates->no_update[ AMP_PLUGIN_BASENAME ];
	}

	return $manifest;
}

/**
 * Get the current AMP version.
 *
 * @return string Current AMP version.
 */
function get_amp_version() {
	return defined( 'AMP__VERSION' )
		? AMP__VERSION
		: get_plugin_data( WP_PLUGIN_DIR . '/' . AMP_PLUGIN_BASENAME )['Version'];
}

/**
 * Retrieve an option by name.
 *
 * @param string $name Option name.
 * @return mixed|null Option value, `null` if it cannot be found.
 */
function get_option( $name ) {
	$options = \get_option( AMP_BETA_OPTION_NAME, [] );
	return isset( $options[ $name ] ) ? $options[ $name ] : null;
}
