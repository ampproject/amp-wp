<?php
/**
 * Fetches and formats data for AMP reader themes.
 *
 * @package AMP
 * @since 1.6.0
 */

/**
 * Class AMP_Reader_Themes.
 *
 * @since 1.6.0
 */
final class AMP_Reader_Themes {
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
	 * The default reader theme.
	 *
	 * @var string
	 */
	const DEFAULT_READER_THEME = 'classic';

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
		$themes = array_filter( (array) apply_filters( 'amp_reader_themes', $themes ) );

		$themes = array_filter(
			$themes,
			function( $theme ) {
				return is_array( $theme ) && ! empty( $theme ) && ! empty( $theme['screenshot_url'] ); // Screenshots are required.
			}
		);

		$themes = array_map(
			function ( $theme ) {
				$theme['availability'] = $this->get_theme_availability( $theme );
				return $theme;
			},
			$themes
		);

		$this->themes = $themes;

		return $this->themes;
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
	 * @return array Theme data from the wordpress.org API.
	 */
	public function get_default_reader_themes() {
		if ( null !== $this->default_reader_themes ) {
			return $this->default_reader_themes;
		}

		$cache_key = 'amp_themes_wporg';
		$response  = get_transient( $cache_key );
		if ( ! $response ) {
			// Note: This can be used to refresh the hardcoded raw theme data.
			require_once ABSPATH . 'wp-admin/includes/theme.php';

			$response = themes_api(
				'query_themes',
				[
					'author'   => 'wordpressdotorg',
					'per_page' => 24, // There are only 12 as of 05/2020.
				]
			);

			if ( ! is_wp_error( $response ) ) {
				set_transient( $cache_key, $response, DAY_IN_SECONDS );
			}
		}

		if ( is_array( $response ) ) {
			$response = (object) $response;
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

		// Supply the screenshots.
		$reader_themes = array_map(
			static function ( $theme ) use ( $keys ) {
				return array_merge(
					wp_array_slice_assoc( (array) $theme, $keys ),
					[ 'screenshot_url' => amp_get_asset_url( "images/reader-themes/{$theme->slug}.png" ) ]
				);
			},
			$reader_themes
		);

		$reader_themes[] = $this->get_classic_mode();

		$this->default_reader_themes = $reader_themes;
		return $this->default_reader_themes;
	}

	/**
	 * Returns whether a theme can be installed on the system.
	 *
	 * @param array $theme Theme data.
	 * @return bool True if themes can be installed.
	 */
	public function can_install_theme( $theme ) {
		// @todo Add support for installing non-default reader themes. Until that is done, themes that are provided via
		// the amp_reader_themes filter will show on the reader themes screen but will need to be manually installed on
		// the site.
		$default_reader_theme_slugs = array_diff(
			AMP_Core_Theme_Sanitizer::get_supported_themes(),
			[ 'twentyten' ]
		);

		if ( ! in_array( $theme['slug'], $default_reader_theme_slugs, true ) ) {
			return false;
		}

		if ( null === $this->can_install_themes ) {
			if ( ! class_exists( 'WP_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}

			$this->can_install_themes = true === ( new WP_Upgrader() )->fs_connect( [ get_theme_root() ] );
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

			case wp_get_theme( $theme['slug'] )->exists():
				return self::STATUS_INSTALLED;

			case $this->can_install_theme( $theme ):
				return self::STATUS_INSTALLABLE;

			default:
				return self::STATUS_NON_INSTALLABLE;
		}
	}

	/**
	 * Provides details for the classic theme included with the plugin.
	 *
	 * @return array
	 */
	private function get_classic_mode() {
		return [
			'name'           => 'AMP Classic',
			'slug'           => 'classic',
			'preview_url'    => 'https://amp-wp.org',
			'screenshot_url' => amp_get_asset_url( 'images/reader-themes/classic.png' ),
			'homepage'       => 'https://amp-wp.org',
			'description'    => __(
				// @todo Improved description text.
				'A legacy default template that looks nice and clean, with a good balance between ease and extensibility when it comes to customization.',
				'amp'
			),
			'requires'       => false,
			'requires_php'   => false,
			'availability'   => self::STATUS_INSTALLED,
		];
	}
}
