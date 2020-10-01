<?php
/**
 * Fetches and formats data for AMP reader themes.
 *
 * @package AMP
 * @since 2.0
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Core_Theme_Sanitizer;
use AMP_Options_Manager;
use AmpProject\AmpWP\ExtraThemeAndPluginHeaders;
use AmpProject\AmpWP\Option;
use WP_Error;
use WP_Theme;
use WP_Upgrader;

/**
 * Handles reader themes.
 *
 * @since 2.0
 * @internal
 */
final class ReaderThemes {
	/**
	 * Formatted theme data.
	 *
	 * @var array
	 */
	private $themes;

	/**
	 * Reader themes supported by default.
	 *
	 * @var array
	 */
	private $default_reader_themes;

	/**
	 * Whether themes can be installed in the current WordPress installation.
	 *
	 * @var bool
	 */
	private $can_install_themes;

	/**
	 * The error resulting from a failed themes_api request.
	 *
	 * @var null|WP_Error
	 */
	private $themes_api_error;

	/**
	 * The default reader theme.
	 *
	 * @var string
	 */
	const DEFAULT_READER_THEME = 'legacy';

	/**
	 * Status indicating a reader theme is active on the site.
	 *
	 * @var string
	 */
	const STATUS_ACTIVE = 'active';

	/**
	 * Status indicating a reader theme is installed but not active.
	 *
	 * @var string
	 */
	const STATUS_INSTALLED = 'installed';

	/**
	 * Status indicating a reader theme is not installed but is installable.
	 *
	 * @var string
	 */
	const STATUS_INSTALLABLE = 'installable';

	/**
	 * Status indicating a reader theme is not installed and can't be installed.
	 *
	 * @var string
	 */
	const STATUS_NON_INSTALLABLE = 'non-installable';

	/**
	 * Retrieves all AMP plugin options specified in the endpoint schema.
	 *
	 * @return array Formatted theme data.
	 */
	public function get_themes() {
		if ( null !== $this->themes ) {
			return $this->themes;
		}

		$themes = $this->get_default_reader_themes();

		// Also include themes that declare AMP-compatibility in their style.css.
		$default_reader_theme_slugs = wp_list_pluck( $themes, 'slug' );
		foreach ( $this->get_compatible_installed_themes() as $compatible_installed_theme ) {
			if ( ! in_array( $compatible_installed_theme->get_stylesheet(), $default_reader_theme_slugs, true ) ) {
				$themes[] = $this->normalize_theme_data( $compatible_installed_theme );
			}
		}

		/**
		 * Filters supported reader themes.
		 *
		 * @param array $themes [
		 *     Reader theme data.
		 *     {
		 *         @type string         $name           Theme name.
		 *         @type string         $slug           Theme slug.
		 *         @type string         $slug           URL of theme preview.
		 *         @type string         $screenshot_url The URL of a mobile screenshot. Note: if this is empty, the theme may not display.
		 *         @type string         $homepage       A link to a page with more information about the theme.
		 *         @type string         $description    A description of the theme.
		 *         @type string|boolean $requires       Minimum version of WordPress required by the theme. False if all versions are supported.
		 *         @type string|boolean $requires_php   Minimum version of PHP required by the theme. False if all versions are supported.
		 *         @type string         $download_link  A link to the theme's zip file. If empty, the plugin will attempt to download the theme from wordpress.org.
		 *     }
		 * ]
		 */
		$themes = (array) apply_filters( 'amp_reader_themes', $themes );

		$selected_theme_slug = AMP_Options_Manager::get_option( Option::READER_THEME );
		$theme_slugs         = wp_list_pluck( $themes, 'slug' );

		/*
		 * Check if the chosen Reader theme is among the list of filtered themes. If not, an attempt will be made to
		 * obtain the theme data from the list of installed themes. If neither case is true, the AMP Legacy theme will
		 * be used as a fallback.
		 */
		if ( self::DEFAULT_READER_THEME !== $selected_theme_slug && ! in_array( $selected_theme_slug, $theme_slugs, true ) ) {
			$active_theme = wp_get_theme( $selected_theme_slug );

			if ( $active_theme->exists() ) {
				$themes[] = $this->normalize_theme_data( $active_theme );
			}
		}

		$themes = array_filter(
			$themes,
			static function( $theme ) {
				return is_array( $theme ) && ! empty( $theme['slug'] );
			}
		);

		$themes = array_map(
			function ( $theme ) {
				$theme                 = $this->normalize_theme_data( $theme );
				$theme['availability'] = $this->get_theme_availability( $theme );
				return $theme;
			},
			$themes
		);

		// Sort themes alphabetically before AMP Legacy.
		usort(
			$themes,
			static function ( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			}
		);

		/*
		 * Append the AMP Legacy theme details after filtering the default themes. This ensures the AMP Legacy theme
		 * will always be available as a fallback if the chosen Reader theme becomes unavailable.
		 */
		$themes[] = $this->get_legacy_theme();

		$this->themes = array_values( $themes );

		return $this->themes;
	}

