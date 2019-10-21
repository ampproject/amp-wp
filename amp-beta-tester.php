<?php
/**
 * Plugin Name: AMP Beta Tester
 * Description: Opt-in to receive non-stable release builds for the AMP plugin.
 * Plugin URI: https://amp-wp.org
 * Author: AMP Project Contributors
 * Author URI: https://github.com/ampproject/amp-wp/graphs/contributors
 * Version: 0.1
 * Text Domain: amp-beta-tester
 * Domain Path: /languages/
 * License: GPLv2 or later
 *
 * @package AMP Beta Tester
 */

namespace AMP_Beta_Tester;

define( 'AMP__BETA_TESTER__DIR__', dirname( __FILE__ ) );
define( 'AMP_PLUGIN_FILE', 'amp/amp.php' );

// DEV_CODE. This block of code is removed during the build process.
if ( file_exists( AMP__BETA_TESTER__DIR__ . '/amp.php' ) ) {
	add_filter(
		'site_transient_update_plugins',
		function ( $updates ) {
			if ( isset( $updates->response ) && is_array( $updates->response ) ) {
				if ( array_key_exists( 'amp/amp-beta-tester.php', $updates->response ) ) {
					unset( $updates->response['amp/amp-beta-tester.php'] );
				}
			}

			return $updates;
		}
	);
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Hook into WP.
 *
 * @return void
 */
function init() {
	// Abort init if AMP plugin is not active.
	if ( ! defined( 'AMP__FILE__' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\show_amp_not_active_notice' );
		return;
	}

	add_filter( 'pre_set_site_transient_update_plugins', __NAMESPACE__ . '\update_amp_manifest' );
	add_action( 'after_plugin_row_' . AMP_PLUGIN_FILE, __NAMESPACE__ . '\replace_view_version_details_link', 10, 2 );
}

/**
 * Display an admin notice if the AMP plugin is not active.
 *
 * @return void
 */
function show_amp_not_active_notice() {
	$error = esc_html__( 'AMP Beta Tester requires AMP to be active.', 'amp-beta-tester' );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo "<div class='notice notice-error'><p><strong>{$error}</strong></p></div>";
}

/**
 * Modifies the AMP plugin manifest to point to a new beta update if one exists.
 *
 * @param \stdClass $updates Object containing information on plugin updates.
 * @return \stdClass
 */
function update_amp_manifest( $updates ) {
	if ( ! isset( $updates->no_update ) ) {
		return $updates;
	}

	$amp_zip_file = 'amp.zip';
	$amp_manifest = isset( $updates->response[ AMP_PLUGIN_FILE ] )
		? $updates->response[ AMP_PLUGIN_FILE ]
		: $updates->no_update[ AMP_PLUGIN_FILE ];

	$github_releases = get_amp_github_releases();

	if ( is_array( $github_releases ) ) {
		$amp_version = get_plugin_data( WP_PLUGIN_DIR . '/' . AMP_PLUGIN_FILE )['Version'];
		$amp_updated = false;

		foreach ( $github_releases as $release ) {
			if ( $release->prerelease ) {
				$release_version = $release->tag_name;

				// If there is a new release, let's see if there is a zip available for download.
				if ( version_compare( $release_version, $amp_version, '>' ) ) {
					foreach ( $release->assets as $asset ) {
						if ( $amp_zip_file === $asset->name ) {
							$amp_manifest->new_version = $release_version;
							$amp_manifest->package     = $asset->browser_download_url;
							$amp_manifest->url         = $release->html_url;

							// Set the AMP plugin to be updated.
							$updates->response[ AMP_PLUGIN_FILE ] = $amp_manifest;
							unset( $updates->no_update[ AMP_PLUGIN_FILE ] );

							$amp_updated = true;
							break;
						}
					}

					if ( $amp_updated ) {
						break;
					}
				}
			}
		}
	}

	return $updates;
}

/**
 * Fetch AMP releases from GitHub.
 *
 * @return array|null
 */
function get_amp_github_releases() {
	$raw_response = wp_remote_get( 'https://api.github.com/repos/ampproject/amp-wp/releases' );
	if ( is_wp_error( $raw_response ) ) {
		return null;
	}
	return json_decode( $raw_response['body'] );
}

/**
 * Replace the 'View version details' link with the link to the release on GitHub.
 *
 * @param string $file Plugin file.
 * @param array  $plugin_data Plugin data.
 */
function replace_view_version_details_link( $file, $plugin_data ) {
	$plugin_version = $plugin_data['Version'];

	if ( is_pre_release( $plugin_version ) ) {
		ob_start();
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const link = document.querySelectorAll("[data-slug='amp'] a.thickbox.open-plugin-details-modal");

				link.forEach( (link) => {
					link.className = 'overridden'; // Override class so that onclick listeners are disabled.
					link.target = '_blank';
					link.href = '<?php echo $plugin_data['url']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>';
				} );
			}, false);
		</script>
		<?php

		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Determine if the supplied version code is a prerelease.
 *
 * @param string $plugin_version Plugin version code.
 * @return bool
 */
function is_pre_release( $plugin_version ) {
	return (bool) preg_match( '/^\d+\.\d+\.\d+-(beta|alpha)\d?$/', $plugin_version );
}
