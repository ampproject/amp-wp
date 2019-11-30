<?php
/**
 * Plugin Name: AMP Beta Tester
 * Description: Opt-in to receive non-stable release builds for the AMP plugin.
 * Plugin URI: https://amp-wp.org
 * Author: AMP Project Contributors
 * Author URI: https://github.com/ampproject/amp-wp/graphs/contributors
 * Version: 1.5.0-alpha
 * Text Domain: amp
 * Domain Path: /languages/
 * License: GPLv2 or later
 *
 * @package AMP Beta Tester
 */

namespace AMP_Beta_Tester;

define( 'AMP__BETA_TESTER__DIR__', dirname( __FILE__ ) );
define( 'AMP__BETA__TESTER__RELEASES__TRANSIENT', 'amp_releases' );
define( 'AMP__PLUGIN__BASENAME', 'amp/amp.php' );

// DEV_CODE. This block of code is removed during the build process.
if ( file_exists( AMP__BETA_TESTER__DIR__ . '/amp.php' ) ) {
	add_filter(
		'site_transient_update_plugins',
		function ( $updates ) {
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
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\restore_update_plugins_transient' );
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
 * Restore `update_plugins` transient and remove any plugin data.
 */
function restore_update_plugins_transient() {
	delete_site_transient( AMP__BETA__TESTER__RELEASES__TRANSIENT );
	delete_site_transient( 'update_plugins' );
}

/**
 * Hook into WP.
 *
 * @return void
 */
function init() {
	add_filter( 'plugins_api_result', __NAMESPACE__ . '\update_amp_plugin_information', 10, 3 );
	add_filter( 'pre_set_site_transient_update_plugins', __NAMESPACE__ . '\update_amp_manifest' );
	add_filter( 'upgrader_post_install', __NAMESPACE__ . '\move_plugin_to_correct_folder', 10, 4 );
}

/**
 * Modifies the AMP plugin manifest to point to the latest non-stable update, if it exists.
 *
 * @param \stdClass $updates Object containing information on plugin updates.
 * @return \stdClass
 */
function update_amp_manifest( $updates ) {
	if ( ! isset( $updates->no_update ) ) {
		return $updates;
	}

	if ( ! get_amp_update_manifest() ) {
		return $updates;
	}

	$latest_manifest = fetch_amp_update_manifest();

	if ( ! $latest_manifest ) {
		$current_manifest = fetch_amp_update_manifest( get_amp_version() );

		if ( $current_manifest ) {
			unset( $updates->response[ AMP__PLUGIN__BASENAME ] );
			$updates->no_update[ AMP__PLUGIN__BASENAME ] = $current_manifest;
		}
	} else {
		unset( $updates->no_update[ AMP__PLUGIN__BASENAME ] );
		$updates->response[ AMP__PLUGIN__BASENAME ] = $latest_manifest;
	}

	return $updates;
}

/**
 * Update the AMP plugin information data to reflect that of the GitHub release.
 *
 * @param false|object|array $value  The result object or array. Default false.
 * @param string             $action The type of information being requested from the Plugin Installation API.
 * @param object             $args   Plugin API arguments.
 * @return false|object|array Updated $value, or passed-through $value on failure.
 */
function update_amp_plugin_information( $value, $action, $args ) {
	if ( 'plugin_information' !== $action || 'amp' !== $args->slug ) {
		return $value;
	}

	$amp_version = get_amp_version();

	if ( ! is_pre_release( $amp_version ) ) {
		return $value;
	}

	$amp_manifest = fetch_amp_update_manifest( $amp_version );

	if ( ! $amp_manifest ) {
		return $value;
	}

	$value->version = $amp_version;

	$value->download_link            = $amp_manifest->package;
	$value->versions[ $amp_version ] = $amp_manifest->package;

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

	if ( ! isset( $hook_extra['plugin'] ) || AMP__PLUGIN__BASENAME !== $hook_extra['plugin'] ) {
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

	return $releases;
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

	$manifest = [];
	$zip_url  = get_download_url_from_amp_release( $release );

	if ( $zip_url ) {
		$manifest['package'] = $zip_url;
	}

	$manifest['new_version'] = $release->tag_name;
	$manifest['url']         = $release->html_url;

	return array_merge( (array) $current_manifest, $manifest );
}

/**
 * Fetch the AMP plugin update manifest for the specified version from GitHub.
 *
 * @param string $version Version to get manifest for. Defaults to getting the latest pre-release.
 * @return object|false Latest release, or false on failure.
 */
function fetch_amp_update_manifest( $version = 'pre-release' ) {
	$amp_manifest = null;
	$releases     = get_site_transient( AMP__BETA__TESTER__RELEASES__TRANSIENT );

	if ( empty( $releases ) ) {
		$releases = get_amp_github_releases();
		set_site_transient( AMP__BETA__TESTER__RELEASES__TRANSIENT, $releases, DAY_IN_SECONDS );
	}

	if ( is_array( $releases ) ) {
		$amp_version = get_amp_version();

		foreach ( $releases as $release ) {
			if (
				'pre-release' === $version
				&& true === $release->prerelease
				&& version_compare( $release->tag_name, $amp_version, '>' )
			) {
				$amp_manifest = generate_amp_update_manifest( $release );
				break;
			}

			if ( $version === $release->tag_name ) {
				$amp_manifest = generate_amp_update_manifest( $release );
				break;
			}
		}
	} else {
		// Something went wrong fetching the releases.
		return false;
	}

	if ( empty( $amp_manifest ) ) {
		return false;
	}

	return (object) $amp_manifest;
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

	if ( isset( $updates->response[ AMP__PLUGIN__BASENAME ] ) ) {
		$manifest = $updates->response[ AMP__PLUGIN__BASENAME ];
	} elseif ( isset( $updates->no_update[ AMP__PLUGIN__BASENAME ] ) ) {
		$manifest = $updates->no_update[ AMP__PLUGIN__BASENAME ];
	} else {
		return false;
	}

	return $manifest;
}

/**
 * Determine if the supplied version code is a prerelease.
 *
 * @param string $plugin_version Plugin version code.
 * @return bool
 */
function is_pre_release( $plugin_version ) {
	return (bool) preg_match( '/^\d+\.\d+(\.\d+)?-/', $plugin_version );
}

/**
 * Get the current AMP version.
 *
 * @param bool $strip_build_info Whether to strip build information or not.
 * @return string Current AMP version.
 */
function get_amp_version( $strip_build_info = true ) {
	$amp_version = defined( 'AMP__VERSION' )
		? AMP__VERSION
		: get_plugin_data( WP_PLUGIN_DIR . '/' . AMP__PLUGIN__BASENAME )['Version'];

	if ( $strip_build_info ) {
		// Strip the timestamp and commit hash from the plugin version if it exists.
		preg_match( '/[^-]*-[^-]*/', $amp_version, $amp_version );
		$amp_version = $amp_version[0];
	}

	return $amp_version;
}