	/**
	 * Provides the themes api error, or null if there is no error.
	 *
	 * @return null|WP_Error
	 */
	public function get_themes_api_error() {
		return $this->themes_api_error;
	}

	/**
	 * Gets a reader theme by slug.
	 *
	 * @param string $slug Theme slug.
	 * @return array|false Theme data or false if the theme is not found.
	 */
	public function get_reader_theme_by_slug( $slug ) {
		return current(
			array_filter(
				$this->get_themes(),
				static function ( $theme ) use ( $slug ) {
					return $theme['slug'] === $slug;
				}
			)
		);
	}

	/**
	 * Retrieves theme data.
	 *
	 * @return array Theme data from the wordpress.org API, or an empty array on failure.
	 */
	public function get_default_reader_themes() {
		if ( null !== $this->default_reader_themes ) {
			return $this->default_reader_themes;
		}

		$cache_key = 'amp_themes_wporg';
		$response  = get_transient( $cache_key );

		if ( ! $response ) {
			require_once ABSPATH . 'wp-admin/includes/theme.php';

			$response = themes_api(
				'query_themes',
				[
					'author'   => 'wordpressdotorg',
					'per_page' => 24, // There are only 12 as of 05/2020.
				]
			);

			if ( is_array( $response ) ) {
				$response = (object) $response;
			}

			/**
			 * The response must minimally be an object with a themes array.
			 *
			 * @see https://wordpress.org/support/topic/issue-during-activating-the-updated-plugins/#post-13383737
			 */
			if (
				! is_object( $response )
				|| ! property_exists( $response, 'themes' )
				|| ! is_array( $response->themes )
				|| is_wp_error( $response )
			) {
				$message = __( 'The request for reader themes from WordPress.org resulted in an invalid response. Check your Site Health to confirm that your site can communicate with WordPress.org. Otherwise, please try again later or contact your host.', 'amp' );
				if ( is_wp_error( $response ) && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
					$message .= ' ' . __( 'Error:', 'amp' );
					if ( $response->get_error_message() ) {
						$message .= sprintf( ' %s (%s).', $response->get_error_message(), $response->get_error_code() );
					} else {
						$message .= ' ' . $response->get_error_code() . '.';
					}
				}

				$this->themes_api_error = new WP_Error(
					'amp_themes_api_invalid_response',
					$message
				);

				return [];
			}

			if ( empty( $response->themes ) ) {
				$this->themes_api_error = new WP_Error(
					'amp_themes_api_empty_themes_array',
					__( 'The default reader themes cannot be displayed because a plugin appears to be overriding the themes response from WordPress.org.', 'amp' )
				);
				return [];
			}

			// Store the transient only if the response was valid.
			set_transient( $cache_key, $response, DAY_IN_SECONDS );
		}

		$supported_themes = array_diff(
			AMP_Core_Theme_Sanitizer::get_supported_themes(),
			[ 'twentyten' ] // Because it is not responsive.
		);

		// Get the subset of themes.
		$reader_themes = array_filter(
			$response->themes,
			static function ( $theme ) use ( $supported_themes ) {
				return in_array( $theme->slug, $supported_themes, true );
			}
		);

		$reader_themes = array_map(
			function ( $theme ) {
				$theme_data                   = $this->normalize_theme_data( $theme );
				$theme_data['screenshot_url'] = amp_get_asset_url( "images/reader-themes/{$theme_data['slug']}.jpg" );

				return $theme_data;
			},
			$reader_themes
		);

		$this->default_reader_themes = $reader_themes;
		return $this->default_reader_themes;
	}

	/**
	 * Get installed themes that are marked as being AMP-compatible.
	 *
	 * @return WP_Theme[] Themes.
	 */
	private function get_compatible_installed_themes() {
		$compatible_themes = [];
		foreach ( wp_get_themes() as $theme ) {
			$value = $theme->get( ExtraThemeAndPluginHeaders::AMP_HEADER );
			if ( rest_sanitize_boolean( $value ) && ExtraThemeAndPluginHeaders::AMP_HEADER_LEGACY !== $value ) {
				$compatible_themes[] = $theme;
			}
		}
		return $compatible_themes;
	}

	/**
	 * Normalize the specified theme data.
	 *
	 * @param WP_Theme|array|\stdClass $theme Theme.
	 * @return array Normalized theme data.
	 */
	private function normalize_theme_data( $theme ) {
		if ( $theme instanceof WP_Theme ) {
			if ( $theme->errors() ) {
				return [];
			}

			$mobile_screenshot = null;
			if ( file_exists( $theme->get_stylesheet_directory() . '/screenshot-mobile.png' ) ) {
				$mobile_screenshot = $theme->get_stylesheet_directory_uri() . '/screenshot-mobile.png';
			} elseif ( file_exists( $theme->get_stylesheet_directory() . '/screenshot-mobile.jpg' ) ) {
				$mobile_screenshot = $theme->get_stylesheet_directory_uri() . '/screenshot-mobile.jpg';
			} else {
				$mobile_screenshot = $theme->get_screenshot();
			}
			if ( $mobile_screenshot ) {
				$mobile_screenshot = add_query_arg( 'ver', $theme->get( 'Version' ), $mobile_screenshot );
			}

			return [
				'name'           => $theme->display( 'Name' ) ?: $theme->get_stylesheet(),
				'slug'           => $theme->get_stylesheet(),
				'preview_url'    => null,
				'screenshot_url' => $mobile_screenshot ?: '',
				'homepage'       => $theme->display( 'ThemeURI' ),
				'description'    => $theme->display( 'Description' ),
				'requires'       => $theme->get( 'RequiresWP' ),
				'requires_php'   => $theme->get( 'RequiresPHP' ),
			];
		}

		if ( ! is_array( $theme ) && ! is_object( $theme ) ) {
			return [];
		}

		$keys = [
			'name',
			'slug',
			'preview_url',
			'screenshot_url',
			'homepage',
			'description',
			'requires',
			'requires_php',
		];

		return array_merge(
			array_fill_keys( $keys, '' ),
			wp_array_slice_assoc( (array) $theme, $keys )
		);
	}

	/**
	 * Returns whether a theme can be installed on the system.
	 *
	 * @param array $theme Theme data.
	 * @return bool True if themes can be installed.
	 */
	public function can_install_theme( $theme ) {
		if ( ! current_user_can( 'install_themes' ) ) {
			return false;
		}

		if ( null === $this->can_install_themes ) {
			if ( ! class_exists( 'WP_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}

			ob_start(); // Prevent request_filesystem_credentials() from outputting the request-filesystem-credentials-form.
			require_once ABSPATH . 'wp-admin/includes/template.php'; // Needed for submit_button().
			$this->can_install_themes = true === ( new WP_Upgrader() )->fs_connect( [ get_theme_root() ] );
			ob_end_clean();
		}

		if ( ! $this->can_install_themes ) {
			return false;
		}

		if ( ! empty( $theme['requires'] ) && version_compare( get_bloginfo( 'version' ), $theme['requires'], '<' ) ) {
			return false;
		}

		if ( ! empty( $theme['requires_php'] ) && version_compare( phpversion(), $theme['requires_php'], '<' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns reader theme availability status.
	 *
	 * @param array $theme Theme data.
	 * @return string Theme availability status.
	 */
	public function get_theme_availability( $theme ) {
		switch ( true ) {
			case get_stylesheet() === $theme['slug']:
				return self::STATUS_ACTIVE;

			case self::DEFAULT_READER_THEME === $theme['slug'] || wp_get_theme( $theme['slug'] )->exists():
				return self::STATUS_INSTALLED;

			case $this->can_install_theme( $theme ):
				return self::STATUS_INSTALLABLE;

			default:
				return self::STATUS_NON_INSTALLABLE;
		}
	}

	/**
	 * Determine if the data for the specified Reader theme exists.
	 *
	 * @param string $theme_slug Theme slug.
	 * @return bool Whether the Reader theme data exists.
	 */
	public function theme_data_exists( $theme_slug ) {
		return in_array( $theme_slug, wp_list_pluck( $this->get_themes(), 'slug' ), true );
	}

	/**
	 * Determine if the AMP legacy Reader theme is being used as a fallback.
	 *
	 * @return bool True if being used as a fallback, false otherwise.
	 */
	public function using_fallback_theme() {
		return amp_is_legacy()
			&& self::DEFAULT_READER_THEME !== AMP_Options_Manager::get_option( Option::READER_THEME );
	}

	/**
	 * Provides details for the legacy theme included with the plugin.
	 *
	 * @return array
	 */
	private function get_legacy_theme() {
		return [
			'name'           => __( 'AMP Legacy', 'amp' ),
			'slug'           => self::DEFAULT_READER_THEME,
			'preview_url'    => 'https://amp-wp.org', // This is unused.
			'screenshot_url' => amp_get_asset_url( 'images/reader-themes/legacy.jpg' ),
			'homepage'       => 'https://amp-wp.org/documentation/how-the-plugin-works/classic-templates/',
			'description'    => __( 'The original templates included in the plugin with limited customization options.', 'amp' ),
			'requires'       => false,
			'requires_php'   => false,
			'availability'   => self::STATUS_INSTALLED,
		];
	}
}
